<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    if (!isset($_SESSION['loggedin'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not logged in']);
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "ze_electronic");

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Get user_id from session
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        throw new Exception("User ID not found in session");
    }

    // Fetch purchase requests
    $query = "
        SELECT 
            pr.id,
            pr.pr_number as prNumber,
            pr.requestor_name as requestor,
            'Engineering' as department,
            DATE_FORMAT(pr.request_date, '%Y-%m-%d') as date,
            DATE_FORMAT(pr.required_by, '%Y-%m-%d') as requiredDate,
            pr.status,
            COALESCE(pr.urgency, 'Medium') as urgency,
            COALESCE(pr.reason, '') as reason,
            COALESCE(pr.notes, '') as notes,
            pr.approved_by as approver,
            DATE_FORMAT(pr.approved_at, '%Y-%m-%d') as approvalDate,
            pr.mpn,
            pr.manufacturer,
            pr.quantity,
            pr.unit_price,
            pr.category
        FROM purchase_requests pr
        WHERE pr.requestor_name = 'Current User'
        ORDER BY pr.created_at DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        // Use pr_number or generate ID
        $row['id'] = $row['prNumber'] ?? ("PR-" . date('Y') . "-" . str_pad($row['id'], 3, '0', STR_PAD_LEFT));
        
        // Use session fullname instead of "Current User"
        $row['requestor'] = $_SESSION['fullname'] ?? 'Unknown User';

        // Create items array from main table data
        $items = [];
        if (!empty($row['mpn'])) {
            $qty = (int)($row['quantity'] ?? 0);
            $unitPrice = (float)($row['unit_price'] ?? 0);
            
            $items[] = [
                'mpn' => $row['mpn'],
                'manufacturer' => $row['manufacturer'] ?? 'N/A',
                'description' => $row['category'] ?? '',
                'qty' => $qty,
                'unitPrice' => $unitPrice,
                'total' => $qty * $unitPrice
            ];
        }

        if (!empty($items)) {
            $row['items'] = $items;
            
            // Remove extra fields
            unset($row['prNumber'], $row['mpn'], $row['manufacturer'], $row['quantity'], $row['unit_price'], $row['category']);
            
            $requests[] = $row;
        }
    }

    echo json_encode($requests);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Get PRs error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'requests' => []
    ]);
}
?>