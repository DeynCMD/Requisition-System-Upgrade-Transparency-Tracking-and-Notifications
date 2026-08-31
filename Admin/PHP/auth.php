<?php
ini_set('session.cookie_samesite', 'Lax');
session_start();

// DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "ze_electronic");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../HTML/ZE-Electronics.php?error=invalid");
    exit();
}

// Get input
$input = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Basic input validation
if (empty($input) || empty($password)) {
    header("Location: ../HTML/ZE-Electronics.php?error=empty");
    exit();
}

// =======================
//    SIMPLE RATE LIMITING (session-based)
// =======================
$attempt_key = 'login_attempts_' . md5($input);

if (!isset($_SESSION[$attempt_key])) {
    $_SESSION[$attempt_key] = ['count' => 0, 'time' => time()];
}

$attempts = &$_SESSION[$attempt_key];

// Check rate limiting: 5 attempts in 5 minutes
if ($attempts['count'] >= 5 && (time() - $attempts['time']) < 300) {
    $wait_time = 300 - (time() - $attempts['time']);
    $wait_minutes = ceil($wait_time / 60);
    header("Location: ../HTML/ZE-Electronics.php?error=too_many_attempts&wait=$wait_minutes");
    exit();
}

// =======================
//    STEP 1 — CHECK SYSTEM USERS TABLE
// =======================
$stmt = $conn->prepare("
    SELECT id, firstname, lastname, middlename, username, email, password, role, gender, is_active 
    FROM users 
    WHERE (username = ? OR email = ?) AND is_active = 1
    LIMIT 1
");

if (!$stmt) {
    error_log("MySQL prepare error: " . $conn->error);
    header("Location: ../HTML/ZE-Electronics.php?error=system");
    exit();
}

$stmt->bind_param("ss", $input, $input);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password'])) {

    // ✅ SYSTEM USER LOGIN SUCCESS
    session_regenerate_id(true);

    $_SESSION['loggedin']      = true;
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['username']      = $user['username'];
    $_SESSION['email']         = $user['email'];
    $_SESSION['role']          = strtoupper(trim($user['role']));
    $_SESSION['fullname']      = trim("{$user['firstname']} " .
        (!empty($user['middlename']) ? $user['middlename'] . " " : "") .
        $user['lastname']);
    $_SESSION['gender']        = $user['gender'] ?? '—';
    $_SESSION['csrf_token']    = bin2hex(random_bytes(32));
    $_SESSION['last_activity'] = time();

    unset($_SESSION[$attempt_key]);

    // Log successful login
    try {
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (activity_type, user_id, performed_by, description, ip_address) VALUES ('login', ?, ?, 'User logged in successfully', ?)");
        if ($log_stmt) {
            $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $performed = $user['username'];
            $log_stmt->bind_param("iss", $user['id'], $performed, $ip);
            $log_stmt->execute();
            $log_stmt->close();
        }
    } catch (Exception $e) {
        error_log("Activity log error: " . $e->getMessage());
    }

    $conn->close();

    // Redirect based on role
    switch ($_SESSION['role']) {
        case 'ADMIN':
            header("Location: ../HTML/AdminZE.php");
            exit();
        case 'REQUESTOR':
            header("Location: ../../Requesitor/HTML/requestor-dashboard.php");
            exit();
        case 'FINANCE':
            header("Location: ../../finance/HTML/finance-dashboard.php");
            exit();
        case 'BUYER':
            header("Location: ../../Buyers/HTML/buyer_dashboard.php");
            exit();
        default:
            header("Location: ../HTML/ZE-Electronics.php?error=norole");
            exit();
    }
}

// =======================
//    STEP 2 — CHECK SUPPLIERS TABLE (portal login)
// =======================
$sup_stmt = $conn->prepare("
    SELECT id, name, username, password, active
    FROM suppliers
    WHERE username = ?
    LIMIT 1
");

if ($sup_stmt) {
    $sup_stmt->bind_param("s", $input);
    $sup_stmt->execute();
    $supplier = $sup_stmt->get_result()->fetch_assoc();
    $sup_stmt->close();

    if ($supplier && password_verify($password, $supplier['password'])) {

        if (!$supplier['active']) {
            $attempts['count']++;
            $attempts['time'] = time();
            $conn->close();
            header("Location: ../HTML/ZE-Electronics.php?error=inactive");
            exit();
        }

        // ✅ SUPPLIER LOGIN SUCCESS
        session_regenerate_id(true);

        $_SESSION['supplier_logged_in'] = true;
        $_SESSION['supplier_id']        = (int)$supplier['id'];
        $_SESSION['supplier_name']      = $supplier['name'];
        $_SESSION['supplier_username']  = $supplier['username'];

        unset($_SESSION[$attempt_key]);

        $conn->close();
        header("Location: ../../Supplier/HTML/supplier_dashboard.php");
        exit();
    }
}

// =======================
//    ❌ NO MATCH FOUND
// =======================
$attempts['count']++;
$attempts['time'] = time();

// Log failed attempt
try {
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (activity_type, performed_by, description, ip_address) VALUES ('login', ?, 'Failed login - user not found or wrong password', ?)");
    if ($log_stmt) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log_stmt->bind_param("ss", $input, $ip);
        $log_stmt->execute();
        $log_stmt->close();
    }
} catch (Exception $e) {
    error_log("Activity log error: " . $e->getMessage());
}

$conn->close();
header("Location: ../HTML/ZE-Electronics.php?error=wrong");
exit();