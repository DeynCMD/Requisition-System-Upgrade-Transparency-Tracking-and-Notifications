<?php
// supplier_api.php — all AJAX endpoints for the supplier portal
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['supplier_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$supplier_id = (int)$_SESSION['supplier_id'];

$conn = new mysqli('localhost', 'root', '', 'ze_electronic');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB error']); exit;
}
$conn->set_charset('utf8mb4');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── GET: list open PRs available for bidding ─────
// Shows all approved PRs where this supplier has NOT yet bid
if ($method === 'GET' && $action === 'open_requests') {
    $rows = $conn->query("
        SELECT
            pr.id,
            pr.pr_number,
            pr.mpn,
            pr.manufacturer,
            pr.category,
            pr.subcategory,
            pr.quantity,
            pr.currency,
            pr.reason,
            pr.urgency,
            pr.required_by,
            pr.requestor_name,
            pr.created_at,
            (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id) AS total_bids,
            (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id AND sb.supplier_id = $supplier_id) AS my_bid_count
        FROM purchase_requests pr
        WHERE pr.status = 'approved'
        ORDER BY pr.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'requests' => $rows]); exit;
}

// ── GET: my submitted bids ────────────────────────
if ($method === 'GET' && $action === 'my_bids') {
    $rows = $conn->query("
        SELECT
            sb.id,
            sb.pr_id,
            sb.unit_price,
            sb.delivery_date,
            sb.notes,
            sb.status,
            sb.created_at,
            pr.pr_number,
            pr.mpn,
            pr.category,
            pr.quantity,
            pr.currency,
            po.po_number,
            po.status AS po_status,
            po.total_amount AS po_total
        FROM supplier_bids sb
        JOIN purchase_requests pr ON pr.id = sb.pr_id
        LEFT JOIN purchase_orders po ON po.pr_id = sb.pr_id AND po.supplier_id = $supplier_id
        WHERE sb.supplier_id = $supplier_id
        ORDER BY sb.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'bids' => $rows]); exit;
}

