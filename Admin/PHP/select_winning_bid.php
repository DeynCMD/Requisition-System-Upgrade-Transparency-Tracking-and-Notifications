<?php
// select_winning_bid.php
// Atomic transition: status='approved' -> status='finance_pending'.
// Callable by BUYER and ADMIN roles. Marks the chosen supplier bid as
// 'selected' and rejects the rest.

ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

$role = $_SESSION['role'];
if ($role !== 'BUYER' && $role !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Only Buyer or Admin can select a winning bid.']);
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/log_activity.php';
require_once __DIR__ . '/send_pr_status_email.php';

$prId         = (int)($_POST['pr_id'] ?? 0);
$winningBidId = (int)($_POST['winning_bid_id'] ?? 0);

if ($prId <= 0 || $winningBidId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing pr_id or winning_bid_id']);
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn->begin_transaction();

    // Lock the PR row to prevent two concurrent selections.
    $stmt = $conn->prepare("
        SELECT id, pr_number, status, finance_status, requestor_name, user_id
        FROM purchase_requests
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->bind_param('i', $prId);
    $stmt->execute();
    $pr = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$pr) {
        throw new Exception('Purchase request not found');
    }
    if ($pr['status'] !== 'approved') {
        throw new Exception('This PR is not in the bidding stage (current status: ' . $pr['status'] . ')');
    }

    // Validate the chosen bid exists and belongs to this PR, and is still pending.
    $stmt = $conn->prepare("
        SELECT id, supplier_id, status FROM supplier_bids
        WHERE id = ? AND pr_id = ? AND status = 'pending'
    ");
    $stmt->bind_param('ii', $winningBidId, $prId);
    $stmt->execute();
    $bid = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bid) {
        throw new Exception('Invalid bid for this PR or bid is not pending');
    }

    // Reject all other bids on this PR.
    $stmt = $conn->prepare("
        UPDATE supplier_bids
        SET status = 'rejected', updated_at = NOW()
        WHERE pr_id = ? AND id != ? AND status = 'pending'
    ");
    $stmt->bind_param('ii', $prId, $winningBidId);
    $stmt->execute();
    $stmt->close();

    // Mark winning bid as selected.
    $stmt = $conn->prepare("
        UPDATE supplier_bids
        SET status = 'selected', updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param('i', $winningBidId);
    $stmt->execute();
    $stmt->close();

    // Transition PR status. The WHERE guard ensures we don't double-transition.
    $stmt = $conn->prepare("
        UPDATE purchase_requests
        SET status = 'finance_pending', finance_status = 'pending', updated_at = NOW()
        WHERE id = ? AND status = 'approved'
    ");
    $stmt->bind_param('i', $prId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        // Another caller won the race.
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'This PR was already processed by someone else']);
        exit;
    }

    $conn->commit();

    // Log activity.
    if (function_exists('logActivity')) {
        $actor = $_SESSION['fullname'] ?? $_SESSION['username'] ?? $role;
        logActivity(
            $conn,
            'winning_bid_selected',
            $actor,
            "Winning bid selected for PR {$pr['pr_number']} (bid #$winningBidId)",
            "Bid #$winningBidId marked selected; PR moved to finance review",
            null,
            $pr['pr_number']
        );
    }

    // Send notification email to requestor.
    if (!empty($pr['user_id'])) {
        $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->bind_param('i', $pr['user_id']);
        $stmt->execute();
        $emailRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $requestorEmail = $emailRow['email'] ?? '';
        if ($requestorEmail && function_exists('sendPRStatusEmail')) {
            @sendPRStatusEmail(
                $conn,
                $pr['pr_number'],
                'awaiting_finance',
                $requestorEmail,
                $pr['requestor_name']
            );
        }
    }

    echo json_encode([
        'success'     => true,
        'message'     => 'Winning bid selected. PR is now awaiting finance review.',
        'pr_number'   => $pr['pr_number'],
        'winning_bid' => $winningBidId,
    ]);
} catch (Exception $e) {
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    error_log("select_winning_bid error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
