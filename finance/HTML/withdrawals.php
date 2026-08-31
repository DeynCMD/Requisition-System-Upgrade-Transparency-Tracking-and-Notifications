<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'FINANCE') {
    header('Location: ../../Admin/HTML/ZE-Electronics.php'); exit;
}
require_once '../../Admin/PHP/db.php';

$withdrawals = $conn->query("
    SELECT w.*, pr.mpn, pr.category, pr.subcategory
    FROM pr_withdrawals w
    JOIN purchase_requests pr ON pr.id = w.pr_id
    ORDER BY w.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Summary counts
$counts = ['pending'=>0,'approved'=>0,'rejected'=>0];
foreach($withdrawals as $w) $counts[$w['status']] = ($counts[$w['status']] ?? 0) + 1;

// MRO budget snapshot for sidebar context
$depts = $conn->query("SELECT * FROM department_budgets ORDER BY id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Withdrawal Requests — Finance</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../CSS/theme.css"/>
  <style>
    /* page-specific only — sidebar handled by theme.css */
    body { background: #12121a; }
    .main-content { flex: 1; padding: 36px 40px; margin-left: 260px; }
    .page-title   { color: var(--green); font-size: 2rem; margin-bottom: 24px; }

    .s-card { background: var(--card-bg); border-radius: 16px; padding: 24px;
               box-shadow: 0 10px 30px rgba(0,0,0,.4); margin-bottom: 26px; }
    .s-card h2 { font-size: 1.1rem; color: #fbbf24; margin-bottom: 20px;
                  display: flex; align-items: center; gap: 10px; }

    .tw { background: #fff; border-radius: 12px; overflow-x: auto; border: 1px solid #e5e7eb; }
    .tw table { width: 100%; border-collapse: collapse; }
    .tw thead  { background: #f1f5f9; }
    .tw th { text-align: left; color: #64748b; font-weight: 600; font-size: .72rem;
              text-transform: uppercase; letter-spacing: .4px;
              padding: 13px 14px; border-bottom: 1px solid #e5e7eb; }
    .tw td { padding: 13px 14px; border-bottom: 1px solid #e5e7eb; color: #1f2937; font-size: .9rem; }
    .tw tbody tr:last-child td { border-bottom: none; }
    .tw tbody tr:hover { background: #f8fafc; }
    .empty { text-align: center; color: #94a3b8; padding: 30px; font-style: italic; }

    .badge { display: inline-flex; padding: 4px 11px; border-radius: 20px; font-size: .74rem; font-weight: 600; }
    .badge.pending  { background: #fef3c7; color: #92400e; }
    .badge.approved { background: #dcfce7; color: #15803d; }
    .badge.rejected { background: #fee2e2; color: #991b1b; }

    .action-btns { display: flex; gap: 8px; }
    .abtn { padding: 8px 14px; border: none; border-radius: 8px; cursor: pointer;
             font-weight: 600; font-size: .84rem; display: inline-flex;
             align-items: center; gap: 6px; transition: .2s; }
    .abtn.approve       { background: #22543d; color: #fff; }
    .abtn.approve:hover { background: #276749; }
    .abtn.reject        { background: #742a2a; color: #fff; }
    .abtn.reject:hover  { background: #9b2c2c; }

    /* summary count pills */
    .summary-pills { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
    .sp { flex: 1; min-width: 130px; background: var(--card-bg); border-radius: 14px;
           padding: 18px; border: 1px solid var(--border); }
    .sp .label { font-size: .78rem; color: #9a9ab5; margin-bottom: 6px; }
    .sp .val   { font-size: 2rem; font-weight: 700; }
    .sp .val.yellow { color: #fbbf24; }
    .sp .val.green  { color: var(--green); }
    .sp .val.red    { color: var(--danger); }
  </style>
</head>
<body>
<div class="container">
  <aside class="sidebar">
    <div class="profile">
      <img src="../Assets/Avatar.jpg" alt="Finance"/>
      <span class="role">FINANCE</span>
    </div>
    <nav class="nav-menu">
      <ul>
        <li><a href="finance-dashboard.php"><i class="fas fa-dollar-sign"></i> Finance</a></li>
        <li><a href="budget-approvals.html"><i class="fas fa-check-double"></i> Budget Approvals</a></li>
        <li><a href="finance-budget.php"><i class="fas fa-wallet"></i> Budget</a></li>
        <li><a href="withdrawals.php" class="active"><i class="fas fa-rotate-left"></i> Withdrawals</a></li>
        <li><a href="history.html"><i class="fas fa-history"></i> History</a></li>
      </ul>
    </nav>
    <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Withdrawal Requests</h1>
    <p style="color:#9a9ab5;margin-bottom:24px;font-size:.95rem;">
      When a requested item is reported as unavailable or cancelled, Finance reviews and releases the reserved budget back.
    </p>

    <!-- Summary counts -->
    <div class="summary-pills">
      <div class="sp"><div class="label">Pending</div><div class="val yellow"><?= $counts['pending'] ?></div></div>
      <div class="sp"><div class="label">Approved</div><div class="val green"><?= $counts['approved'] ?></div></div>
      <div class="sp"><div class="label">Rejected</div><div class="val red"><?= $counts['rejected'] ?></div></div>
    </div>

    <!-- MRO Budget snapshot — shows impact of refunds by category -->
    <div class="s-card" style="margin-bottom:26px;">
      <h2><i class="fas fa-chart-pie"></i> MRO Budget Snapshot <span style="font-size:.8rem;color:#9a9ab5;font-weight:400;margin-left:8px;">— approving a withdrawal refunds the matching category</span></h2>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
        <?php
        $mroIcons = ['Maintenance'=>['#fbbf24','fa-wrench'],'Repair'=>['#f87171','fa-screwdriver-wrench'],'Operations'=>['#a78bfa','fa-industry']];
        foreach($depts as $d):
          [$col,$ico] = $mroIcons[$d['department_name']] ?? ['#9a9ab5','fa-circle'];
          $pct = $d['allocated_amount'] > 0 ? round(($d['spent_amount']/$d['allocated_amount'])*100,1) : 0;
        ?>
        <div style="background:#1a1a2e;border-radius:12px;padding:16px;border:1px solid #444;">
          <div style="font-size:1.4rem;color:<?= $col ?>;margin-bottom:8px;"><i class="fas <?= $ico ?>"></i></div>
          <div style="font-weight:700;color:#e0e0ff;margin-bottom:8px;"><?= htmlspecialchars($d['department_name']) ?></div>
          <div style="background:#333;border-radius:20px;height:8px;overflow:hidden;margin-bottom:8px;">
            <div style="height:100%;border-radius:20px;background:<?= $col ?>;width:<?= min($pct,100) ?>%;transition:width .5s;"></div>
          </div>
          <div style="font-size:.82rem;color:#9a9ab5;">
            Remaining: <strong style="color:#e0e0ff;">₱<?= number_format($d['remaining_amount'],2) ?></strong><br>
            Spent: <strong style="color:#e0e0ff;">₱<?= number_format($d['spent_amount'],2) ?></strong>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="s-card">
      <h2><i class="fas fa-rotate-left"></i> All Withdrawal Requests</h2>
      <p style="color:#9a9ab5;font-size:.85rem;margin-bottom:16px;">Approving a request releases the reserved budget back to the MRO category and total budget.</p>
      <div class="tw">
        <table>
          <thead>
            <tr>
              <th>PR #</th><th>Requestor</th><th>MPN</th><th>Category</th>
              <th>Amount</th><th>Reason</th><th>Submitted</th><th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($withdrawals)):?>
            <tr><td colspan="9" class="empty">No withdrawal requests yet.</td></tr>
            <?php else: foreach($withdrawals as $w): ?>
            <tr id="row-<?= $w['id'] ?>">
              <td><strong><?= htmlspecialchars($w['pr_number']) ?></strong></td>
              <td><?= htmlspecialchars($w['requested_by_name']) ?></td>
              <td><?= htmlspecialchars($w['mpn'] ?? '—') ?></td>
              <td><?= htmlspecialchars($w['category'] ?? '—') ?><?= $w['subcategory'] ? ' / '.htmlspecialchars($w['subcategory']) : '' ?></td>
              <td><?= htmlspecialchars($w['currency']) ?> <?= number_format($w['amount'],2) ?></td>
              <td style="max-width:200px"><?= htmlspecialchars(substr($w['reason'],0,80)) ?><?= strlen($w['reason'])>80?'…':'' ?></td>
              <td><?= date('M d, Y', strtotime($w['created_at'])) ?></td>
              <td><span class="badge <?= $w['status'] ?>"><?= ucfirst($w['status']) ?></span></td>
              <td>
                <?php if($w['status']==='pending'): ?>
                <div class="action-btns">
                  <button class="abtn approve" onclick="reviewWithdrawal(<?= $w['id'] ?>,'approve')"><i class="fas fa-check"></i> Approve</button>
                  <button class="abtn reject" onclick="reviewWithdrawal(<?= $w['id'] ?>,'reject')"><i class="fas fa-times"></i> Reject</button>
                </div>
                <?php else: ?>
                <span style="color:#9a9ab5;font-size:.84rem">
                  <?= ucfirst($w['status']) ?> by <?= htmlspecialchars($w['reviewed_by_name']??'—') ?>
                  <?php if($w['status']==='rejected' && $w['rejection_reason']): ?>
                  <br><small style="color:#f87171"><?= htmlspecialchars($w['rejection_reason']) ?></small>
                  <?php endif; ?>
                </span>
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
async function refreshMROSnapshot(){
  const res = await fetch('../../Admin/PHP/get_budget_snapshot.php');
  const data = await res.json();
  if(!data.success) return;
  const mroColors = {Maintenance:'#fbbf24',Repair:'#f87171',Operations:'#a78bfa'};
  data.depts.forEach(d => {
    const pct = d.allocated_amount > 0 ? Math.round((d.spent_amount/d.allocated_amount)*100*10)/10 : 0;
    // find the snapshot card by department name text
    document.querySelectorAll('[style*="background:#1a1a2e"]').forEach(card => {
      const nameEl = card.querySelector('[style*="font-weight:700"]');
      if(!nameEl || nameEl.textContent.trim() !== d.department_name) return;
      const bar = card.querySelector('[style*="border-radius:20px;height:8px"]');
      if(bar) bar.style.width = Math.min(pct,100) + '%';
      const strongs = card.querySelectorAll('strong');
      strongs.forEach(s => {
        if(s.previousSibling?.textContent?.includes('Remaining'))
          s.textContent = '₱' + Number(d.remaining_amount).toLocaleString('en-PH',{minimumFractionDigits:2});
        if(s.previousSibling?.textContent?.includes('Spent'))
          s.textContent = '₱' + Number(d.spent_amount).toLocaleString('en-PH',{minimumFractionDigits:2});
      });
    });
  });
}

async function reviewWithdrawal(id, action){
  let body = {id, action};
  if(action === 'reject'){
    const {value: reason, isConfirmed} = await Swal.fire({
      title:'Rejection reason',
      input:'textarea',inputPlaceholder:'Explain why this withdrawal is rejected (min 5 chars)',
      showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Reject',
      preConfirm: v=>{ if(!v||v.trim().length<5){Swal.showValidationMessage('Min 5 characters');} return v?.trim(); }
    });
    if(!isConfirmed||!reason) return;
    body.rejection_reason = reason;
  } else {
    const r = await Swal.fire({title:'Approve withdrawal?',text:'Budget will be refunded to remaining balance.',icon:'question',showCancelButton:true,confirmButtonColor:'#22c55e',confirmButtonText:'Approve'});
    if(!r.isConfirmed) return;
  }
  const res = await fetch('../PHP/review_withdrawal.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
  const d = await res.json();
  if(d.success){
    Swal.fire({icon:'success',title:'Done',text:d.message,confirmButtonColor:'#22c55e'}).then(()=>{
      // Update the row inline instead of full reload
      const row = document.getElementById('row-'+id);
      if(row){
        const statusCell = row.querySelector('.badge');
        const actionsCell = row.querySelector('.action-btns') || row.querySelector('td:last-child div');
        if(statusCell){
          statusCell.className = `badge ${action}`;
          statusCell.textContent = action.charAt(0).toUpperCase()+action.slice(1);
        }
        if(actionsCell){
          actionsCell.outerHTML = `<span style="color:#9a9ab5;font-size:.84rem">${action.charAt(0).toUpperCase()+action.slice(1)}</span>`;
        }
        // Update MRO snapshot bars if visible
        setTimeout(refreshMROSnapshot, 600);
      } else {
        location.reload();
      }
    });
  } else {
    Swal.fire({icon:'error',title:'Error',text:d.message});
  }
}
</script>
</body>
</html>
