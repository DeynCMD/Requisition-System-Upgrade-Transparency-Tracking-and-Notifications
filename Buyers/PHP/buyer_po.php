<?php
// buyer_po.php — Buyer: manual PO split + status updates
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);
session_start();

// Use role check consistent with all other buyer pages
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'BUYER') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

require_once '../../Admin/PHP/db.php';
$buyerId = (int)($_SESSION['user_id'] ?? 0);
$input   = json_decode(file_get_contents('php://input'), true);
$action  = $input['action'] ?? 'create_split';

// ── Update PO status ──────────────────────────────
if ($action === 'update_status') {
    $poId   = (int)($input['po_id'] ?? 0);
    $status = $input['status'] ?? '';
    $allowed = ['Issued', 'Received', 'Cancelled'];
    if (!$poId || !in_array($status, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']); exit;
    }
    $stmt = $conn->prepare("UPDATE purchase_orders SET status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param('si', $status, $poId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'message' => "Status updated to $status"]);
    $conn->close(); exit;
}

// ── Create manual split POs ───────────────────────
$prId  = (int)($input['pr_id'] ?? 0);
$lines = $input['lines'] ?? [];

if (!$prId || empty($lines)) {
    echo json_encode(['success' => false, 'message' => 'Missing PR or lines']); exit;
}

// Verify PR exists and is finance-approved (with a winning bid selected)
$pr = $conn->query("
    SELECT * FROM purchase_requests
    WHERE id=$prId
      AND status='finance_approved'
      AND finance_status='approved'
")->fetch_assoc();
if (!$pr) {
    echo json_encode([
        'success' => false,
        'message' => 'PR not found or not yet approved by Finance. A winning bid must be selected and Finance must approve before creating a PO.'
    ]);
    exit;
}

// Get already allocated qty (non-cancelled POs)
$allocRow = $conn->query("
    SELECT COALESCE(SUM(quantity),0) AS allocated
    FROM purchase_orders
    WHERE pr_id=$prId AND status != 'Cancelled'
")->fetch_assoc();
$existingAlloc = (int)($allocRow['allocated'] ?? 0);

// Validate new allocation doesn't exceed remaining
$newQty = array_sum(array_column($lines, 'quantity'));
if ($existingAlloc + $newQty > (int)$pr['quantity']) {
    $remaining = (int)$pr['quantity'] - $existingAlloc;
    echo json_encode(['success' => false, 'message' => "Allocation ($newQty) exceeds remaining quantity ($remaining)"]); exit;
}

// Validate each line
foreach ($lines as $line) {
    $suppId = (int)($line['supplier_id'] ?? 0);
    $qty    = (int)($line['quantity'] ?? 0);
    $price  = (float)($line['unit_price'] ?? 0);
    $date   = trim($line['delivery_date'] ?? '');
    if (!$suppId || $qty < 1 || $price <= 0 || !$date) {
        echo json_encode(['success' => false, 'message' => 'Invalid line data']); exit;
    }
    // Verify supplier exists and is active
    $suppCheck = $conn->query("SELECT name FROM suppliers WHERE id=$suppId AND active=1")->fetch_assoc();
    if (!$suppCheck) {
        echo json_encode(['success' => false, 'message' => "Supplier ID $suppId not found or inactive"]); exit;
    }
}

// Generate POs
$year   = date('Y');
$seqRow = $conn->query("SELECT COUNT(*) AS c FROM purchase_orders WHERE po_number LIKE 'PO-$year-%'")->fetch_assoc();
$seq    = (int)($seqRow['c'] ?? 0);

$conn->begin_transaction();
try {
    $poNumbers = [];
    foreach ($lines as $line) {
        $seq++;
        $poNumber   = "PO-$year-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
        $suppId     = (int)$line['supplier_id'];
        $qty        = (int)$line['quantity'];
        $price      = (float)$line['unit_price'];
        $date       = $conn->real_escape_string(trim($line['delivery_date']));
        $total      = round($price * $qty, 2);
        $currency   = $pr['currency'];
        $prNumber   = $pr['pr_number'];

        $suppName = $conn->query("SELECT name FROM suppliers WHERE id=$suppId")->fetch_assoc()['name'];
        $suppNameEsc = $conn->real_escape_string($suppName);

        $poNumber_e   = $conn->real_escape_string($poNumber);
        $prNumber_e   = $conn->real_escape_string($prNumber);
        $currency_e   = $conn->real_escape_string($currency);

        $conn->query("
            INSERT INTO purchase_orders
              (po_number, pr_id, pr_number, supplier_id, supplier_name, quantity,
               unit_price, total_amount, currency, delivery_date, created_by)
            VALUES
              ('$poNumber_e', $prId, '$prNumber_e', $suppId, '$suppNameEsc',
               $qty, $price, $total, '$currency_e', '$date', $buyerId)
        ");
        if ($conn->error) throw new Exception($conn->error);
        $poNumbers[] = $poNumber;
    }
    $conn->commit();
    echo json_encode(['success' => true, 'message' => count($poNumbers) . ' PO(s) created', 'po_numbers' => $poNumbers]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
