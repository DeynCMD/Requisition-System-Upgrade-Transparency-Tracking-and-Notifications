<?php
// get_price_prediction.php
header('Content-Type: application/json');
require_once 'db.php';  // your database connection file

$mpn = $_GET['mpn'] ?? '';
if (empty($mpn)) {
    echo json_encode(['error' => 'No MPN provided']);
    exit;
}

$mpn = $conn->real_escape_string($mpn);

// Get historical monthly average prices (newest first)
$query = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS month,
        AVG(unit_price) AS avg_price
    FROM purchase_requests 
    WHERE mpn = '$mpn'
      AND status = 'approved' 
      AND unit_price > 0
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
";

$result = $conn->query($query);

$historical_prices = [];
$historical_months = [];

while ($row = $result->fetch_assoc()) {
    $historical_prices[] = (float) $row['avg_price'];
    $historical_months[] = $row['month'];
}

if (empty($historical_prices)) {
    echo json_encode(['error' => 'No price history found for this MPN']);
    exit;
}

// ────────────────────────────────────────────────
// Calculate simulated MAPE
// ────────────────────────────────────────────────
$absolute_percentage_errors = [];
$valid_comparisons = 0;

for ($i = 1; $i < count($historical_prices); $i++) {
    // Use data available BEFORE this point to "predict" the price for month i-1
    $past_prices = array_slice($historical_prices, $i);

    if (count($past_prices) < 3) {
        continue; // not enough history to make a meaningful prediction
    }

    // === Use the SAME prediction logic you use for the current forecast ===
    // Example 1: Simple average
    $past_avg = array_sum($past_prices) / count($past_prices);
    $predicted = $past_avg;

    // Example 2: If you have a trend-based method, use it here instead
    // $predicted = $past_avg * (1 + $your_trend_factor);

    $actual = $historical_prices[$i - 1];

    if ($actual <= 0) {
        continue;
    }

    $ape = abs($actual - $predicted) / $actual;
    $absolute_percentage_errors[] = $ape;
    $valid_comparisons++;
}

$mape = 0;
if ($valid_comparisons > 0) {
    $mape = (array_sum($absolute_percentage_errors) / $valid_comparisons) * 100;
    $mape = round($mape, 2);
}

// ────────────────────────────────────────────────
// Your existing prediction logic (example)
// ────────────────────────────────────────────────
$current_avg_price = array_sum($historical_prices) / count($historical_prices);

// Simple prediction example (replace with your real method)
$predicted_price = $current_avg_price;  // or add trend, etc.

// Example trend calculation
$trend = 0;
if (count($historical_prices) >= 6) {
    $recent = array_slice($historical_prices, 0, 3);
    $older = array_slice($historical_prices, 3, 3);
    if (array_sum($older) > 0) {
        $trend = (array_sum($recent) / count($recent)) / (array_sum($older) / count($older)) - 1;
    }
}
$predicted_price = $current_avg_price * (1 + $trend);

// Confidence (simple example)
$confidence = min(95, 50 + count($historical_prices) * 5);

// ────────────────────────────────────────────────
// Final response
// ────────────────────────────────────────────────
echo json_encode([
    'current_avg_price' => round($current_avg_price, 4),
    'predicted_price' => round($predicted_price, 4),
    'historical_prices' => array_reverse($historical_prices),
    'historical_months' => array_reverse($historical_months),
    'total_orders' => count($historical_prices) * 10, // replace with real count
    'confidence' => $confidence,
    'mape' => $mape,
    'mape_comparisons' => $valid_comparisons,
    'mape_note' => $valid_comparisons > 0 ? 'Simulated on historical data' : 'Not enough data'
]);
?>