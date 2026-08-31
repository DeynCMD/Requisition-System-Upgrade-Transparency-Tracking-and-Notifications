<?php
require 'db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item     = trim($_POST['item'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $price    = (float)($_POST['price'] ?? 0);

    if ($item && $quantity > 0 && $price > 0) {
        $year = date('Y');
        $count = $pdo->query("SELECT COUNT(*) FROM purchase_requests WHERE YEAR(created_at) = $year")->fetchColumn() + 1;
        $pr_number = "PR-$year-" . str_pad($count, 3, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare("INSERT INTO purchase_requests (pr_number, item, quantity, unit_price) VALUES (?,?,?,?)");
        $stmt->execute([$pr_number, $item, $quantity, $price]);

        $message = "Request submitted successfully! PR#: $pr_number";
    } else {
        $message = "Please fill all required fields correctly";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Request — Procurement System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="style.css" /> <!-- your existing css -->
</head>
<body>

<div class="container">
    <!-- Your sidebar (copy from your dashboard) -->
    <aside class="sidebar">
        <div class="profile">
            <img src="../Assets/Avatar.jpg" alt="Requestor" />
            <span class="role">REQUESTOR</span>
        </div>
        <nav class="nav-menu">
            <ul>
                <li><a href="requestor-dashboard.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="create-request.php" class="active"><i class="fas fa-plus-circle"></i> Create Request</a></li>
                <li><a href="request-tracking.html"><i class="fas fa-search"></i> Request Tracking</a></li>
                <li><a href="HistoryZE.html"><i class="fas fa-history"></i> History</a></li>
            </ul>
        </nav>
        <button class="logout-btn">LOGOUT</button>
    </aside>

    <main class="main-content">
        <h1>Create New Purchase Request</h1>

        <?php if ($message): ?>
        <div style="padding: 15px; margin: 20px 0; border-radius: 8px; background:#2a2a3a; color: <?= strpos($message, 'success') !== false ? '#4ade80' : '#f87171' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Item Description</label>
                <textarea name="item" required rows="4"></textarea>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" min="1" required>
            </div>

            <div class="form-group">
                <label>Unit Price (₱)</label>
                <input type="number" name="price" step="0.01" min="0.01" required>
            </div>

            <button type="submit" class="btn-submit">Submit Request</button>
        </form>
    </main>
</div>

</body>
</html>