<?php
// suppliers.php — CRUD for suppliers table (with portal credentials)
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', 0);
session_start();

if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['ADMIN'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$conn = new mysqli('localhost', 'root', '', 'ze_electronic');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB error']); exit;
}
$conn->set_charset('utf8mb4');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── LIST ──────────────────────────────────────────
if ($method === 'GET' && $action === 'list') {
    $rows = $conn->query("
        SELECT id, name, contact, email, phone, address, username, active, created_at
        FROM suppliers ORDER BY name ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'suppliers' => $rows]); exit;
}

// ── ADD ───────────────────────────────────────────
if ($method === 'POST' && $action === 'add') {
    $input   = json_decode(file_get_contents('php://input'), true);
    $name    = trim($input['name']     ?? '');
    $contact = trim($input['contact']  ?? '');
    $email   = trim($input['email']    ?? '');
    $phone   = trim($input['phone']    ?? '');
    $address = trim($input['address']  ?? '');
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    $userId  = (int)$_SESSION['user_id'];

    if (!$name)     { echo json_encode(['success' => false, 'message' => 'Supplier name is required']); exit; }
    if (!$username) { echo json_encode(['success' => false, 'message' => 'Portal username is required']); exit; }
    if (!$password) { echo json_encode(['success' => false, 'message' => 'Portal password is required']); exit; }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']); exit;
    }

    // Check username uniqueness
    $dup = $conn->query("SELECT id FROM suppliers WHERE username = '" . $conn->real_escape_string($username) . "'")->num_rows;
    if ($dup > 0) { echo json_encode(['success' => false, 'message' => 'Username already taken']); exit; }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO suppliers (name, contact, email, phone, address, username, password, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssssssi', $name, $contact, $email, $phone, $address, $username, $hashed, $userId);
    $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Supplier added with portal access', 'id' => $id]); exit;
}

// ── RESET PASSWORD ────────────────────────────────
if ($method === 'POST' && $action === 'reset_password') {
    $input      = json_decode(file_get_contents('php://input'), true);
    $id         = (int)($input['id']       ?? 0);
    $newPassword = trim($input['password'] ?? '');

    if (!$id || strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID or password too short (min 6 chars)']); exit;
    }

    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt   = $conn->prepare("UPDATE suppliers SET password = ? WHERE id = ?");
    $stmt->bind_param('si', $hashed, $id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Password reset successfully']); exit;
}

// ── TOGGLE ACTIVE ─────────────────────────────────
if ($method === 'POST' && $action === 'toggle') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $stmt = $conn->prepare("UPDATE suppliers SET active = NOT active WHERE id = ?");
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    echo json_encode(['success' => true]); exit;
}

// ── DELETE ────────────────────────────────────────
if ($method === 'POST' && $action === 'delete') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    // Prevent delete if supplier has POs
    $count = $conn->query("SELECT COUNT(*) as c FROM purchase_orders WHERE supplier_id=$id")->fetch_assoc()['c'];
    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete: supplier has purchase orders']); exit;
    }
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close();
    echo json_encode(['success' => true, 'message' => 'Supplier removed']); exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
$conn->close();
