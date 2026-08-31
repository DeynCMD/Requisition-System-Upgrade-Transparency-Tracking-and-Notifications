<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header('Location: ../../Admin/HTML/ZE-Electronics.php'); exit;
}
require_once '../../Admin/PHP/db.php';

// Fetch dept budgets
$depts = $conn->query("SELECT * FROM department_budgets ORDER BY id")->fetch_all(MYSQLI_ASSOC);
// Fetch main budget
$main  = $conn->query("SELECT * FROM finance_budget WHERE id=1")->fetch_assoc();
// All transactions (for table + CSV)
$txs   = $conn->query("SELECT * FROM budget_transactions ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
// MRO-only transactions
$mrotxs = $conn->query("
    SELECT * FROM budget_transactions
    WHERE department IS NOT NULL AND department != ''
    ORDER BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);
// JSON for JS
$deptsJson  = json_encode($depts);
$mrotxsJson = json_encode($mrotxs);
$reportDate = date('F d, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Budget Categories — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="../CSS/admin_style.css?v=<?= time() ?>">
  <style>
    .main-content{margin-left:260px;padding:36px 40px;}
    .page-title{color:var(--green);font-size:2rem;margin-bottom:24px;}
    .s-card{background:var(--card-bg);border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.4);margin-bottom:26px;}
    .s-card h2{font-size:1.1rem;color:var(--yellow);margin-bottom:20px;display:flex;align-items:center;gap:10px;}
    .fg{display:grid;gap:14px;}
    .fg.c3{grid-template-columns:repeat(3,1fr);}
    @media(max-width:900px){.fg.c3{grid-template-columns:1fr;}}
    .field label{display:block;font-size:.8rem;color:#9a9ab5;margin-bottom:5px;}
    .field input,.field select{width:100%;background:#1e1e2e;border:1px solid var(--border);color:var(--text-light);padding:11px 13px;border-radius:10px;font-size:.93rem;font-family:inherit;outline:none;transition:border-color .2s;}
    .field input:focus,.field select:focus{border-color:var(--green);}
    .s-btn{background:#22c55e;color:#fff;border:none;padding:11px 20px;border-radius:12px;cursor:pointer;font-size:.93rem;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;gap:8px;transition:.2s;}
    .s-btn:hover{background:#16a34a;}
    /* budget cards */
    .budget-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;}
    .bcard{background:#1e1e2e;border-radius:14px;padding:20px;border:1px solid var(--border);}
    .bcard .dept-name{font-size:1.1rem;font-weight:700;color:var(--text-light);margin-bottom:4px;}
    .bcard .dept-icon{font-size:1.8rem;margin-bottom:12px;}
    .bcard .dept-icon.m{color:#fbbf24;}.bcard .dept-icon.r{color:#f87171;}.bcard .dept-icon.o{color:#a78bfa;}
    .bcard .nums{display:flex;justify-content:space-between;font-size:.85rem;color:#9a9ab5;margin-top:10px;flex-wrap:wrap;gap:6px;}
    .bcard .nums strong{color:var(--text-light);}
    .bar-track{background:#333;border-radius:20px;height:10px;overflow:hidden;margin:10px 0;}
    .bar-fill{height:100%;border-radius:20px;transition:width .5s;}
    .bar-fill.m{background:#fbbf24;}.bar-fill.r{background:#f87171;}.bar-fill.o{background:#a78bfa;}
    /* charts */
    .charts-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:26px;}
    @media(max-width:800px){.charts-row{grid-template-columns:1fr;}}
    .chart-card{background:var(--card-bg);border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.4);}
    .chart-card h3{font-size:.95rem;color:var(--yellow);margin-bottom:16px;display:flex;align-items:center;gap:8px;}
    .chart-card canvas{max-height:280px;}
    /* export bar */
    .export-bar{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;}
    .export-bar span{color:#9a9ab5;font-size:.85rem;}
    .exp-btn{background:#1e1e2e;border:1px solid #444;color:#e0e0ff;padding:9px 18px;border-radius:10px;cursor:pointer;font-size:.88rem;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;gap:8px;transition:.2s;}
    .exp-btn:hover{border-color:#22c55e;color:#22c55e;}
    .exp-btn.pdf{border-color:#f87171;color:#f87171;}.exp-btn.pdf:hover{background:#f87171;color:#fff;}
    .exp-btn.csv{border-color:#34d399;color:#34d399;}.exp-btn.csv:hover{background:#34d399;color:#fff;}
    /* transactions table */
    .tw{background:#fff;border-radius:12px;overflow-x:auto;border:1px solid #e5e7eb;}
    .tw table{width:100%;border-collapse:collapse;}
    .tw thead{background:#f1f5f9;}
    .tw th{text-align:left;color:#64748b;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;padding:12px 14px;border-bottom:1px solid #e5e7eb;}
    .tw td{padding:12px 14px;border-bottom:1px solid #e5e7eb;color:#1f2937;font-size:.9rem;}
    .tw tbody tr:last-child td{border-bottom:none;}
    .tw tbody tr:hover{background:#f8fafc;}
    .badge{display:inline-flex;padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:600;}
    .badge.green,.badge.add,.badge.refund{background:#dcfce7;color:#15803d;}
    .badge.yellow,.badge.allocate{background:#fef9c3;color:#854d0e;}
    .badge.purple{background:#ede9fe;color:#6d28d9;}
    .badge.gray,.badge.adjust{background:#f1f5f9;color:#64748b;}
    .badge.spend,.badge.deduct{background:#fee2e2;color:#991b1b;}
    /* main budget banner */
    .main-budget{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:26px;}
    .mb-card{flex:1;min-width:180px;background:var(--card-bg);border-radius:14px;padding:18px;box-shadow:0 6px 20px rgba(0,0,0,.3);}
    .mb-card .label{font-size:.8rem;color:#9a9ab5;margin-bottom:6px;}
    .mb-card .val{font-size:1.5rem;font-weight:700;color:var(--text-light);}
    .mb-card .val.green{color:var(--green);}.mb-card .val.red{color:var(--red);}.mb-card .val.yellow{color:var(--yellow);}
    /* ── PRINT STYLES ─────────────────────────────── */
    @media print {
      body { background: #fff !important; font-family: "Segoe UI", sans-serif; }
      .sidebar, .export-bar, .s-btn, .s-card:has(#alloc-dept) { display: none !important; }
      .main-content { margin-left: 0 !important; padding: 0 !important; }
      .print-header { display: flex !important; }
      .charts-row { display: grid !important; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
      .chart-card { background: #fff !important; border: 1px solid #ddd; border-radius: 8px;
                     padding: 16px; box-shadow: none !important; page-break-inside: avoid; }
      .chart-card h3 { color: #333 !important; }
      .bcard { background: #f9fafb !important; border: 1px solid #ddd !important; page-break-inside: avoid; }
      .bcard .dept-name, .bcard .nums strong { color: #111 !important; }
      .bcard .nums { color: #555 !important; }
      .bar-track { background: #e5e7eb !important; }
      .s-card { background: #fff !important; box-shadow: none !important;
                 border: 1px solid #ddd; border-radius: 8px; margin-bottom: 16px; page-break-inside: avoid; }
      .s-card h2 { color: #1f2937 !important; border-bottom: 2px solid #22c55e; padding-bottom: 8px; }
      .main-budget { margin-bottom: 16px; }
      .mb-card { background: #f9fafb !important; border: 1px solid #ddd; box-shadow: none !important; }
      .mb-card .label { color: #555 !important; }
      .mb-card .val { color: #111 !important; }
      .tw { border: 1px solid #ddd; }
      .tw th { background: #f1f5f9 !important; color: #374151 !important; }
      .tw td { color: #111 !important; }
      .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      canvas { max-height: 220px !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .page-title { color: #111 !important; }
    }
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
        <li><a href="admin_returns.php"><i class="fas fa-rotate-left"></i> Item Returns</a></li>
      <li><a href="HistoryZE.php"><i class="fas fa-history"></i> History</a></li>
      </ul>
    </nav>
    <a href="../PHP/logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <!-- Print-only report header -->
    <div class="print-header" style="display:none; align-items:center; gap:16px; margin-bottom:24px; padding-bottom:16px; border-bottom:3px solid #22c55e;">
      <div>
        <div style="font-size:1.5rem;font-weight:800;color:#111;">Procurement System</div>
        <div style="font-size:.9rem;color:#555;">MRO Budget Report — Generated <?= $reportDate ?></div>
      </div>
    </div>

    <h1 class="page-title">Budget Categories</h1>

    <!-- Main budget summary -->
    <div class="main-budget">
      <div class="mb-card">
        <div class="label">Total Budget</div>
        <div class="val">₱<?= number_format($main['total_budget'] ?? 0, 2) ?></div>
      </div>
      <div class="mb-card">
        <div class="label">Allocated to Depts</div>
        <div class="val yellow">₱<?= number_format($main['allocated_budget'] ?? 0, 2) ?></div>
      </div>
      <div class="mb-card">
        <div class="label">Spent</div>
        <div class="val red">₱<?= number_format($main['spent_budget'] ?? 0, 2) ?></div>
      </div>
      <div class="mb-card">
        <div class="label">Remaining (Unallocated)</div>
        <div class="val green">₱<?= number_format($main['remaining_budget'] ?? 0, 2) ?></div>
      </div>
    </div>

    <!-- Export buttons -->
    <div class="export-bar">
      <span><i class="fas fa-file-export"></i> Export:</span>
      <button class="exp-btn pdf" onclick="printMROReport()">
        <i class="fas fa-file-pdf"></i> Print / Save PDF
      </button>
      <button class="exp-btn csv" onclick="exportMROCSV()">
        <i class="fas fa-file-csv"></i> Download CSV
      </button>
    </div>

    <!-- Charts row -->
    <div class="charts-row">
      <div class="chart-card">
        <h3><i class="fas fa-chart-bar"></i> Allocated vs Spent per Category</h3>
        <canvas id="barChart"></canvas>
      </div>
      <div class="chart-card">
        <h3><i class="fas fa-chart-pie"></i> Budget Utilization</h3>
        <canvas id="doughnutChart"></canvas>
      </div>
    </div>

    <!-- Dept budget cards -->
    <div class="s-card">
      <h2><i class="fas fa-chart-pie"></i> MRO Budget Breakdown</h2>
      <div class="budget-grid" id="dept-cards">
        <?php
        $icons = ['Maintenance'=>['m','fa-wrench'],'Repair'=>['r','fa-screwdriver-wrench'],'Operations'=>['o','fa-industry']];
        foreach($depts as $d):
          $key = $d['department_name'];
          [$cls,$ico] = $icons[$key] ?? ['gray','fa-circle'];
          $pct = $d['allocated_amount'] > 0 ? round(($d['spent_amount']/$d['allocated_amount'])*100,1) : 0;
        ?>
        <div class="bcard">
          <div class="dept-icon <?= $cls ?>"><i class="fas <?= $ico ?>"></i></div>
          <div class="dept-name"><?= htmlspecialchars($key) ?></div>
          <div class="bar-track"><div class="bar-fill <?= $cls ?>" style="width:<?= min($pct,100) ?>%"></div></div>
          <div class="nums">
            <span>Allocated: <strong>₱<?= number_format($d['allocated_amount'],2) ?></strong></span>
            <span>Spent: <strong>₱<?= number_format($d['spent_amount'],2) ?></strong></span>
            <span>Left: <strong>₱<?= number_format($d['remaining_amount'],2) ?></strong></span>
          </div>
          <div style="font-size:.78rem;color:#9a9ab5;margin-top:8px;"><?= $pct ?>% utilized</div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Allocate budget form -->
    <div class="s-card">
      <h2><i class="fas fa-hand-holding-dollar"></i> Allocate Budget to Department</h2>
      <div class="fg c3">
        <div class="field"><label>Department *</label>
          <select id="alloc-dept">
            <option value="">Select department</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Repair">Repair</option>
            <option value="Operations">Operations</option>
          </select>
        </div>
        <div class="field"><label>Amount (₱) *</label>
          <input id="alloc-amount" type="number" min="1" step="0.01" placeholder="e.g. 50000"/>
        </div>
        <div class="field"><label>Description</label>
          <input id="alloc-desc" placeholder="e.g. Q2 Maintenance Budget"/>
        </div>
      </div>
      <div style="margin-top:14px">
        <button class="s-btn" onclick="allocateBudget()"><i class="fas fa-paper-plane"></i> Allocate</button>
      </div>
    </div>

    <!-- MRO Transaction History -->
    <div class="s-card">
      <h2><i class="fas fa-clock-rotate-left"></i> MRO Transaction History</h2>
      <div class="tw">
        <table id="mro-tx-table">
          <thead><tr><th>Type</th><th>Amount</th><th>Department</th><th>Description</th><th>Date</th></tr></thead>
          <tbody>
            <?php if(empty($mrotxs)): ?>
            <tr><td colspan="5" style="text-align:center;color:#aaa;padding:20px">No MRO transactions yet</td></tr>
            <?php else: foreach($mrotxs as $tx):
              $badgeCls = match($tx['transaction_type']){
                'add'=>'add','allocate'=>'allocate','spend'=>'spend','refund'=>'refund',default=>'gray'
              };
            ?>
            <tr>
              <td><span class="badge <?= $badgeCls ?>"><?= ucfirst(htmlspecialchars($tx['transaction_type'])) ?></span></td>
              <td>₱<?= number_format($tx['amount'],2) ?></td>
              <td><?= htmlspecialchars($tx['department'] ?: '—') ?></td>
              <td><?= htmlspecialchars($tx['description'] ?: '—') ?></td>
              <td><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- All Transactions History (kept for reference) -->
    <div class="s-card">
      <h2><i class="fas fa-list"></i> All Recent Transactions</h2>
      <div class="tw">
        <table>
          <thead><tr><th>Type</th><th>Amount</th><th>Department</th><th>Description</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach(array_slice($txs,0,20) as $tx):
              $badgeCls = match($tx['transaction_type']){
                'add'=>'add','allocate'=>'allocate','spend'=>'spend','refund'=>'refund',default=>'gray'
              };
            ?>
            <tr>
              <td><span class="badge <?= $badgeCls ?>"><?= ucfirst(htmlspecialchars($tx['transaction_type'])) ?></span></td>
              <td>₱<?= number_format($tx['amount'],2) ?></td>
              <td><?= htmlspecialchars($tx['department'] ?: '—') ?></td>
              <td><?= htmlspecialchars($tx['description'] ?: '—') ?></td>
              <td><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($txs)):?><tr><td colspan="5" style="text-align:center;color:#aaa;padding:20px">No transactions yet</td></tr><?php endif;?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<script>
const depts   = <?= $deptsJson ?>;
const mrotxs  = <?= $mrotxsJson ?>;

const labels   = depts.map(d => d.department_name);
const colors   = { Maintenance:'#fbbf24', Repair:'#f87171', Operations:'#a78bfa' };
const bgColors = labels.map(l => colors[l] || '#9a9ab5');

// Bar chart
window._barChart = new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: 'Allocated',
        data: depts.map(d => parseFloat(d.allocated_amount)),
        backgroundColor: bgColors.map(c => c + '55'),
        borderColor: bgColors,
        borderWidth: 2,
        borderRadius: 6,
      },
      {
        label: 'Spent',
        data: depts.map(d => parseFloat(d.spent_amount)),
        backgroundColor: bgColors,
        borderColor: bgColors,
        borderWidth: 2,
        borderRadius: 6,
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { labels: { color: '#e0e0ff', font: { size: 12 } } },
      tooltip: { callbacks: { label: ctx => ` ₱${ctx.parsed.y.toLocaleString('en-PH',{minimumFractionDigits:2})}` } }
    },
    scales: {
      x: { ticks: { color: '#b8b8d4' }, grid: { color: '#2a2a3a' } },
      y: { ticks: { color: '#b8b8d4', callback: v => '₱' + v.toLocaleString() }, grid: { color: '#2a2a3a' } }
    }
  }
});

// Doughnut chart
window._doughnutChart = new Chart(document.getElementById('doughnutChart'), {
  type: 'doughnut',
  data: {
    labels: labels.map(l => {
      const d = depts.find(x => x.department_name === l);
      const pct = d.allocated_amount > 0 ? ((d.spent_amount / d.allocated_amount) * 100).toFixed(1) : 0;
      return `${l} (${pct}%)`;
    }),
    datasets: [{
      data: depts.map(d => parseFloat(d.spent_amount) || 0.001),
      backgroundColor: bgColors,
      borderColor: '#12121a',
      borderWidth: 3,
      hoverOffset: 8
    }]
  },
  options: {
    responsive: true,
    cutout: '65%',
    plugins: {
      legend: { position: 'bottom', labels: { color: '#e0e0ff', padding: 16, font: { size: 12 } } },
      tooltip: { callbacks: { label: ctx => ` ₱${ctx.parsed.toLocaleString('en-PH',{minimumFractionDigits:2})} spent` } }
    }
  }
});

// Print / PDF
function printMROReport() {
  setTimeout(() => window.print(), 200);
}

// CSV Export
function exportMROCSV() {
  if (!mrotxs || mrotxs.length === 0) { Swal.fire({ icon:'info', title:'No Data', text:'No MRO transactions to export.', confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff' }); return; }
  const headers = ['Type','Amount (PHP)','Department','Description','Date'];
  const rows = mrotxs.map(tx => [
    tx.transaction_type,
    parseFloat(tx.amount).toFixed(2),
    tx.department || '',
    (tx.description || '').replace(/,/g, ';'),
    tx.created_at
  ]);
  const csv  = [headers, ...rows].map(r => r.map(v => `"${v}"`).join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = url;
  a.download = `MRO_Transactions_<?= date('Y-m-d') ?>.csv`;
  a.click();
  URL.revokeObjectURL(url);
}

// Allocate budget
async function allocateBudget(){
  const dept   = document.getElementById('alloc-dept').value;
  const amount = parseFloat(document.getElementById('alloc-amount').value);
  const desc   = document.getElementById('alloc-desc').value.trim();
  if(!dept || !amount || amount <= 0){
    Swal.fire({icon:'warning',title:'Missing fields',text:'Select a department and enter a valid amount.'}); return;
  }
  const r = await Swal.fire({title:`Allocate ₱${amount.toLocaleString()} to ${dept}?`,icon:'question',showCancelButton:true,confirmButtonColor:'#22c55e',confirmButtonText:'Allocate'});
  if(!r.isConfirmed) return;
  const res = await fetch('../PHP/dept_budget.php?action=allocate',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({department:dept,amount,description:desc})});
  const d = await res.json();
  if(d.success){
    Swal.fire({icon:'success',title:'Done',text:d.message,confirmButtonColor:'#22c55e'});
    document.getElementById('alloc-dept').value = '';
    document.getElementById('alloc-amount').value = '';
    document.getElementById('alloc-desc').value = '';
    refreshBudget();
  } else {
    Swal.fire({icon:'error',title:'Error',text:d.message});
  }
}

// ── Realtime budget refresh ───────────────────────
const SNAP = '../PHP/get_budget_snapshot.php';

function fmt(n){ return '₱' + Number(n).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }

async function refreshBudget(){
  const res = await fetch(SNAP);
  const data = await res.json();
  if(!data.success) return;

  const m = data.main;

  // ── Main budget banner cards ──
  const mbVals = document.querySelectorAll('.mb-card .val');
  if(mbVals[0]) mbVals[0].textContent = fmt(m.total_budget);
  if(mbVals[1]) mbVals[1].textContent = fmt(m.allocated_budget);
  if(mbVals[2]) mbVals[2].textContent = fmt(m.spent_budget);
  if(mbVals[3]) mbVals[3].textContent = fmt(m.remaining_budget);

  // ── MRO dept cards ──
  data.depts.forEach(d => {
    const pct = d.allocated_amount > 0 ? Math.round((d.spent_amount/d.allocated_amount)*100*10)/10 : 0;
    document.querySelectorAll('.bcard').forEach(card => {
      if(card.querySelector('.dept-name')?.textContent.trim() !== d.department_name) return;
      const bar = card.querySelector('.bar-fill');
      if(bar) bar.style.width = Math.min(pct,100) + '%';
      const strongs = card.querySelectorAll('.nums strong');
      if(strongs[0]) strongs[0].textContent = fmt(d.allocated_amount);
      if(strongs[1]) strongs[1].textContent = fmt(d.spent_amount);
      if(strongs[2]) strongs[2].textContent = fmt(d.remaining_amount);
      const small = card.querySelector('[style*="font-size:.78rem"]');
      if(small) small.textContent = pct + '% utilized';
    });
  });

  // ── MRO Transaction History table ──
  const mroTbody = document.querySelector('#mro-tx-table tbody');
  if(mroTbody){
    if(!data.mrotxs.length){
      mroTbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#aaa;padding:20px">No MRO transactions yet</td></tr>';
    } else {
      const badgeCls = {add:'add',allocate:'allocate',spend:'spend',refund:'refund'};
      mroTbody.innerHTML = data.mrotxs.map(tx => {
        const cls = badgeCls[tx.transaction_type] || 'gray';
        const label = tx.transaction_type.charAt(0).toUpperCase() + tx.transaction_type.slice(1);
        const date = new Date(tx.created_at).toLocaleString('en-PH',{year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'});
        return `<tr>
          <td><span class="badge ${cls}">${label}</span></td>
          <td>${fmt(tx.amount)}</td>
          <td>${tx.department || '—'}</td>
          <td>${tx.description || '—'}</td>
          <td>${date}</td>
        </tr>`;
      }).join('');
    }
  }

  // ── All transactions table ──
  const allTbody = document.querySelector('.s-card:last-of-type .tw tbody');
  if(allTbody && data.txs.length){
    const badgeCls = {add:'add',allocate:'allocate',spend:'spend',refund:'refund'};
    allTbody.innerHTML = data.txs.slice(0,20).map(tx => {
      const cls = badgeCls[tx.transaction_type] || 'gray';
      const label = tx.transaction_type.charAt(0).toUpperCase() + tx.transaction_type.slice(1);
      const date = new Date(tx.created_at).toLocaleString('en-PH',{year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'});
      return `<tr>
        <td><span class="badge ${cls}">${label}</span></td>
        <td>${fmt(tx.amount)}</td>
        <td>${tx.department || '—'}</td>
        <td>${tx.description || '—'}</td>
        <td>${date}</td>
      </tr>`;
    }).join('');
  }

  // ── Update charts ──
  if(window._barChart && window._doughnutChart){
    window._barChart.data.datasets[0].data = data.depts.map(d=>parseFloat(d.allocated_amount));
    window._barChart.data.datasets[1].data = data.depts.map(d=>parseFloat(d.spent_amount));
    window._barChart.update('active');

    window._doughnutChart.data.datasets[0].data = data.depts.map(d=>parseFloat(d.spent_amount)||0.001);
    window._doughnutChart.data.labels = data.depts.map(d=>{
      const pct = d.allocated_amount>0 ? ((d.spent_amount/d.allocated_amount)*100).toFixed(1) : 0;
      return `${d.department_name} (${pct}%)`;
    });
    window._doughnutChart.update('active');
  }
}

// Poll every 15 seconds
setInterval(refreshBudget, 15000);
</script>
</body>
</html>
