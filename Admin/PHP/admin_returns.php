<?php
// admin_returns.php — Admin: review and action return requests
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

require_once 'db.php';
$adminId   = (int)$_SESSION['user_id'];
$adminName = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Admin';
$method    = $_SERVER['REQUEST_METHOD'];
$action    = $_GET['action'] ?? '';

// ── GET: list all return requests ────────────────
if ($method === 'GET' && $action === 'list') {
    $status_filter = $_GET['status'] ?? 'all';
    $where = $status_filter !== 'all' ? "WHERE r.status = '" . $conn->real_escape_string($status_filter) . "'" : '';
    $rows  = $conn->query("
        SELECT r.*, po.unit_price, po.currency, po.total_amount, po.quantity AS po_qty,
               pr.mpn, pr.category, pr.requestor_name
        FROM po_returns r
        JOIN purchase_orders po ON po.id = r.po_id
        JOIN purchase_requests pr ON pr.pr_number = r.pr_number
        $where
        ORDER BY r.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'returns' => $rows]); exit;
}

// ── POST: approve a return ───────────────────────
if ($method === 'POST' && $action === 'approve') {
    $input   = json_decode(file_get_contents('php://input'), true);
    $retId   = (int)($input['return_id']  ?? 0);
    $notes   = trim($input['admin_notes'] ?? '');

    if (!$retId) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }

    $ret = $conn->query("SELECT * FROM po_returns WHERE id=$retId AND status='pending'")->fetch_assoc();
    if (!$ret) { echo json_encode(['success' => false, 'message' => 'Return not found or already actioned']); exit; }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            UPDATE po_returns
            SET status='approved', reviewed_by=?, reviewed_by_name=?,
                reviewed_at=NOW(), admin_notes=?
            WHERE id=?
        ");
        $stmt->bind_param('issi', $adminId, $adminName, $notes, $retId);
        $stmt->execute(); $stmt->close();

        $conn->query("UPDATE purchase_orders SET return_status='approved' WHERE id={$ret['po_id']}");

        $by    = $conn->real_escape_string($adminName);
        $poNum = $conn->real_escape_string($ret['po_number']);
        $conn->query("
            INSERT INTO activity_logs (activity_type, user_id, performed_by, pr_number, description)
            VALUES ('return_approved', $adminId, '$by', '{$ret['pr_number']}',
                    'Return approved for PO $poNum')
        ");

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Return approved']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── POST: reject a return ────────────────────────
if ($method === 'POST' && $action === 'reject') {
    $input   = json_decode(file_get_contents('php://input'), true);
    $retId   = (int)($input['return_id']  ?? 0);
    $notes   = trim($input['admin_notes'] ?? '');

    if (!$retId || strlen($notes) < 5) {
        echo json_encode(['success' => false, 'message' => 'Return ID and rejection reason (min 5 chars) required']); exit;
    }

    $ret = $conn->query("SELECT * FROM po_returns WHERE id=$retId AND status='pending'")->fetch_assoc();
    if (!$ret) { echo json_encode(['success' => false, 'message' => 'Return not found or already actioned']); exit; }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            UPDATE po_returns
            SET status='rejected', reviewed_by=?, reviewed_by_name=?,
                reviewed_at=NOW(), admin_notes=?
            WHERE id=?
        ");
        $stmt->bind_param('issi', $adminId, $adminName, $notes, $retId);
        $stmt->execute(); $stmt->close();

        $conn->query("UPDATE purchase_orders SET return_status='rejected' WHERE id={$ret['po_id']}");

        $by    = $conn->real_escape_string($adminName);
        $poNum = $conn->real_escape_string($ret['po_number']);
        $conn->query("
            INSERT INTO activity_logs (activity_type, user_id, performed_by, pr_number, description)
            VALUES ('return_rejected', $adminId, '$by', '{$ret['pr_number']}',
                    'Return rejected for PO $poNum — {$notes}')
        ");

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Return rejected']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── POST: mark return as completed (item physically returned) ──
if ($method === 'POST' && $action === 'complete') {
    $input = json_decode(file_get_contents('php://input'), true);
    $retId = (int)($input['return_id'] ?? 0);
    if (!$retId) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }

    $ret = $conn->query("SELECT * FROM po_returns WHERE id=$retId AND status='approved'")->fetch_assoc();
    if (!$ret) { echo json_encode(['success' => false, 'message' => 'Return not found or not yet approved']); exit; }

    $conn->begin_transaction();
    try {
        $conn->query("UPDATE po_returns SET status='returned', updated_at=NOW() WHERE id=$retId");
        $conn->query("UPDATE purchase_orders SET return_status='returned', status='Cancelled' WHERE id={$ret['po_id']}");

        $by    = $conn->real_escape_string($adminName);
        $poNum = $conn->real_escape_string($ret['po_number']);
        $conn->query("
            INSERT INTO activity_logs (activity_type, user_id, performed_by, pr_number, description)
            VALUES ('return_completed', $adminId, '$by', '{$ret['pr_number']}',
                    'Item return completed for PO $poNum — PO cancelled')
        ");

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Return marked as completed. PO cancelled.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
$conn->close();
