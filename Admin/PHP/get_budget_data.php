<?php
// ================================================
// IMPORTANT: session_start() MUST be FIRST
// ================================================
session_start();

// ================================================
// CORS Headers (required for ngrok / cross-origin)
// ================================================
header("Access-Control-Allow-Origin: *");           // Use * for testing; later replace with your exact ngrok domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle browser preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Force JSON response
header('Content-Type: application/json');

// ================================================
// Security: ADMIN only
// ================================================
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || $_SESSION['role'] !== 'FINANCE') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized - ADMIN access required'
    ]);
    exit();
}

// ================================================
// Debug mode (disable in production)
// ================================================
ini_set('display_errors', 0);        // Don't show errors to users
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// ================================================
// Database connection
// ================================================
$conn = new mysqli("localhost", "root", "", "ze_electronic");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit();
}

try {
    // ================================================
    // 1. Get latest company budget
    // ================================================
    $budgetSql = "
        SELECT 
            total_budget,
            allocated_budget,
            spent_budget,
            remaining_budget,
            updated_at,
            updated_by
        FROM finance_budget
        ORDER BY id DESC
        LIMIT 1
    ";

    $budgetResult = $conn->query($budgetSql);

    if (!$budgetResult) {
        throw new Exception("Finance budget query failed: " . $conn->error);
    }

    // If no budget record exists → create default
    if ($budgetResult->num_rows === 0) {
        $conn->query("
            INSERT INTO finance_budget 
            (total_budget, allocated_budget, spent_budget, remaining_budget)
            VALUES (0, 0, 0, 0)
        ");

        // Re-fetch after insert
        $budgetResult = $conn->query($budgetSql);
    }

    $budgetRow = $budgetResult->fetch_assoc();

    $budget = [
        'total' => (float) ($budgetRow['total_budget'] ?? 0),
        'allocated' => (float) ($budgetRow['allocated_budget'] ?? 0),
        'spent' => (float) ($budgetRow['spent_budget'] ?? 0),
        'remaining' => (float) ($budgetRow['remaining_budget'] ?? 0),
        'updated_at' => $budgetRow['updated_at'] ?? null,
        'updated_by' => $budgetRow['updated_by'] ?? null
    ];

    // ================================================
    // 2. Get recent 5 transactions
    // ================================================
    $transSql = "
        SELECT 
            id,
            transaction_type,
            amount,
            department,
            description,
            performed_by,
            created_at
        FROM budget_transactions
        ORDER BY created_at DESC
        LIMIT 5
    ";

    $transResult = $conn->query($transSql);

    $transactions = [];

    if ($transResult) {
        while ($row = $transResult->fetch_assoc()) {
            $transactions[] = [
                'id' => (int) $row['id'],
                'type' => $row['transaction_type'],
                'amount' => (float) $row['amount'],
                'department' => $row['department'] ?? 'N/A',
                'description' => $row['description'] ?? '',
                'performed_by' => $row['performed_by'],
                'created_at' => $row['created_at']
            ];
        }
    }

    // ================================================
    // 3. Success response
    // ================================================
    echo json_encode([
        'success' => true,
        'budget' => $budget,
        'transactions' => $transactions
    ]);

} catch (Exception $e) {
    error_log("Budget API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?>