<?php
require_once '../PHP/supplier_guard.php';
require_once '../../Admin/PHP/db.php';
require_once '../../Admin/PHP/currency_config.php';

$total_bids   = $conn->query("SELECT COUNT(*) AS c FROM supplier_bids WHERE supplier_id=$supplier_id")->fetch_assoc()['c'] ?? 0;
$won_bids     = $conn->query("SELECT COUNT(*) AS c FROM supplier_bids WHERE supplier_id=$supplier_id AND status='selected'")->fetch_assoc()['c'] ?? 0;
$open_prs     = $conn->query("SELECT COUNT(*) AS c FROM purchase_requests WHERE status='approved'")->fetch_assoc()['c'] ?? 0;
$pending_bids = $conn->query("SELECT COUNT(*) AS c FROM supplier_bids WHERE supplier_id=$supplier_id AND status='pending'")->fetch_assoc()['c'] ?? 0;

$recent_bids = $conn->query("
    SELECT sb.unit_price, sb.delivery_date, sb.status, sb.created_at,
           pr.pr_number, pr.mpn, pr.quantity, pr.currency
    FROM supplier_bids sb
    JOIN purchase_requests pr ON pr.id = sb.pr_id
    WHERE sb.supplier_id = $supplier_id
    ORDER BY sb.created_at DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$recent_pos = $conn->query("
    SELECT po.po_number, po.total_amount, po.currency, po.delivery_date, po.status, po.created_at, pr.mpn
    FROM purchase_orders po
    JOIN purchase_requests pr ON pr.id = po.pr_id
    WHERE po.supplier_id = $supplier_id
    ORDER BY po.created_at DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Dashboard — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="../CSS/supplier_style.css?v=<?= time() ?>">
</head>
<body>
<div class="container">

  <aside class="sidebar">
    <div class="profile">
      <img src="../../Admin/Assets/Avatar.jpg" alt="Supplier"/>
      <span class="role">SUPPLIER</span>
    </div>
    <nav class="nav-menu"><ul>
      <li><a href="supplier_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="supplier_open_requests.php"><i class="fas fa-folder-open"></i> Open Requests</a></li>
      <li><a href="supplier_my_bids.php"><i class="fas fa-gavel"></i> My Bids</a></li>
      <li><a href="supplier_my_pos.php"><i class="fas fa-file-invoice"></i> Purchase Orders</a></li>
    </ul></nav>
    <a href="../PHP/supplier_logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <header><h1 style="color:var(--green);font-size:2.2rem;margin-bottom:6px;">Dashboard</h1></header>
    <p style="color:#9a9ab5;margin-bottom:30px;">Welcome back, <strong style="color:var(--text-light)"><?= htmlspecialchars($supplier_name) ?></strong>.</p>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card green">
        <div class="text"><h3>Open Requests</h3><div class="number"><?= $open_prs ?></div></div>
        <i class="fas fa-folder-open"></i>
      </div>
      <div class="stat-card blue">
        <div class="text"><h3>Total Bids Submitted</h3><div class="number"><?= $total_bids ?></div></div>
        <i class="fas fa-gavel"></i>
      </div>
      <div class="stat-card yellow">
        <div class="text"><h3>Awaiting Decision</h3><div class="number"><?= $pending_bids ?></div></div>
        <i class="fas fa-clock"></i>
      </div>
      <div class="stat-card purple">
        <div class="text"><h3>Bids Won</h3><div class="number"><?= $won_bids ?></div></div>
        <i class="fas fa-trophy"></i>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

      <!-- Recent Bids -->
      <div class="s-card">
        <h2><i class="fas fa-gavel"></i> Recent Bids</h2>
        <?php if (empty($recent_bids)): ?>
          <p style="color:var(--gray);text-align:center;padding:24px;font-style:italic;">No bids submitted yet.</p>
        <?php else: ?>
          <table class="dark-table">
            <thead><tr><th>PR #</th><th>MPN</th><th>Unit Price</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($recent_bids as $b):
                $sym = CURRENCY_SYMBOLS[$b['currency']] ?? $b['currency'].' '; ?>
              <tr>
                <td><?= htmlspecialchars($b['pr_number']) ?></td>
                <td><?= htmlspecialchars($b['mpn'] ?? '—') ?></td>
                <td>
                  <?= $sym ?><?= number_format($b['unit_price'], 4) ?>
                  <?php if ($b['currency'] !== 'PHP'): ?>
                    <div class="php-equiv-block">≈ ₱<?= number_format(to_php((float)$b['unit_price'], $b['currency']), 4) ?></div>
                  <?php endif; ?>
                </td>
                <td><span class="bid-status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
        <div style="margin-top:16px"><a href="supplier_my_bids.php" class="s-btn sm blue"><i class="fas fa-list"></i> All Bids</a></div>
      </div>

      <!-- Recent POs -->
      <div class="s-card">
        <h2><i class="fas fa-file-invoice"></i> Recent Purchase Orders</h2>
        <?php if (empty($recent_pos)): ?>
          <p style="color:var(--gray);text-align:center;padding:24px;font-style:italic;">No purchase orders yet.</p>
        <?php else: ?>
          <table class="dark-table">
            <thead><tr><th>PO #</th><th>MPN</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($recent_pos as $po):
                $sym = CURRENCY_SYMBOLS[$po['currency']] ?? $po['currency'].' ';
                $badge = match($po['status']) { 'Received'=>'green','Cancelled'=>'red',default=>'blue' }; ?>
              <tr>
                <td><strong><?= htmlspecialchars($po['po_number']) ?></strong></td>
                <td><?= htmlspecialchars($po['mpn'] ?? '—') ?></td>
                <td>
                  <?= $sym ?><?= number_format($po['total_amount'], 2) ?>
                  <?php if ($po['currency'] !== 'PHP'): ?>
                    <div class="php-equiv-block">≈ ₱<?= number_format(to_php((float)$po['total_amount'], $po['currency']), 2) ?></div>
                  <?php endif; ?>
                </td>
                <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($po['status']) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
        <div style="margin-top:16px"><a href="supplier_my_pos.php" class="s-btn sm"><i class="fas fa-file-invoice"></i> All POs</a></div>
      </div>

    </div>

    <?php if ($open_prs > 0): ?>
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:16px;padding:22px 28px;
                display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;
                box-shadow:0 10px 30px rgba(0,0,0,.4);">
      <div>
        <div style="font-size:1rem;font-weight:700;color:var(--green);margin-bottom:4px;">
          <i class="fas fa-bell"></i> <?= $open_prs ?> open request<?= $open_prs > 1 ? 's' : '' ?> available for bidding
        </div>
        <div style="font-size:.85rem;color:#9a9ab5;">Submit competitive bids to win purchase orders.</div>
      </div>
      <a href="supplier_open_requests.php" class="s-btn"><i class="fas fa-folder-open"></i> View Open Requests</a>
    </div>
    <?php endif; ?>

  </main>
</div>
</body>
</html>
