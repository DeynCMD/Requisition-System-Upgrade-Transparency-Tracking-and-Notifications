<?php
/**
 * Price Prediction API
 * Uses Linear Regression for price forecasting
 * FIXED: True out-of-sample MAPE backtesting (no leakage)
 */

header('Content-Type: application/json');
session_start();

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

require_once 'db.php';

if (!isset($_GET['mpn']) || empty($_GET['mpn'])) {
    echo json_encode(['error' => 'MPN parameter is required']);
    exit();
}

$mpn = $_GET['mpn'];

// Fetch historical monthly averages
$query = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        AVG(unit_price) as avg_price,
        COUNT(*) as order_count
    FROM purchase_requests 
    WHERE mpn = ?
      AND status = 'approved'
      AND unit_price > 0
    GROUP BY month 
    ORDER BY month ASC
");

$query->bind_param('s', $mpn);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'No historical data found for this part']);
    exit();
}

$historical_data = [];
while ($row = $result->fetch_assoc()) {
    $historical_data[] = $row;
}

// ────────────────────────────────────────────────
// FIXED: True out-of-sample MAPE
// Train on 0 to (i-1), predict i, compare to actual i
// ────────────────────────────────────────────────
$absolute_percentage_errors = [];
$valid_comparisons = 0;

for ($i = 3; $i < count($historical_data); $i++) {
    // Training: months 0 to (i-1) — EXCLUDE month i
    $training_data = array_slice($historical_data, 0, $i);

    if (count($training_data) < 3) {
        continue;
    }

    $x_train = [];
    $y_train = [];
    for ($j = 0; $j < count($training_data); $j++) {
        $x_train[] = $j;
        $y_train[] = (float) $training_data[$j]['avg_price'];
    }

    $regression = linearRegression($x_train, $y_train);
    $slope = $regression['slope'];
    $intercept = $regression['intercept'];

    // Predict month i
    $next_x = count($training_data); // index for month i
    $predicted = $slope * $next_x + $intercept;

    $actual = (float) $historical_data[$i]['avg_price'];

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
// Predict next month using ALL data
// ────────────────────────────────────────────────
$prediction_result = calculatePricePrediction($historical_data);

// Attach MAPE (after prediction — safe)
$prediction_result['mape'] = $mape;
$prediction_result['mape_comparisons'] = $valid_comparisons;
$prediction_result['mape_note'] = $valid_comparisons > 0
    ? "Simulated backtest on $valid_comparisons months"
    : "Not enough data for meaningful MAPE";

echo json_encode($prediction_result);

// ────────────────────────────────────────────────
// Functions (unchanged)
// ────────────────────────────────────────────────

function calculatePricePrediction($data)
{
    $n = count($data);

    if ($n < 2) {
        $current_price = $data[0]['avg_price'];
        return [
            'current_avg_price' => $current_price,
            'predicted_price' => $current_price,
            'total_orders' => array_sum(array_column($data, 'order_count')),
            'historical_months' => [date('M Y', strtotime($data[0]['month'] . '-01'))],
            'historical_prices' => [$current_price],
            'confidence' => 30,
            'method' => 'insufficient_data'
        ];
    }

    $x_values = [];
    $y_values = [];
    $months_labels = [];

    for ($i = 0; $i < $n; $i++) {
        $x_values[] = $i;
        $y_values[] = (float) $data[$i]['avg_price'];
        $months_labels[] = date('M Y', strtotime($data[$i]['month'] . '-01'));
    }

    $regression = linearRegression($x_values, $y_values);
    $slope = $regression['slope'];
    $intercept = $regression['intercept'];
    $r_squared = $regression['r_squared'];

    $next_x = $n;
    $predicted_price = ($slope * $next_x) + $intercept;

    if ($predicted_price < 0) {
        $predicted_price = end($y_values);
    }

    $confidence = calculateConfidence($r_squared, $n);

    $current_price = end($y_values);

    $total_orders = array_sum(array_column($data, 'order_count'));

    return [
        'current_avg_price' => round($current_price, 4),
        'predicted_price' => round($predicted_price, 4),
        'total_orders' => $total_orders,
        'historical_months' => $months_labels,
        'historical_prices' => array_map(fn($p) => round($p, 4), $y_values),
        'confidence' => $confidence,
        'slope' => round($slope, 6),
        'r_squared' => round($r_squared, 4),
        'method' => 'linear_regression',
        'data_points' => $n
    ];
}

function linearRegression($x, $y)
{
    $n = count($x);

    $mean_x = array_sum($x) / $n;
    $mean_y = array_sum($y) / $n;

    $numerator = 0;
    $denominator = 0;

    for ($i = 0; $i < $n; $i++) {
        $numerator += ($x[$i] - $mean_x) * ($y[$i] - $mean_y);
        $denominator += pow($x[$i] - $mean_x, 2);
    }

    $slope = $denominator != 0 ? $numerator / $denominator : 0;
    $intercept = $mean_y - ($slope * $mean_x);

    $ss_tot = 0;
    $ss_res = 0;

    for ($i = 0; $i < $n; $i++) {
        $predicted = ($slope * $x[$i]) + $intercept;
        $ss_tot += pow($y[$i] - $mean_y, 2);
        $ss_res += pow($y[$i] - $predicted, 2);
    }

    $r_squared = $ss_tot != 0 ? 1 - ($ss_res / $ss_tot) : 0;
    $r_squared = max(0, min(1, $r_squared));

    return [
        'slope' => $slope,
        'intercept' => $intercept,
        'r_squared' => $r_squared
    ];
}

function calculateConfidence($r_squared, $data_points)
{
    $base_confidence = $r_squared * 100;

    if ($data_points >= 12) {
        $data_factor = 1.0;
    } elseif ($data_points >= 6) {
        $data_factor = 0.9;
    } elseif ($data_points >= 3) {
        $data_factor = 0.75;
    } else {
        $data_factor = 0.5;
    }

    $confidence = $base_confidence * $data_factor;
    return (int) max(20, min(95, $confidence));
}

$conn->close();
?>