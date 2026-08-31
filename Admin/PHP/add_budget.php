<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'FINANCE') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['amount']) || !isset($data['description'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    $amount = (float) $data['amount'];
    $description = trim($data['description']);
    $userId = $_SESSION['user_id'];

    if ($amount <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
        exit();
    }

    $conn = new mysqli("localhost", "root", "", "ze_electronic");

    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    $conn->begin_transaction();

    try {
        // Update main budget
        $updateQuery = "UPDATE finance_budget SET 
                        total_budget = total_budget + ?,
                        remaining_budget = remaining_budget + ?,
                        updated_by = ?,
                        updated_at = NOW()
                        LIMIT 1";

        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ddi", $amount, $amount, $userId);
        $stmt->execute();
        $stmt->close();

        // Log transaction
        $transQuery = "INSERT INTO budget_transactions 
                       (transaction_type, amount, description, performed_by) 
                       VALUES ('add', ?, ?, ?)";

        $stmt = $conn->prepare($transQuery);
        $stmt->bind_param("dsi", $amount, $description, $userId);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Budget added successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    $conn->close();

} catch (Exception $e) {
    error_log("Add budget error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>