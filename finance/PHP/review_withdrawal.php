<?php
// review_withdrawal.php — finance reviews withdrawal requests
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'FINANCE') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$conn = new mysqli('localhost', 'root', '', 'ze_electronic');
if ($conn->connect_error) { echo json_encode(['success' => false, 'message' => 'DB error']); exit; }
$conn->set_charset('utf8mb4');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── List pending withdrawals ──────────────────────
if ($method === 'GET' && $action === 'list') {
    $rows = $conn->query("
        SELECT w.*, pr.mpn, pr.category, pr.subcategory
        FROM pr_withdrawals w
        JOIN purchase_requests pr ON pr.id = w.pr_id
        ORDER BY w.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'withdrawals' => $rows]); exit;
}

// ── Approve or Reject ─────────────────────────────
if ($method === 'POST') {
    $input        = json_decode(file_get_contents('php://input'), true);
    $wId          = (int)($input['id'] ?? 0);
    $act          = $input['action'] ?? '';
    $rejReason    = trim($input['rejection_reason'] ?? '');
    $reviewerName = $_SESSION['fullname'] ?? 'Finance';
    $reviewerId   = (int)$_SESSION['user_id'];

    if (!$wId || !in_array($act, ['approve','reject'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']); exit;
    }
    if ($act === 'reject' && strlen($rejReason) < 5) {
        echo json_encode(['success' => false, 'message' => 'Rejection reason required (min 5 chars)']); exit;
    }

    $wd = $conn->query("SELECT * FROM pr_withdrawals WHERE id=$wId AND status='pending'")->fetch_assoc();
    if (!$wd) { echo json_encode(['success' => false, 'message' => 'Withdrawal not found or already reviewed']); exit; }

    $conn->begin_transaction();
    try {
        $newStatus = ($act === 'approve') ? 'approved' : 'rejected';

        $stmt = $conn->prepare("
            UPDATE pr_withdrawals
            SET status=?, reviewed_by=?, reviewed_by_name=?, reviewed_at=NOW(), rejection_reason=?
            WHERE id=?
        ");
        $stmt->bind_param('sissi', $newStatus, $reviewerId, $reviewerName, $rejReason, $wId);
        $stmt->execute(); $stmt->close();

        // Update PR withdrawal_status
        $prId = (int)$wd['pr_id'];
        $conn->query("UPDATE purchase_requests SET withdrawal_status='$newStatus' WHERE id=$prId");

        if ($act === 'approve') {
            // Refund flat finance_budget
            $amount = (float)$wd['amount'];
            $conn->query("UPDATE finance_budget SET spent_budget = GREATEST(0, spent_budget - $amount), remaining_budget = remaining_budget + $amount WHERE id=1");
            // Log refund transaction (flat)
            $desc = $conn->real_escape_string("Refund for PR {$wd['pr_number']} — withdrawal approved");
            $conn->query("INSERT INTO budget_transactions (transaction_type, amount, description, performed_by) VALUES ('refund', $amount, '$desc', $reviewerId)");

            // Also credit back the MRO department budget if the PR has a category
            $pr = $conn->query("SELECT category FROM purchase_requests WHERE id=$prId")->fetch_assoc();
            $category = $pr['category'] ?? '';
            $mroCategories = ['Maintenance', 'Repair', 'Operations', 'Operation'];
            if ($category && in_array($category, $mroCategories)) {
                // Normalize "Operation" -> "Operations"
                $deptName = ($category === 'Operation') ? 'Operations' : $category;
                $deptName = $conn->real_escape_string($deptName);
                $conn->query("
                    UPDATE department_budgets
                    SET spent_amount    = GREATEST(0, spent_amount - $amount),
                        remaining_amount = remaining_amount + $amount,
                        updated_at       = NOW()
                    WHERE department_name = '$deptName'
                ");
                // Log MRO refund transaction
                $mroDesc = $conn->real_escape_string("MRO refund to $deptName for PR {$wd['pr_number']}");
                $conn->query("INSERT INTO budget_transactions (transaction_type, amount, department, description, performed_by) VALUES ('refund', $amount, '$deptName', '$mroDesc', $reviewerId)");
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => "Withdrawal $newStatus"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
$conn->close();
