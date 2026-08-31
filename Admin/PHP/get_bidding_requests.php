<?php
// get_bidding_requests.php
// Lists PRs in the bidding stage: status='approved' and finance_status='pending',
// meaning admin has approved but no winning supplier bid has been chosen yet.
// Admin-only JSON endpoint powering the "Bidding" section of the admin dashboard.

ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', '0');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/db.php';

$rows = $conn->query("
    SELECT
        pr.id, pr.pr_number, pr.mpn, pr.manufacturer, pr.category, pr.subcategory,
        pr.quantity, pr.currency, pr.urgency, pr.requestor_name, pr.created_at,
        (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id AND sb.status = 'pending') AS pending_bids,
        (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id) AS total_bids
    FROM purchase_requests pr
    WHERE pr.status         = 'approved'
      AND pr.finance_status = 'pending'
    ORDER BY pr.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'requests' => $rows]);
$conn->close();
