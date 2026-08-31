<?php
header('Content-Type: application/json');

// Security: only allow admins or add your own auth
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get the search term from frontend
$query = $_GET['q'] ?? '';
if (empty($query)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing query parameter']);
    exit;
}

// Your DigiKey API key and endpoint
$apiKey = 'YOUR_DIGIKEY_API_KEY_HERE'; // ← put your real key
$url = "https://api.digikey.com/Search/v3/Products/Keyword?keywords=" . urlencode($query);

// Forward headers if needed (e.g. Accept-Language, etc.)
$headers = [
    'X-DIGIKEY-Client-Id: YOUR_CLIENT_ID',
    'Authorization: Bearer ' . $apiKey,
    'Accept: application/json'
];

// Use cURL to fetch from DigiKey
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Pass the response back to frontend
http_response_code($httpCode);
echo $response;
?>