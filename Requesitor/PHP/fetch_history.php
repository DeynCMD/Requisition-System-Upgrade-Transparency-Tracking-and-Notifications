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

// Get filter parameters
$activity_type = $_GET['type'] ?? 'all';
$date_filter = $_GET['date'] ?? '';
$limit = (int) ($_GET['limit'] ?? 20);
$offset = (int) ($_GET['offset'] ?? 0);

// Define which types are request-related
$request_types = [
    'request_created',
    'request_approved',
    'request_rejected',
    'request_pending',
    'request_cancelled'
];

// Build WHERE clause
$where = [];
$params = [];
$types = "";

// Type filter
if ($activity_type !== 'all') {
    // Specific type selected → use exactly that one
    $where[] = "activity_type = ?";
    $params[] = $activity_type;
    $types .= "s";
} else {
    // When "all" is selected → only request types
    $placeholders = implode(',', array_fill(0, count($request_types), '?'));
    $where[] = "activity_type IN ($placeholders)";
    $params = array_merge($params, $request_types);
    $types .= str_repeat("s", count($request_types));
}

// Date filter
if (!empty($date_filter)) {
    $where[] = "DATE(created_at) = ?";
    $params[] = $date_filter;
    $types .= "s";
}

// Main query
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

// For total count (used for pagination)
$count_query = "SELECT COUNT(*) as total FROM activity_logs WHERE 1=1";
$count_params = [];
$count_types = "";

if ($activity_type !== 'all') {
    $count_query .= " AND activity_type = ?";
    $count_params[] = $activity_type;
    $count_types .= "s";
} else {
    $placeholders = implode(',', array_fill(0, count($request_types), '?'));
    $count_query .= " AND activity_type IN ($placeholders)";
    $count_params = array_merge($count_params, $request_types);
    $count_types .= str_repeat("s", count($request_types));
}

if (!empty($date_filter)) {
    $count_query .= " AND DATE(created_at) = ?";
    $count_params[] = $date_filter;
    $count_types .= "s";
}

$count_stmt = $conn->prepare($count_query);
if ($count_stmt) {
    if (!empty($count_types)) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'] ?? 0;
    $count_stmt->close();
} else {
    $total = 0;
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