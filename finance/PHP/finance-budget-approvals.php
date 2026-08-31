<?php
// finance-budget-approvals.php – FIXED for your real table schema
// Works with your purchase_requests columns (no missing fields)
// Logs insufficient budget & rejection to activity_logs (visible in history)

ob_start();

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$log_dir = __DIR__ . '/../../logs';
if (!is_dir($log_dir))
    mkdir($log_dir, 0755, true);
ini_set('log_errors', 1);
ini_set('error_log', $log_dir . '/finance_errors.log');

header('Content-Type: application/json; charset=utf-8');

// CORS for testing (ngrok, etc.)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'FINANCE') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$performed_by = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Finance User';

require_once '../../Admin/PHP/db.php';

if ($conn->connect_error) {
    ob_end_clean();
    error_log("DB connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if (file_exists('../../Admin/PHP/send_pr_status_email.php')) {
    require_once '../../Admin/PHP/send_pr_status_email.php';
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$action = $_POST['action'] ?? '';

require_once '../../Admin/PHP/currency_config.php';
$exchange_rates = EXCHANGE_RATES;

// Get pending finance approvals
if ($action === 'get_approved_requests') {
    $sql = "
        SELECT id, pr_number, requestor_name, category, quantity, unit_price, currency, reason,
               (quantity * unit_price) AS total_amount, created_at
        FROM purchase_requests
        WHERE status = 'finance_pending'
          AND finance_status = 'pending'
        ORDER BY created_at DESC
    ";

    $result = $conn->query($sql);
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// Finance Approve
if ($action === 'finance_approve') {
    $pr_id = (int) ($_POST['pr_id'] ?? 0);

    error_log("=== APPROVE STARTED for PR ID: $pr_id ===");

    if ($pr_id <= 0) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid PR ID']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT pr.pr_number, pr.requestor_name, pr.currency, pr.quantity, pr.unit_price,
                   u.email AS requestor_email
            FROM purchase_requests pr
            LEFT JOIN users u ON pr.user_id = u.id
            WHERE pr.id = ? AND pr.status = 'finance_pending' AND pr.finance_status = 'pending'
        ");
        $stmt->bind_param("i", $pr_id);
        $stmt->execute();
        $pr = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$pr)
            throw new Exception("Request not found or not yet ready for finance review (supplier must have a winning bid selected first)");

        $quantity = (float) $pr['quantity'];
        $unit_price = (float) $pr['unit_price'];
        $currency = strtoupper(trim($pr['currency'] ?? 'PHP'));
        $total_original = $quantity * $unit_price;
        $pr_number = $pr['pr_number'];
        $requestor_name = $pr['requestor_name'];
        $requestor_email = $pr['requestor_email'] ?? '';

        $pr_total_php = to_php($total_original, $currency);

        $budget_query = $conn->query("SELECT remaining_budget FROM finance_budget WHERE id = 1");
        $budget_row = $budget_query->fetch_assoc();
        $remaining = (float) ($budget_row['remaining_budget'] ?? 0);

        error_log("Budget: Required=₱" . number_format($pr_total_php, 2) . " | Available=₱" . number_format($remaining, 2));

        if ($pr_total_php > $remaining) {
            $log_type = 'budget_insufficient';
            $log_desc = "Finance approval blocked: Insufficient budget for PR #$pr_number "
                . "(required ₱" . number_format($pr_total_php, 2) . ", "
                . "remaining ₱" . number_format($remaining, 2) . ")";

            $log_stmt = $conn->prepare("
                INSERT INTO activity_logs 
                (activity_type, user_id, performed_by, pr_number, description, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            if ($log_stmt) {
                $log_stmt->bind_param("sisss", $log_type, $user_id, $performed_by, $pr_number, $log_desc);
                $log_stmt->execute();
                error_log("INSUFFICIENT BUDGET LOGGED - ID: " . $conn->insert_id);
                $log_stmt->close();
            } else {
                error_log("Log prepare failed: " . $conn->error);
            }

            ob_end_clean();
            echo json_encode([
                'success' => false,
                'message' => "Insufficient budget. Remaining: ₱" . number_format($remaining, 2) . " (PR requires ₱" . number_format($pr_total_php, 2) . ")"
            ]);
            exit;
        }

        $conn->begin_transaction();

        $new_remaining = $remaining - $pr_total_php;

        $stmt = $conn->prepare("
            UPDATE purchase_requests 
            SET finance_status = 'approved',
                finance_approved_by = ?,
                finance_approved_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $user_id, $pr_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("
            UPDATE finance_budget 
            SET spent_budget = spent_budget + ?,
                remaining_budget = ?,
                updated_at = NOW(),
                updated_by = ?
            WHERE id = 1
        ");
        $stmt->bind_param("ddi", $pr_total_php, $new_remaining, $user_id);
        $stmt->execute();
        $stmt->close();

        // ── Also deduct from MRO dept spent_amount if category matches ──
        $mroCategories = ['Maintenance', 'Repair', 'Operations', 'Operation'];
        $prCategory = $pr['category'] ?? '';
        if ($prCategory && in_array($prCategory, $mroCategories)) {
            $deptName = ($prCategory === 'Operation') ? 'Operations' : $prCategory;
            $deptName = $conn->real_escape_string($deptName);
            $conn->query("
                UPDATE department_budgets
                SET spent_amount     = spent_amount + $pr_total_php,
                    remaining_amount = GREATEST(0, remaining_amount - $pr_total_php),
                    updated_at       = NOW()
                WHERE department_name = '$deptName'
            ");
            // Log dept spend transaction
            $spendDesc = $conn->real_escape_string("Spend for PR $pr_number in $deptName");
            $conn->query("INSERT INTO budget_transactions (transaction_type, amount, department, description, performed_by)
                          VALUES ('spend', $pr_total_php, '$deptName', '$spendDesc', $user_id)");
        }

        $stmt = $conn->prepare("
            INSERT INTO finance_approvals 
            (pr_id, pr_number, requestor_name, total_amount, status, 
             finance_approved_by, finance_approved_at)
            VALUES (?, ?, ?, ?, 'approved', ?, NOW())
        ");
        $stmt->bind_param("issdi", $pr_id, $pr_number, $requestor_name, $pr_total_php, $user_id);
        $stmt->execute();
        $stmt->close();

        $log_type = 'request_finance_approved';
        $log_desc = "Finance approved PR #$pr_number (₱" . number_format($pr_total_php, 2) . ")";

        $log_stmt = $conn->prepare("
            INSERT INTO activity_logs 
            (activity_type, user_id, performed_by, pr_number, description, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $log_stmt->bind_param("sisss", $log_type, $user_id, $performed_by, $pr_number, $log_desc);
        $log_stmt->execute();
        $log_stmt->close();

        $conn->commit();

        $emailSent = false;
        if ($requestor_email && function_exists('sendPRStatusEmail')) {
            try {
                $emailSent = sendPRStatusEmail($conn, $pr_number, 'approved_by_finance', $requestor_email, $requestor_name);
            } catch (Exception $e) {
            }
        }

        $message = "PR approved. ₱" . number_format($pr_total_php, 2) . " deducted.";
        if ($emailSent)
            $message .= " ✓ Email sent.";

        ob_end_clean();
        echo json_encode(['success' => true, 'message' => $message]);

    } catch (Exception $e) {
        if ($conn->in_transaction)
            $conn->rollback();
        error_log("ERROR in finance_approve: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ═══════════════════════════════════════════════════════════════
// FINANCE REJECT – MINIMAL UPDATE (no missing columns)
// ═══════════════════════════════════════════════════════════════
if ($action === 'finance_reject') {
    $pr_id = (int) ($_POST['pr_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');

    error_log("=== FINANCE REJECT for PR ID: $pr_id ===");

    if ($pr_id <= 0 || strlen($reason) < 5) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid ID or reason too short']);
        exit;
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("
            SELECT pr.pr_number, pr.requestor_name, (pr.quantity * pr.unit_price) AS total_amount,
                   u.email AS requestor_email
            FROM purchase_requests pr
            LEFT JOIN users u ON pr.user_id = u.id
            WHERE pr.id = ? AND pr.status = 'finance_pending' AND pr.finance_status = 'pending'
        ");
        $stmt->bind_param("i", $pr_id);
        $stmt->execute();
        $pr = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$pr)
            throw new Exception('Request not found or not yet ready for finance review (supplier must have a winning bid selected first)');

        $pr_number = $pr['pr_number'];
        $requestor_name = $pr['requestor_name'];
        $requestor_email = $pr['requestor_email'] ?? '';

        // FIXED: Only update finance_status
        $stmt = $conn->prepare("
            UPDATE purchase_requests 
            SET finance_status = 'rejected'
            WHERE id = ?
        ");
        $stmt->bind_param("i", $pr_id);
        $stmt->execute();
        $stmt->close();

        // Log to finance_approvals (using rejection_reason)
        $stmt = $conn->prepare("
            INSERT INTO finance_approvals 
            (pr_id, pr_number, requestor_name, total_amount, status, 
             finance_approved_by, finance_approved_at, rejection_reason)
            VALUES (?, ?, ?, ?, 'rejected', ?, NOW(), ?)
        ");
        $stmt->bind_param("issdis", $pr_id, $pr_number, $requestor_name, $pr['total_amount'], $user_id, $reason);
        $stmt->execute();
        $stmt->close();

        // Log rejection to activity_logs
        $log_type = 'request_finance_rejected';
        $log_desc = "Finance rejected PR #$pr_number – Reason: $reason";

        $log_stmt = $conn->prepare("
            INSERT INTO activity_logs 
            (activity_type, user_id, performed_by, pr_number, description, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $log_stmt->bind_param("sisss", $log_type, $user_id, $performed_by, $pr_number, $log_desc);
        $log_stmt->execute();
        $log_stmt->close();

        $conn->commit();

        $emailSent = false;
        if ($requestor_email && function_exists('sendPRStatusEmail')) {
            try {
                $emailSent = sendPRStatusEmail($conn, $pr_number, 'rejected_by_finance', $requestor_email, $requestor_name);
            } catch (Exception $e) {
            }
        }

        $message = 'Request rejected successfully.' . ($emailSent ? ' ✅ Email sent.' : '');

        ob_end_clean();
        echo json_encode(['success' => true, 'message' => $message]);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("ERROR in finance_reject: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

ob_end_clean();
echo json_encode(['success' => false, 'message' => 'Invalid action']);