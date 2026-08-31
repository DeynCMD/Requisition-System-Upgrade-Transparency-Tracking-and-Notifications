<?php
/**
 * PHP Proxy for DigiKey API
 * This forwards requests to your Node.js server
 * Save as: api/digikey/search.php
 */

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, ngrok-skip-browser-warning");
header("Content-Type: application/json");
header("ngrok-skip-browser-warning: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get query parameters
$query = isset($_GET['q']) ? $_GET['q'] : '';
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

if (empty($query)) {
    echo json_encode([]);
    exit();
}

// Forward request to Node.js server (localhost:3000)
$nodeUrl = "http://localhost:3000/api/digikey/search?q=" . urlencode($query) . "&quantity=" . $quantity;

// Use cURL to make the request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $nodeUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to connect to search service',
        'details' => $error
    ]);
    exit();
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
}

// Return the response from Node.js
echo $response;
?>