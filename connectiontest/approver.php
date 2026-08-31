<?php
require 'db.php';

$requests = $pdo->query("
    SELECT pr_number, item, quantity, total_amount, created_at
    FROM purchase_requests
    WHERE status = 'pending'
    ORDER BY created_at DESC
")->fetchAll();
?>

<div class="table-card">
    <div class="table-header">
        <i class="fas fa-clock"></i> Pending Requests
    </div>

    <?php if (empty($requests)): ?>
        <p style="text-align:center; padding:40px 0; color:#888;">
            No pending requests at this time
        </p>
    <?php else: ?>
    <table class="approval-table">
        <thead>
            <tr>
                <th>PR#</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Total (₱)</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $req): ?>
            <tr>
                <td><strong><?= htmlspecialchars($req['pr_number']) ?></strong></td>
                <td><?= htmlspecialchars($req['item']) ?></td>
                <td><?= number_format($req['quantity']) ?></td>
                <td><?= number_format($req['total_amount'], 2) ?></td>
                <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>