// ── GET: dashboard stats ──────────────────────────
if ($method === 'GET' && $action === 'stats') {
    $total_bids = $conn->query("SELECT COUNT(*) AS c FROM supplier_bids WHERE supplier_id=$supplier_id")->fetch_assoc()['c'] ?? 0;
    $won_bids   = $conn->query("SELECT COUNT(*) AS c FROM supplier_bids WHERE supplier_id=$supplier_id AND status='selected'")->fetch_assoc()['c'] ?? 0;
    $open_prs   = $conn->query("SELECT COUNT(*) AS c FROM purchase_requests WHERE status='approved'")->fetch_assoc()['c'] ?? 0;
    $total_po_value = $conn->query("
        SELECT COALESCE(SUM(total_amount), 0) AS v
        FROM purchase_orders
        WHERE supplier_id=$supplier_id AND status != 'Cancelled'
    ")->fetch_assoc()['v'] ?? 0;

    echo json_encode([
        'success'         => true,
        'total_bids'      => (int)$total_bids,
        'won_bids'        => (int)$won_bids,
        'open_prs'        => (int)$open_prs,
        'total_po_value'  => (float)$total_po_value
    ]); exit;
}

// ── POST: submit a bid ────────────────────────────
if ($method === 'POST' && $action === 'submit_bid') {
    $input      = json_decode(file_get_contents('php://input'), true);
    $pr_id      = (int)($input['pr_id']        ?? 0);
    $unit_price = (float)($input['unit_price'] ?? 0);
    $delivery   = trim($input['delivery_date'] ?? '');
    $notes      = trim($input['notes']         ?? '');

    if (!$pr_id || $unit_price <= 0 || !$delivery) {
        echo json_encode(['success' => false, 'message' => 'Unit price and delivery date are required']); exit;
    }

    // Validate delivery date is in the future
    if (strtotime($delivery) <= strtotime('today')) {
        echo json_encode(['success' => false, 'message' => 'Delivery date must be in the future']); exit;
    }

    // Check PR still open
    $pr = $conn->query("SELECT id, pr_number, quantity FROM purchase_requests WHERE id=$pr_id AND status='approved'")->fetch_assoc();
    if (!$pr) {
        echo json_encode(['success' => false, 'message' => 'This request is no longer open for bidding']); exit;
    }

    // Check no PO already issued for this PR
    $po_exists = $conn->query("SELECT id FROM purchase_orders WHERE pr_id=$pr_id")->num_rows;
    if ($po_exists > 0) {
        echo json_encode(['success' => false, 'message' => 'A purchase order has already been issued for this request']); exit;
    }

    // Check duplicate bid
    $dup = $conn->query("SELECT id FROM supplier_bids WHERE pr_id=$pr_id AND supplier_id=$supplier_id")->num_rows;
    if ($dup > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already submitted a bid for this request']); exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO supplier_bids (pr_id, supplier_id, unit_price, delivery_date, notes, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param('iidss', $pr_id, $supplier_id, $unit_price, $delivery, $notes);
    $stmt->execute();
    $bid_id = $conn->insert_id;
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Bid submitted successfully', 'bid_id' => $bid_id]); exit;
}

// ── POST: update a bid (only if still pending) ────
if ($method === 'POST' && $action === 'update_bid') {
    $input      = json_decode(file_get_contents('php://input'), true);
    $bid_id     = (int)($input['bid_id']       ?? 0);
    $unit_price = (float)($input['unit_price'] ?? 0);
    $delivery   = trim($input['delivery_date'] ?? '');
    $notes      = trim($input['notes']         ?? '');

    if (!$bid_id || $unit_price <= 0 || !$delivery) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']); exit;
    }

    // Confirm bid belongs to this supplier and is still pending
    $bid = $conn->query("
        SELECT id, status FROM supplier_bids
        WHERE id=$bid_id AND supplier_id=$supplier_id
    ")->fetch_assoc();
    if (!$bid) {
        echo json_encode(['success' => false, 'message' => 'Bid not found']); exit;
    }
    if ($bid['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Cannot edit a bid that has already been decided']); exit;
    }

    $stmt = $conn->prepare("
        UPDATE supplier_bids SET unit_price=?, delivery_date=?, notes=?, updated_at=NOW()
        WHERE id=? AND supplier_id=?
    ");
    $stmt->bind_param('dssii', $unit_price, $delivery, $notes, $bid_id, $supplier_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Bid updated']); exit;
}

// ── POST: withdraw a bid (only if pending) ────────
if ($method === 'POST' && $action === 'withdraw_bid') {
    $input  = json_decode(file_get_contents('php://input'), true);
    $bid_id = (int)($input['bid_id'] ?? 0);

    $bid = $conn->query("
        SELECT id, status FROM supplier_bids
        WHERE id=$bid_id AND supplier_id=$supplier_id
    ")->fetch_assoc();
    if (!$bid) {
        echo json_encode(['success' => false, 'message' => 'Bid not found']); exit;
    }
    if ($bid['status'] !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Cannot withdraw a bid that has already been decided']); exit;
    }

    $stmt = $conn->prepare("DELETE FROM supplier_bids WHERE id=? AND supplier_id=?");
    $stmt->bind_param('ii', $bid_id, $supplier_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Bid withdrawn']); exit;
}

// ── GET: my purchase orders ───────────────────────
if ($method === 'GET' && $action === 'my_pos') {
    $rows = $conn->query("
        SELECT
            po.id,
            po.po_number,
            po.pr_number,
            po.quantity,
            po.unit_price,
            po.total_amount,
            po.currency,
            po.delivery_date,
            po.status,
            po.created_at,
            pr.mpn,
            pr.category,
            pr.requestor_name
        FROM purchase_orders po
        JOIN purchase_requests pr ON pr.id = po.pr_id
        WHERE po.supplier_id = $supplier_id
        ORDER BY po.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'pos' => $rows]); exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
$conn->close();
