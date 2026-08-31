<?php
session_start();
header('Content-Type: application/json');

require_once 'db.php'; // Your database connection
require_once 'log_activity.php'; // Your logging helper

try {
    // Check if user is logged in and is ADMIN
    if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'ADMIN') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit();
    }

    // Get user ID from POST
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No ID provided']);
        exit();
    }

    // Optional: Prevent deleting self
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete yourself']);
        exit();
    }

    // Get user info BEFORE deleting (for logging)
    $stmt = $conn->prepare("SELECT firstname, lastname, middlename, role, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    $user = $result->fetch_assoc();
    $fullName = trim($user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname']);

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Log the activity
        logActivity(
            $conn,
            'user_deleted',
            $_SESSION['username'] ?? 'Admin', // Use logged-in admin's username
            "User {$fullName} was removed from the system",
            "Previous role: {$user['role']}, Email: {$user['email']}",
            $fullName,
            null
        );

        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>