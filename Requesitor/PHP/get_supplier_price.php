<?php
/**
 * get_supplier_price.php
 * Returns the most practical (lowest) supplier bid prices for a given MPN,
 * plus the historical average and a simple price trend, accessible by REQUESTOR role.
 *
 * GET ?mpn=BAV99LT1G
 */
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['loggedin'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']); exit;
}

$mpn = trim($_GET['mpn'] ?? '');
if (!$mpn) {
    echo json_encode(['success' => false, 'message' => 'MPN is required']); exit;
}

$conn = new mysqli('localhost', 'root', '', 'ze_electronic');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB error']); exit;
}
$conn->set_charset('utf8mb4');

// ── 1. Supplier bids for this MPN (via supplier_bids → purchase_requests) ────
$stmt = $conn->prepare("
    SELECT
        s.name          AS supplier_name,
        sb.unit_price,
        sb.delivery_date,
        sb.notes,
        pr.currency,
        pr.created_at   AS bid_date
    FROM supplier_bids sb
    JOIN suppliers      s  ON s.id  = sb.supplier_id
    JOIN purchase_requests pr ON pr.id = sb.pr_id
    WHERE pr.mpn = ?
      AND s.active = 1
    ORDER BY sb.unit_price ASC
");
$stmt->bind_param('s', $mpn);
$stmt->execute();
$bids = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── 2. Historical avg price from approved PRs ─────────────────────────────────
$stmt2 = $conn->prepare("
    SELECT
        AVG(unit_price)  AS avg_price,
        MIN(unit_price)  AS min_price,
        MAX(unit_price)  AS max_price,
        COUNT(*)         AS order_count,
        MAX(created_at)  AS last_ordered
    FROM purchase_requests
    WHERE mpn = ?
      AND status IN ('approved','finance_approved')
      AND unit_price > 0
");
$stmt2->bind_param('s', $mpn);
$stmt2->execute();
$hist = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

// ── 3. Last 6 monthly averages for a simple trend ────────────────────────────
$stmt3 = $conn->prepare("
    SELECT
        DATE_FORMAT(created_at, '%Y-%m') AS month,
        AVG(unit_price)                  AS avg_price
    FROM purchase_requests
    WHERE mpn = ?
      AND status IN ('approved','finance_approved')
      AND unit_price > 0
    GROUP BY month
    ORDER BY month DESC
    LIMIT 6
");
$stmt3->bind_param('s', $mpn);
$stmt3->execute();
$monthly = array_reverse($stmt3->get_result()->fetch_all(MYSQLI_ASSOC));
$stmt3->close();

// ── 4. Determine trend direction ──────────────────────────────────────────────
$trend = 'stable';
$trendPct = 0;
if (count($monthly) >= 2) {
    $oldest = (float)$monthly[0]['avg_price'];
    $newest = (float)end($monthly)['avg_price'];
    if ($oldest > 0) {
        $trendPct = round((($newest - $oldest) / $oldest) * 100, 2);
        if ($trendPct > 2)       $trend = 'rising';
        elseif ($trendPct < -2)  $trend = 'falling';
    }
}

// ── 5. Best / recommended price ───────────────────────────────────────────────
$bestBid      = !empty($bids) ? $bids[0] : null;   // already sorted ASC
$historicalAvg = $hist['order_count'] > 0 ? round((float)$hist['avg_price'], 4) : null;

// "Most practical" = lowest supplier bid if available, else historical avg
$recommendedPrice  = $bestBid ? (float)$bestBid['unit_price'] : $historicalAvg;
$recommendedSource = $bestBid ? $bestBid['supplier_name']     : 'Historical Average';

$conn->close();

echo json_encode([
    'success'            => true,
    'mpn'                => $mpn,
    'recommended_price'  => $recommendedPrice,
    'recommended_source' => $recommendedSource,
    'supplier_bids'      => $bids,
    'historical' => [
        'avg_price'   => $hist['order_count'] > 0 ? round((float)$hist['avg_price'], 4)  : null,
        'min_price'   => $hist['order_count'] > 0 ? round((float)$hist['min_price'], 4)  : null,
        'max_price'   => $hist['order_count'] > 0 ? round((float)$hist['max_price'], 4)  : null,
        'order_count' => (int)($hist['order_count'] ?? 0),
        'last_ordered'=> $hist['last_ordered'] ?? null,
    ],
    'trend' => [
        'direction' => $trend,
        'percent'   => $trendPct,
        'monthly'   => $monthly,
    ],
]);
