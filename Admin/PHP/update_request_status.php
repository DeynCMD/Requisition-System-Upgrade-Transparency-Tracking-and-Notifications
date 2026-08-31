<?php
// update_request_status.php - CLEANED & STRICT OUTPUT VERSION

// ────────────────────────────────────────────────
// NO whitespace, comments, or anything before <?php
// ────────────────────────────────────────────────

ob_start();
header('Content-Type: application/json; charset=utf-8');

// Prevent any display of errors/warnings in output
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL); // still log them to error_log

// Clean any previous output buffer completely
while (ob_get_level() > 0) {
    ob_end_clean();
}

session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Session expired. Please log in again."]);
    exit;
}

// Allow only ADMIN role (APPROVER is merged — no longer a separate role)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(["success" => false, "message" => "Unauthorized."]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "ze_electronic");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

require_once __DIR__ . '/log_activity.php';
require_once __DIR__ . '/send_pr_status_email.php';

if (!function_exists('sendPRStatusEmail')) {
    echo json_encode(["success" => false, "message" => "Email function not available"]);
    exit;
}

$response = ["success" => false, "message" => "Unknown error"];

$userId = (int) $_SESSION['user_id'];

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if ($id <= 0 || empty($action)) {
    $response["message"] = "Invalid request";
    echo json_encode($response);
    exit;
}

try {
    if ($action === 'approve') {
        $stmt = $conn->prepare("
            UPDATE purchase_requests
            SET status = 'approved',
                finance_status = 'pending',
                approved_by = ?,
                approved_at = NOW()
            WHERE id = ? AND status = 'PENDING'
        ");
        $stmt->bind_param("ii", $userId, $id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            // Log activity
            if (function_exists('logActivity')) {
                logActivity(
                    $conn,
                    'request_approved',
                    $_SESSION['fullname'] ?? 'Admin',
                    "Purchase request approved for ID $id",
                    "Status changed to 'approved'",
                    $userId,
                    null
                );
            }

            // Send email
            $req_stmt = $conn->prepare("SELECT pr_number, requestor_name, user_id FROM purchase_requests WHERE id = ?");
            $req_stmt->bind_param("i", $id);
            $req_stmt->execute();
            $req = $req_stmt->get_result()->fetch_assoc();
            $req_stmt->close();

            if ($req) {
                $email_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                $email_stmt->bind_param("i", $req['user_id']);
                $email_stmt->execute();
                $email_row = $email_stmt->get_result()->fetch_assoc();
                $requestor_email = $email_row['email'] ?? '';
                $email_stmt->close();

                sendPRStatusEmail(
                    $conn,
                    $req['pr_number'],
                    'approved_by_approver',
                    $requestor_email,
                    $req['requestor_name']
                );
            }

            $response = ["success" => true, "message" => "Request approved successfully"];
        } else {
            $response["message"] = "Request not found or already processed";
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("
            UPDATE purchase_requests 
            SET status = 'rejected', 
                rejected_by = ?, 
                rejected_at = NOW(),
                rejection_reason = ?
            WHERE id = ? AND status = 'PENDING'
        ");
        $stmt->bind_param("isi", $userId, $reason, $id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            $response = ["success" => true, "message" => "Request rejected successfully"];
        } else {
            $response["message"] = "Request not found or already processed";
        }
    } else {
        $response["message"] = "Invalid action";
    }
} catch (Exception $e) {
    $response["message"] = "Server error: " . $e->getMessage();
    error_log("Update error: " . $e->getMessage());
}

echo json_encode($response);
exit;  // Ensure absolutely nothing is appended after JSONf