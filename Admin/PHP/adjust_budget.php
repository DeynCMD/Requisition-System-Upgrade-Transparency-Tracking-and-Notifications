<?php
session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'FINANCE') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['type']) || !isset($data['amount']) || !isset($data['reason'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    $type = $data['type'];
    $amount = (float)$data['amount'];
    $reason = trim($data['reason']);
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
        if ($type === 'add') {
            $conn->query("UPDATE finance_budget SET total_budget = total_budget + $amount, remaining_budget = remaining_budget + $amount WHERE id = 1");
            $trans_type = 'add';
        } else if ($type === 'deduct') {
            $avail = $conn->query("SELECT total_budget FROM finance_budget LIMIT 1")->fetch_assoc()['total_budget'] ?? 0;
            if ($amount > $avail) {
                throw new Exception("Cannot deduct more than total budget");
            }
            $conn->query("UPDATE finance_budget SET total_budget = total_budget - $amount, remaining_budget = remaining_budget - $amount WHERE id = 1");
            $trans_type = 'deduct';
        } else {
            throw new Exception("Invalid type");
        }

        // Log
        $conn->query("INSERT INTO budget_transactions (transaction_type, amount, description, performed_by) 
                      VALUES ('$trans_type', $amount, '$reason', $userId)");

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Budget adjusted successfully']);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    $conn->close();

} catch (Exception $e) {
    error_log("Adjust budget error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>