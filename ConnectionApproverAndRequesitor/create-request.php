<?php
require 'db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = trim($_POST['item'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);

    if ($item && $quantity > 0 && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO requests (item, quantity, unit_price) VALUES (?, ?, ?)");
        $stmt->execute([$item, $quantity, $price]);
        $message = 'Request submitted successfully!';
    } else {
        $message = 'Please fill all fields correctly.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Request</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        form { display: flex; flex-direction: column; gap: 15px; }
        input, textarea { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .message { padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Submit New Request</h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'success') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Item Description:</label>
        <textarea name="item" required></textarea>

        <label>Quantity:</label>
        <input type="number" name="quantity" min="1" required>

        <label>Price per Unit:</label>
        <input type="number" name="price" step="0.01" min="0.01" required>

        <button type="submit">Submit</button>
    </form>
</body>
</html>