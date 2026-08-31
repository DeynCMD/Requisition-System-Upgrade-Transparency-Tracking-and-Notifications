<?php
// Include this at the top of every supplier portal page
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['supplier_logged_in'])) {
    header('Location: ../../Admin/HTML/ZE-Electronics.php');
    exit();
}

// Convenience variables available after including this file
$supplier_id   = (int)$_SESSION['supplier_id'];
$supplier_name = $_SESSION['supplier_name'] ?? 'Supplier';
