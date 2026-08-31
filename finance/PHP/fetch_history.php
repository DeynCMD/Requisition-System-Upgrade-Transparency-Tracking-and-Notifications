<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "ze_electronic");

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Get parameters from history.js
$activity_type = $_GET['type'] ?? 'all';
$date_filter = $_GET['date'] ?? '';
$limit = (int) ($_GET['limit'] ?? 50);
$offset = (int) ($_GET['offset'] ?? 0);

// === 1. Purchase Request Activities (from activity_logs) ===
$purchase_query = "
    SELECT 
        id,
        activity_type,
        pr_number,
        description,
        performed_by,
        created_at
    FROM activity_logs
    WHERE 1=1
";

$purchase_params = [];
$purchase_types = "";

if ($activity_type !== 'all') {
    $purchase_query .= " AND activity_type = ?";
    $purchase_params[] = $activity_type;
    $purchase_types .= "s";
}

if ($date_filter) {
    $purchase_query .= " AND DATE(created_at) = ?";
    $purchase_params[] = $date_filter;
    $purchase_types .= "s";
}

$purchase_query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$purchase_params[] = $limit;
$purchase_params[] = $offset;
$purchase_types .= "ii";

$stmt = $conn->prepare($purchase_query);
if ($stmt) {
    if (!empty($purchase_params)) {
        $stmt->bind_param($purchase_types, ...$purchase_params);
    }
    $stmt->execute();
    $purchase_result = $stmt->get_result();
    $purchase_activities = $purchase_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $purchase_activities = [];
}

// === 2. Budget Transactions (from budget_transactions) ===
$budget_query = "
    SELECT 
        id,
        transaction_type AS activity_type,
        amount,
        description,
        performed_by,
        created_at
    FROM budget_transactions
    WHERE 1=1
";

$budget_params = [];
$budget_types = "";

if ($date_filter) {
    $budget_query .= " AND DATE(created_at) = ?";
    $budget_params[] = $date_filter;
    $budget_types .= "s";
}

$budget_query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$budget_params[] = $limit;
$budget_params[] = $offset;
$budget_types .= "ii";

$stmt = $conn->prepare($budget_query);
if ($stmt) {
    if (!empty($budget_params)) {
        $stmt->bind_param($budget_types, ...$budget_params);
    }
    $stmt->execute();
    $budget_result = $stmt->get_result();
    $budget_activities = $budget_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $budget_activities = [];
}

// === Combine both lists ===
$all_activities = array_merge($purchase_activities, $budget_activities);

// Sort by created_at DESC (newest first)
usort($all_activities, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Get total count (approximate - for stats / pagination)
$total_query = "SELECT 
    (SELECT COUNT(*) FROM activity_logs WHERE 1=1) +
    (SELECT COUNT(*) FROM budget_transactions WHERE 1=1) AS total";
$total_result = $conn->query($total_query);
$total = $total_result->fetch_assoc()['total'] ?? count($all_activities);

echo json_encode([
    'success' => true,
    'activities' => $all_activities,
    'total' => $total,
    'showing' => count($all_activities)
]);

$conn->close();
?>