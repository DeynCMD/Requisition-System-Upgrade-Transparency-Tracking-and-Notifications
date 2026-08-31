<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    $conn = new mysqli("localhost", "root", "", "ze_electronic");

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    require_once 'log_activity.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    $required = ['firstname', 'lastname', 'username', 'email', 'password', 'role'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            throw new Exception("Missing required field: $field");
        }
    }

    $firstname = trim($data['firstname']);
    $lastname = trim($data['lastname']);
    $middlename = trim($data['middlename'] ?? '');
    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];
    $gender = trim($data['gender'] ?? '—');
    $role = strtoupper(trim($data['role']));

    $allowedRoles = ['ADMIN', 'REQUESTOR', 'FINANCE', 'BUYER'];
    if (!in_array($role, $allowedRoles)) {
        throw new Exception("Invalid role");
    }

    // Server-side password validation
    if (strlen($password) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }
    if (!preg_match('/[A-Z]/', $password)) {
        throw new Exception("Password must contain at least 1 uppercase letter");
    }
    if (preg_match_all('/[0-9]/', $password) < 3) {
        throw new Exception("Password must contain at least 3 numbers");
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        throw new Exception("Password must contain at least 1 special character");
    }

    // Check duplicate username or email
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        throw new Exception("Username or email already exists");
    }
    $stmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO users 
        (firstname, lastname, middlename, username, email, password, gender, role)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssssss",
        $firstname,
        $lastname,
        $middlename,
        $username,
        $email,
        $hashedPassword,
        $gender,
        $role
    );

    if (!$stmt->execute()) {
        throw new Exception("Error adding user: " . $stmt->error);
    }

    // Log activity
    $fullName = trim("$firstname $middlename $lastname");
    logActivity(
        $conn,
        'user_added',
        $_SESSION['username'] ?? 'Admin',
        "New user $fullName added as $role",
        "Username: $username, Email: $email, Gender: $gender",
        $fullName,
        null
    );

    echo json_encode([
        'success' => true,
        'message' => 'User added successfully'
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>