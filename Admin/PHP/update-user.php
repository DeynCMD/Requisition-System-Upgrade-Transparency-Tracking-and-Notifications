<?php
// update-user.php - Modified to include activity logging

$conn = new mysqli("localhost", "root", "", "ze_electronic");

// Include the logging helper
require_once 'log_activity.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo "Invalid request";
    exit;
}

$stmt = $conn->prepare("UPDATE users SET firstname=?, lastname=?, middlename=?, email=?, gender=?, role=? WHERE id=?");

$stmt->bind_param(
    "ssssssi",
    $data['firstname'],
    $data['lastname'],
    $data['middlename'],
    $data['email'],
    $data['gender'],
    $data['role'],
    $data['id']
);

if ($stmt->execute()) {
    // ✨ LOG THE ACTIVITY
    $fullName = trim($data['firstname'] . ' ' . $data['middlename'] . ' ' . $data['lastname']);

    logActivity(
        $conn,
        'user_edited',
        'Admin', // Replace with $_SESSION['username'] if available
        "User {$fullName} information updated",
        "Role: {$data['role']}, Email: {$data['email']}, Gender: {$data['gender']}",
        $fullName,
        null
    );

    echo "User updated successfully!";
} else {
    echo "Error updating user: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>