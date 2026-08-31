<?php
// request_withdrawal.php — requestor submits a withdrawal/refund request
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'REQUESTOR') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$conn = new mysqli('localhost', 'root', '', 'ze_electronic');
if ($conn->connect_error) { echo json_encode(['success' => false, 'message' => 'DB error']); exit; }
$conn->set_charset('utf8mb4');

$input    = json_decode(file_get_contents('php://input'), true);
$prId     = (int)($input['pr_id'] ?? 0);
$reason   = trim($input['reason'] ?? '');
$userId   = (int)$_SESSION['user_id'];
$userName = $_SESSION['fullname'] ?? 'Unknown';

if (!$prId || strlen($reason) < 5) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields (reason must be at least 5 chars)']); exit;
}

// Validate: PR must belong to this user AND be approved (regardless of buyer_status)
$pr = $conn->query("
    SELECT * FROM purchase_requests
    WHERE id = $prId
      AND user_id = $userId
      AND status = 'approved'
")->fetch_assoc();

if (!$pr) {
    echo json_encode(['success' => false, 'message' => 'Invalid request or not eligible for withdrawal (must be approved)']); exit;
}

// Check no existing pending withdrawal
if ($conn->query("SELECT id FROM pr_withdrawals WHERE pr_id=$prId AND status='pending'")->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'A withdrawal request is already pending for this PR']); exit;
}

// Determine type
$withdrawalType = ($pr['buyer_status'] === 'purchased') ? 'post_purchase' : 'pre_po';

$conn->begin_transaction();
try {
    $prNumber = $pr['pr_number'];
    $amount   = (float)$pr['total_amount'];
    $currency = $pr['currency'];

    // Add withdrawal_type column if it doesn't exist yet (safe migration)
    $conn->query("ALTER TABLE pr_withdrawals ADD COLUMN IF NOT EXISTS withdrawal_type ENUM('pre_po','post_purchase') NOT NULL DEFAULT 'post_purchase'");

    $stmt = $conn->prepare("
        INSERT INTO pr_withdrawals (pr_id, pr_number, requested_by, requested_by_name, amount, currency, reason, withdrawal_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('isiidsss', $prId, $prNumber, $userId, $userName, $amount, $currency, $reason, $withdrawalType);
    $stmt->execute();
    $stmt->close();

    $conn->query("UPDATE purchase_requests SET withdrawal_status='requested' WHERE id=$prId");

    // Log activity
    $actType = 'withdrawal_requested';
    $desc    = "Withdrawal requested for $prNumber ($withdrawalType)";
    $conn->query("INSERT INTO activity_logs (activity_type, user_id, performed_by, pr_number, description) VALUES ('$actType', $userId, '$userName', '$prNumber', '$desc')");

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Withdrawal submitted. Finance will review and release the budget if approved.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
