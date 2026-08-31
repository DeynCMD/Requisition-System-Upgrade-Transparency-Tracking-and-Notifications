<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: approver_dashboard.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id > 0 && in_array($action, ['approve', 'reject'])) {
    $new_status = $action === 'approve' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ? AND status = 'pending'");
    $stmt->execute([$new_status, $id]);
    $updated = $stmt->rowCount() > 0;

    if ($updated) {
        header("Location: approver_dashboard.php?success=1");
    } else {
        header("Location: approver_dashboard.php?error=1");
    }
} else {
    header("Location: approver_dashboard.php?error=1");
}
exit;