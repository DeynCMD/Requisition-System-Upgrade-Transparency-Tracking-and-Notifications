<?php
// buyer_returns.php — Buyer: submit and view return requests
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'BUYER') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

require_once '../../Admin/PHP/db.php';
$buyerId   = (int)($_SESSION['user_id'] ?? 0);
$buyerName = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Buyer';
$method    = $_SERVER['REQUEST_METHOD'];
$action    = $_GET['action'] ?? '';

// ── GET: list my return requests ──────────────────
if ($method === 'GET' && $action === 'list') {
    $tableExists = $conn->query("SHOW TABLES LIKE 'po_returns'")->num_rows > 0;
    if (!$tableExists) {
        echo json_encode(['success' => true, 'returns' => []]); exit;
    }
    $rows = $conn->query("
        SELECT r.*, po.po_number, po.pr_number, po.quantity AS po_qty,
               po.unit_price, po.currency, po.total_amount,
               pr.mpn, pr.category
        FROM po_returns r
        JOIN purchase_orders po ON po.id = r.po_id
        JOIN purchase_requests pr ON pr.pr_number = po.pr_number
        WHERE r.requested_by = $buyerId
        ORDER BY r.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'returns' => $rows]); exit;
}

// ── GET: eligible POs for return (Received POs with no active return) ──
if ($method === 'GET' && $action === 'eligible_pos') {
    // Check if return_status column exists (graceful if migration not yet run)
    $hasCol = $conn->query("SHOW COLUMNS FROM purchase_orders LIKE 'return_status'")->num_rows > 0;
    $returnFilter = $hasCol
        ? "AND po.return_status IN ('none', 'rejected')"
        : "AND po.id NOT IN (SELECT po_id FROM po_returns WHERE status IN ('pending','approved'))";

    $tableExists = $conn->query("SHOW TABLES LIKE 'po_returns'")->num_rows > 0;
    if (!$tableExists) {
        // Table doesn't exist yet — just return all Received POs
        $returnFilter = '';
    }

    $rows = $conn->query("
        SELECT po.id, po.po_number, po.pr_number, po.supplier_name,
               po.quantity, po.unit_price, po.currency, po.total_amount,
               po.delivery_date,
               " . ($hasCol ? "po.return_status," : "'none' AS return_status,") . "
               pr.mpn, pr.category, pr.requestor_name
        FROM purchase_orders po
        JOIN purchase_requests pr ON pr.pr_number = po.pr_number
        WHERE po.status = 'Received'
        $returnFilter
        ORDER BY po.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'pos' => $rows]); exit;
}

// ── POST: submit a return request ─────────────────
if ($method === 'POST' && $action === 'submit') {
    $input    = json_decode(file_get_contents('php://input'), true);
    $poId     = (int)($input['po_id']            ?? 0);
    $qtyRet   = (int)($input['quantity_returned'] ?? 1);
    $reason   = trim($input['reason']             ?? '');
    $desc     = trim($input['description']        ?? '');

    $validReasons = ['defective','wrong_item','damaged_shipping','overdelivery','other'];
    if (!$poId || $qtyRet < 1 || !in_array($reason, $validReasons) || strlen($desc) < 5) {
        echo json_encode(['success' => false, 'message' => 'All fields required. Description must be at least 5 characters.']); exit;
    }

    $po = $conn->query("
        SELECT po.*, pr.mpn FROM purchase_orders po
        JOIN purchase_requests pr ON pr.pr_number = po.pr_number
        WHERE po.id = $poId AND po.status = 'Received'
    ")->fetch_assoc();

    if (!$po) {
        echo json_encode(['success' => false, 'message' => 'PO not found or not in Received status']); exit;
    }
    if ($qtyRet > (int)$po['quantity']) {
        echo json_encode(['success' => false, 'message' => "Return quantity ($qtyRet) cannot exceed PO quantity ({$po['quantity']})"]); exit;
    }
    if (!in_array($po['return_status'], ['none', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'A return request already exists for this PO']); exit;
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            INSERT INTO po_returns
              (po_id, po_number, pr_number, supplier_id, supplier_name,
               quantity_returned, reason, description, requested_by, requested_by_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('isssississ',
            $poId, $po['po_number'], $po['pr_number'],
            $po['supplier_id'], $po['supplier_name'],
            $qtyRet, $reason, $desc,
            $buyerId, $buyerName
        );
        $stmt->execute(); $stmt->close();

        // Update PO return_status
        $conn->query("UPDATE purchase_orders SET return_status='requested' WHERE id=$poId");

        // Log
        $by    = $conn->real_escape_string($buyerName);
        $poNum = $conn->real_escape_string($po['po_number']);
        $conn->query("
            INSERT INTO activity_logs (activity_type, user_id, performed_by, pr_number, description)
            VALUES ('return_requested', $buyerId, '$by', '{$po['pr_number']}',
                    'Return requested for PO $poNum — Reason: $reason')
        ");

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Return request submitted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
$conn->close();
