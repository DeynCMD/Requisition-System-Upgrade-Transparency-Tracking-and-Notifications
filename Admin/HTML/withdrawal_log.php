<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header('Location: ZE-Electronics.php'); exit;
}
require_once '../../Admin/PHP/db.php';

$withdrawals = $conn->query("
    SELECT w.*, pr.mpn, pr.category, pr.subcategory, pr.total_amount as pr_amount
    FROM pr_withdrawals w
    JOIN purchase_requests pr ON pr.id = w.pr_id
    ORDER BY w.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$counts = ['pending'=>0,'approved'=>0,'rejected'=>0];
foreach($withdrawals as $w) $counts[$w['status']] = ($counts[$w['status']] ?? 0) + 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Withdrawal Log — Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="../CSS/admin_style.css?v=<?= time() ?>"/>
  <style>
    body { background: #12121a; }
    .main-content{margin-left:260px;padding:36px 40px;}
    .page-title{color:var(--green);font-size:2rem;margin-bottom:6px;}
    .page-sub{color:#9a9ab5;margin-bottom:24px;font-size:.95rem;}

    .summary{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:26px;}
    .sc{flex:1;min-width:140px;background:var(--card-bg);border-radius:14px;padding:18px;border:1px solid var(--border);}
    .sc .label{font-size:.78rem;color:#9a9ab5;margin-bottom:6px;}
    .sc .val{font-size:2rem;font-weight:700;}
    .sc .val.yellow{color:var(--yellow);}
    .sc .val.green{color:var(--green);}
    .sc .val.red{color:var(--red);}

    .s-card{background:var(--card-bg);border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.4);}
    .s-card h2{font-size:1.1rem;color:var(--yellow);margin-bottom:20px;display:flex;align-items:center;gap:10px;}

    .tw{background:#fff;border-radius:12px;overflow-x:auto;border:1px solid #e5e7eb;}
    .tw table{width:100%;border-collapse:collapse;}
    .tw thead{background:#f1f5f9;}
    .tw th{text-align:left;color:#64748b;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;padding:13px 14px;border-bottom:1px solid #e5e7eb;}
    .tw td{padding:13px 14px;border-bottom:1px solid #e5e7eb;color:#1f2937;font-size:.9rem;vertical-align:top;}
    .tw tbody tr:last-child td{border-bottom:none;}
    .tw tbody tr:hover{background:#f8fafc;}
    .empty{text-align:center;color:#94a3b8;padding:30px;font-style:italic;}

    .badge{display:inline-flex;padding:4px 11px;border-radius:20px;font-size:.74rem;font-weight:600;}
    .badge.pending{background:#fef3c7;color:#92400e;}
    .badge.approved{background:#dcfce7;color:#15803d;}
    .badge.rejected{background:#fee2e2;color:#991b1b;}
    .badge.pre_po{background:#ede9fe;color:#6d28d9;}
    .badge.post_purchase{background:#dbeafe;color:#1d4ed8;}

    .filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;}
    .filter-bar select,.filter-bar input{
      background:#1a1a2e;border:1px solid var(--border);color:var(--text-light);
      padding:9px 13px;border-radius:10px;font-size:.9rem;font-family:inherit;outline:none;
    }
    .filter-bar select:focus,.filter-bar input:focus{border-color:#22c55e;}
  </style>
</head>
<body>
<div class="container">
  <aside class="sidebar">
    <div class="profile">
      <img src="../Assets/Avatar.jpg" alt="Admin"/>
      <span class="role">ADMIN</span>
    </div>
    <nav class="nav-menu">
      <ul>
        <li><a href="AdminZE.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="Admin-users.php"><i class="fas fa-users"></i> User Management</a></li>
        <li><a href="Pending-approvals.php"><i class="fas fa-clock"></i> Pending Approvals</a></li>
        <li><a href="suppliers.php"><i class="fas fa-truck-field"></i> Suppliers</a></li>
        <li><a href="admin_price_prediction.php"><i class="fas fa-chart-line"></i> Price Prediction</a></li>
        <li><a href="HistoryZE.php"><i class="fas fa-history"></i> History</a></li>
      </ul>
    </nav>
    <a href="../PHP/logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Withdrawal Log</h1>

    <!-- Summary counts -->
    <div class="summary">
      <div class="sc"><div class="label">Pending</div><div class="val yellow"><?= $counts['pending'] ?></div></div>
      <div class="sc"><div class="label">Approved</div><div class="val green"><?= $counts['approved'] ?></div></div>
      <div class="sc"><div class="label">Rejected</div><div class="val red"><?= $counts['rejected'] ?></div></div>
      <div class="sc"><div class="label">Total</div><div class="val"><?= count($withdrawals) ?></div></div>
    </div>

    <div class="s-card">
      <h2><i class="fas fa-rotate-left"></i> All Withdrawal Requests</h2>

      <!-- Filters -->
      <div class="filter-bar">
        <select id="fStatus" onchange="applyFilters()">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <select id="fType" onchange="applyFilters()">
          <option value="">All Types</option>
          <option value="pre_po">Pre-PO Cancellation</option>
          <option value="post_purchase">Post-Purchase Refund</option>
        </select>
        <input id="fSearch" type="text" placeholder="Search PR #, requestor, MPN…" oninput="applyFilters()"/>
      </div>

      <div class="tw">
        <table>
          <thead>
            <tr>
              <th>PR #</th>
              <th>Type</th>
              <th>Requestor</th>
              <th>MPN</th>
              <th>Category</th>
              <th>Amount</th>
              <th>Reason</th>
              <th>Submitted</th>
              <th>Status</th>
              <th>Reviewed By</th>
            </tr>
          </thead>
          <tbody id="wd-rows">
            <?php if(empty($withdrawals)): ?>
            <tr><td colspan="10" class="empty">No withdrawal requests yet.</td></tr>
            <?php else: foreach($withdrawals as $w):
              $wtype = $w['withdrawal_type'] ?? 'post_purchase';
            ?>
            <tr data-status="<?= $w['status'] ?>" data-type="<?= $wtype ?>"
                data-search="<?= strtolower(htmlspecialchars($w['pr_number'].'|'.$w['requested_by_name'].'|'.($w['mpn']??''))) ?>">
              <td><strong><?= htmlspecialchars($w['pr_number']) ?></strong></td>
              <td><span class="badge <?= $wtype ?>"><?= $wtype === 'pre_po' ? 'Pre-PO Cancel' : 'Post-Purchase' ?></span></td>
              <td><?= htmlspecialchars($w['requested_by_name']) ?></td>
              <td><?= htmlspecialchars($w['mpn'] ?? '—') ?></td>
              <td><?= htmlspecialchars($w['category'] ?? '—') ?><?= $w['subcategory'] ? ' / '.htmlspecialchars($w['subcategory']) : '' ?></td>
              <td><?= htmlspecialchars($w['currency']) ?> <?= number_format($w['amount'],2) ?></td>
              <td style="max-width:200px;word-break:break-word;"><?= htmlspecialchars(substr($w['reason'],0,80)) ?><?= strlen($w['reason'])>80?'…':'' ?></td>
              <td><?= date('M d, Y', strtotime($w['created_at'])) ?></td>
              <td><span class="badge <?= $w['status'] ?>"><?= ucfirst($w['status']) ?></span></td>
              <td>
                <?php if($w['reviewed_by_name']): ?>
                  <?= htmlspecialchars($w['reviewed_by_name']) ?>
                  <?php if($w['reviewed_at']): ?>
                    <br><small style="color:#9a9ab5"><?= date('M d, Y', strtotime($w['reviewed_at'])) ?></small>
                  <?php endif; ?>
                  <?php if($w['status']==='rejected' && $w['rejection_reason']): ?>
                    <br><small style="color:#f87171"><?= htmlspecialchars($w['rejection_reason']) ?></small>
                  <?php endif; ?>
                <?php else: ?>
                  <span style="color:#9a9ab5">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<script>
function applyFilters(){
  const fStatus = document.getElementById('fStatus').value;
  const fType   = document.getElementById('fType').value;
  const fSearch = document.getElementById('fSearch').value.toLowerCase().trim();
  document.querySelectorAll('#wd-rows tr[data-status]').forEach(row => {
    const matchStatus = !fStatus || row.dataset.status === fStatus;
    const matchType   = !fType   || row.dataset.type   === fType;
    const matchSearch = !fSearch || row.dataset.search.includes(fSearch);
    row.style.display = (matchStatus && matchType && matchSearch) ? '' : 'none';
  });
}
</script>
</body>
</html>
