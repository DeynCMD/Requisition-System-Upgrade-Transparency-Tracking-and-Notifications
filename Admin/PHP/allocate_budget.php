<?php
session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'ADMIN') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['department']) || !isset($data['amount']) || !isset($data['description'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    $department = trim($data['department']);
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
        // Check available budget
        $avail = $conn->query("SELECT remaining_budget FROM finance_budget LIMIT 1")->fetch_assoc()['remaining_budget'] ?? 0;
        if ($amount > $avail) {
            throw new Exception("Insufficient budget");
        }

        // Update main budget
        $conn->query("UPDATE finance_budget SET allocated_budget = allocated_budget + $amount, remaining_budget = remaining_budget - $amount WHERE id = 1");

        // Update department
        $conn->query("INSERT INTO department_budgets (department_name, allocated_amount, spent_amount, remaining_amount, created_by) 
                      VALUES ('$department', $amount, 0, $amount, $userId) 
                      ON DUPLICATE KEY UPDATE allocated_amount = allocated_amount + $amount, remaining_amount = remaining_amount + $amount, updated_at = NOW()");

        // Log transaction
        $conn->query("INSERT INTO budget_transactions (transaction_type, amount, department, description, performed_by) 
                      VALUES ('allocate', $amount, '$department', '$description', $userId)");

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Budget allocated successfully']);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    $conn->close();

} catch (Exception $e) {
    error_log("Allocate budget error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>