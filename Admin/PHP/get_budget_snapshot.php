<?php
/**
 * get_budget_snapshot.php
 * Returns live budget data for realtime DOM updates.
 * Accessible to ADMIN and FINANCE roles.
 */
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

// Main budget
$main = $conn->query("SELECT * FROM finance_budget WHERE id=1")->fetch_assoc();

// MRO dept budgets
$depts = $conn->query("SELECT * FROM department_budgets ORDER BY id")->fetch_all(MYSQLI_ASSOC);

// Latest 20 MRO transactions
$mrotxs = $conn->query("
    SELECT * FROM budget_transactions
    WHERE department IS NOT NULL AND department != ''
    ORDER BY created_at DESC LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

// Latest 20 all transactions
$txs = $conn->query("
    SELECT * FROM budget_transactions
    ORDER BY created_at DESC LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

$conn->close();

echo json_encode([
    'success' => true,
    'main'    => $main,
    'depts'   => $depts,
    'mrotxs'  => $mrotxs,
    'txs'     => $txs,
]);
