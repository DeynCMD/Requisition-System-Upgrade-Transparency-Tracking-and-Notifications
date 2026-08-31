<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'] ?? '', ['ADMIN'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "ze_electronic");

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

// Query using ONLY existing columns from your table
$result = $conn->query("
    SELECT 
        id,
        pr_number,
        requestor_name,
        request_date,
        category,
        subcategory,
        mpn,
        quantity,
        unit_price,
        currency,
        distributor,
        selected_distributor_text,
        reason
    FROM purchase_requests 
    WHERE status = 'PENDING'
    ORDER BY request_date DESC
");

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

echo json_encode([
    "success" => true,
    "requests" => $requests
]);

$conn->close();