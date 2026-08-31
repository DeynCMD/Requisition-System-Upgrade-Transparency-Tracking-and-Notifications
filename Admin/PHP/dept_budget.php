<?php
// dept_budget.php — manage MRO department budgets
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['ADMIN','FINANCE'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$conn = new mysqli('localhost', 'root', '', 'ze_electronic');
if ($conn->connect_error) { echo json_encode(['success' => false, 'message' => 'DB error']); exit; }
$conn->set_charset('utf8mb4');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── List all dept budgets ─────────────────────────
if ($method === 'GET' && $action === 'list') {
    $rows = $conn->query("SELECT * FROM department_budgets ORDER BY id")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'budgets' => $rows]); exit;
}

// ── Allocate budget to a department ──────────────
if ($method === 'POST' && $action === 'allocate') {
    $input      = json_decode(file_get_contents('php://input'), true);
    $dept       = trim($input['department'] ?? '');
    $amount     = (float)($input['amount'] ?? 0);
    $desc       = trim($input['description'] ?? '');
    $userId     = (int)$_SESSION['user_id'];

    if (!$dept || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']); exit;
    }

    // Check main budget has enough remaining
    $main = $conn->query("SELECT remaining_budget FROM finance_budget WHERE id=1")->fetch_assoc();
    if (!$main || $main['remaining_budget'] < $amount) {
        echo json_encode(['success' => false, 'message' => 'Insufficient main budget']); exit;
    }

    $conn->begin_transaction();
    try {
        // Deduct from main budget
        $conn->query("UPDATE finance_budget SET allocated_budget=allocated_budget+$amount, remaining_budget=remaining_budget-$amount WHERE id=1");

        // Add to dept budget
        $conn->query("
            INSERT INTO department_budgets (department_name, allocated_amount, remaining_amount, created_by)
            VALUES ('$dept', $amount, $amount, $userId)
            ON DUPLICATE KEY UPDATE
              allocated_amount = allocated_amount + $amount,
              remaining_amount = remaining_amount + $amount,
              updated_at = NOW()
        ");

        // Log transaction
        $logDesc = $desc ?: "Budget allocated to $dept";
        $conn->query("INSERT INTO budget_transactions (transaction_type,amount,department,description,performed_by) VALUES ('allocate',$amount,'$dept','$logDesc',$userId)");

        $conn->commit();
        echo json_encode(['success' => true, 'message' => "₱" . number_format($amount,2) . " allocated to $dept"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
$conn->close();
