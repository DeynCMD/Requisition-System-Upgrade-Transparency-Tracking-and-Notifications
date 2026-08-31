<?php
// supplier_bids.php — Admin: read bids, choose winner, generate POs
// Suppliers now submit their own bids via the Supplier Portal
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$conn = new mysqli('localhost', 'root', '', 'ze_electronic');
if ($conn->connect_error) { echo json_encode(['success' => false, 'message' => 'DB error']); exit; }
$conn->set_charset('utf8mb4');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── List approved PRs available for bidding ───────
if ($method === 'GET' && $action === 'requests') {
    $rows = $conn->query("
        SELECT
            pr.id, pr.pr_number, pr.mpn, pr.category, pr.quantity,
            pr.currency, pr.requestor_name, pr.urgency, pr.created_at,
            (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id) AS bid_count,
            (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id AND sb.status = 'selected') AS has_winner,
            (SELECT COUNT(*) FROM purchase_orders po WHERE po.pr_id = pr.id AND po.status != 'Cancelled') AS has_po
        FROM purchase_requests pr
        WHERE pr.status = 'approved'
        ORDER BY bid_count DESC, pr.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'requests' => $rows]); exit;
}

// ── List bids for a specific PR ───────────────────
if ($method === 'GET' && $action === 'list') {
    $prId = (int)($_GET['pr_id'] ?? 0);
    $rows = $conn->query("
        SELECT sb.*, s.name AS supplier_name, s.email AS supplier_email
        FROM supplier_bids sb
        JOIN suppliers s ON s.id = sb.supplier_id
        WHERE sb.pr_id = $prId
        ORDER BY sb.unit_price ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'bids' => $rows]); exit;
}

// ── List all POs ──────────────────────────────────
if ($method === 'GET' && $action === 'pos') {
    $rows = $conn->query("
        SELECT po.*, pr.mpn, pr.category
        FROM purchase_orders po
        JOIN purchase_requests pr ON pr.id = po.pr_id
        ORDER BY po.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'pos' => $rows]); exit;
}

// ── Select winning bid → mark winner, NO PO generated here ──
// PO generation is handled by the Buyer role.
if ($method === 'POST' && $action === 'select_winner') {
    $input  = json_decode(file_get_contents('php://input'), true);
    $bidId  = (int)($input['bid_id'] ?? 0);
    $userId = (int)$_SESSION['user_id'];

    if (!$bidId) { echo json_encode(['success' => false, 'message' => 'No bid specified']); exit; }

    $bid = $conn->query("
        SELECT sb.*, s.name AS supplier_name, pr.pr_number, pr.quantity,
               pr.currency, pr.mpn, pr.category
        FROM supplier_bids sb
        JOIN suppliers s  ON s.id  = sb.supplier_id
        JOIN purchase_requests pr ON pr.id = sb.pr_id
        WHERE sb.id = $bidId
    ")->fetch_assoc();

    if (!$bid) { echo json_encode(['success' => false, 'message' => 'Bid not found']); exit; }

    $prId = (int)$bid['pr_id'];

    // Check no PO already exists for this PR
    $existing = $conn->query("SELECT id FROM purchase_orders WHERE pr_id=$prId AND status != 'Cancelled'")->num_rows;
    if ($existing > 0) {
        echo json_encode(['success' => false, 'message' => 'A PO already exists for this PR']); exit;
    }

    $conn->begin_transaction();
    try {
        // Mark this bid as selected, reject others for same PR
        $conn->query("UPDATE supplier_bids SET status='selected' WHERE id=$bidId");
        $conn->query("UPDATE supplier_bids SET status='rejected', alloc_qty=0 WHERE pr_id=$prId AND id!=$bidId");

        // Log activity
        $prNum  = $conn->real_escape_string($bid['pr_number']);
        $suppNm = $conn->real_escape_string($bid['supplier_name']);
        $by     = $conn->real_escape_string($_SESSION['username'] ?? 'admin');
        $conn->query("
            INSERT INTO activity_logs (activity_type, user_id, performed_by, pr_number, description)
            VALUES ('po_updated', $userId, '$by', '$prNum',
                    'Winning bid selected: supplier $suppNm for PR $prNum — awaiting Buyer PO generation')
        ");

        $conn->commit();
        echo json_encode([
            'success'        => true,
            'message'        => "Winner selected: {$bid['supplier_name']}. The Buyer will generate the Purchase Order.",
            'supplier_name'  => $bid['supplier_name'],
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── Delete a bid (admin can remove invalid bids) ──
if ($method === 'POST' && $action === 'delete') {
    $input = json_decode(file_get_contents('php://input'), true);
    $bidId = (int)($input['id'] ?? 0);
    // Only allow deletion of non-selected bids
    $bid = $conn->query("SELECT status FROM supplier_bids WHERE id=$bidId")->fetch_assoc();
    if (!$bid) { echo json_encode(['success' => false, 'message' => 'Bid not found']); exit; }
    if ($bid['status'] === 'selected') {
        echo json_encode(['success' => false, 'message' => 'Cannot delete a selected winning bid']); exit;
    }
    $stmt = $conn->prepare("DELETE FROM supplier_bids WHERE id=?");
    $stmt->bind_param('i', $bidId); $stmt->execute(); $stmt->close();
    echo json_encode(['success' => true]); exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
$conn->close();
