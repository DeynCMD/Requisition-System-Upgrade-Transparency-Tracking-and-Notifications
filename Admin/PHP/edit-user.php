<?php
header('Content-Type: application/json');

// Include your database connection
require_once 'db.php';  // Adjust path if needed (e.g. '../PHP/db.php')

// Read JSON input from JS (fetch POST body)
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!$data || !isset($data['id']) || (int) $data['id'] <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing user ID'
    ]);
    exit;
}

$id = (int) $data['id'];

// Prepare values (empty fields become NULL or empty string)
$firstname = $data['firstname'] ?? '';
$lastname = $data['lastname'] ?? '';
$middlename = $data['middlename'] ?? '';
$username = $data['username'] ?? '';     // ← Now handled
$email = $data['email'] ?? '';
$gender = $data['gender'] ?? '';
$role = $data['role'] ?? '';

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("
    UPDATE users 
    SET 
        firstname   = ?,
        lastname    = ?,
        middlename  = ?,
        username    = ?,
        email       = ?,
        gender      = ?,
        role        = ?
    WHERE id = ?
");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Prepare failed: ' . $conn->error
    ]);
    exit;
}

// Bind parameters (s = string, i = integer)
$stmt->bind_param(
    "sssssssi",
    $firstname,
    $lastname,
    $middlename,
    $username,
    $email,
    $gender,
    $role,
    $id
);

// Execute
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made (user may not exist or no data changed)'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Update failed: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>