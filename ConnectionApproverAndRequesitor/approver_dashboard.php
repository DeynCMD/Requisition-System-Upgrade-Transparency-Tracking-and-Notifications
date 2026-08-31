<?php
require 'db.php';

$success = isset($_GET['success']);
$error = isset($_GET['error']);

$stmt = $pdo->query("SELECT * FROM requests WHERE status = 'pending' ORDER BY created_at DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approver Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f2f2f2; }
        button { padding: 6px 12px; margin-right: 5px; border: none; color: white; cursor: pointer; }
        .approve { background: green; }
        .reject { background: red; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .success { background: lightgreen; }
        .error { background: lightcoral; }
        .no-requests { text-align: center; padding: 20px; color: gray; }
    </style>
</head>
<body>
    <h1>Pending Requests</h1>

    <?php if ($success): ?>
        <div class="message success">Status updated successfully!</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="message error">Error updating status.</div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <p class="no-requests">No pending requests.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?= $req['id'] ?></td>
                        <td><?= htmlspecialchars($req['item']) ?></td>
                        <td><?= $req['quantity'] ?></td>
                        <td><?= number_format($req['total_amount'], 2) ?></td>
                        <td><?= $req['created_at'] ?></td>
                        <td>
                            <form method="POST" action="update_status.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $req['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="approve">Approve</button>
                            </form>
                            <form method="POST" action="update_status.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $req['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>