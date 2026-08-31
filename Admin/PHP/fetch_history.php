<?php
header('Content-Type: application/json');
session_start();

// Only authenticated users can access activity logs
if (!isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ze_electronic");

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Get filter parameters
$activity_type = $_GET['type'] ?? 'all';
$date_filter = $_GET['date'] ?? '';
$limit = (int) ($_GET['limit'] ?? 20);
$offset = (int) ($_GET['offset'] ?? 0);

// Build WHERE clause - NO hard restriction on activity_type
$where = [];
$params = [];
$types = "";

// Specific activity type filter (if selected in dropdown)
if ($activity_type !== 'all') {
    $where[] = "activity_type = ?";
    $params[] = $activity_type;
    $types .= "s";
}

// Date filter
if (!empty($date_filter)) {
    $where[] = "DATE(created_at) = ?";
    $params[] = $date_filter;
    $types .= "s";
}

// Main query - returns ALL types unless filtered
$query = "SELECT * FROM activity_logs";
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Prepare & execute
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
}

// Total count using prepared statement
$count_query = "SELECT COUNT(*) as total FROM activity_logs WHERE 1=1";
$count_params = [];
$count_types = "";
if ($activity_type !== 'all') {
    $count_query .= " AND activity_type = ?";
    $count_params[] = $activity_type;
    $count_types .= "s";
}
if (!empty($date_filter)) {
    $count_query .= " AND DATE(created_at) = ?";
    $count_params[] = $date_filter;
    $count_types .= "s";
}
$count_stmt = $conn->prepare($count_query);
$total = 0;
if ($count_stmt) {
    if (!empty($count_params)) $count_stmt->bind_param($count_types, ...$count_params);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $count_stmt->close();
}

echo json_encode([
    'success' => true,
    'activities' => $activities,
    'total' => $total,
    'showing' => count($activities)
]);

$stmt->close();
$conn->close();
?>