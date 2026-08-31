<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'FINANCE') {
    header('Location: ../../Admin/HTML/ZE-Electronics.php'); exit;
}
require_once '../../Admin/PHP/db.php';

// Main budget
$main  = $conn->query("SELECT * FROM finance_budget WHERE id=1")->fetch_assoc();
// MRO dept budgets
$depts = $conn->query("SELECT * FROM department_budgets ORDER BY id")->fetch_all(MYSQLI_ASSOC);
// Recent transactions (all)
$txs   = $conn->query("SELECT * FROM budget_transactions ORDER BY created_at DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
// MRO-only transactions (all, for CSV + report)
$mrotxs = $conn->query("
    SELECT * FROM budget_transactions
    WHERE department IS NOT NULL AND department != ''
    ORDER BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);
// JSON for JS charts
$deptsJson  = json_encode($depts);
$mrotxsJson = json_encode($mrotxs);
$reportDate = date('F d, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Budget Management — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="../CSS/theme.css"/>
  <style>
    body { background: #12121a; font-family: "Segoe UI", sans-serif; }
    .main-content { margin-left: 260px; padding: 36px 40px; }
    .page-title   { color: var(--green); font-size: 2rem; margin-bottom: 6px; }
    .page-sub     { color: #9a9ab5; margin-bottom: 28px; font-size: .95rem; }
    /* tabs */
    .tabs { display: flex; gap: 10px; margin-bottom: 28px; flex-wrap: wrap; }
    .tab-btn {
      background: var(--card-bg); border: 1px solid var(--border); color: #b8b8d4;
      padding: 12px 24px; border-radius: 12px; cursor: pointer; font-size: .95rem;
      font-weight: 500; display: flex; align-items: center; gap: 10px;
      transition: all .25s; font-family: inherit;
    }
    .tab-btn:hover { color: #fff; border-color: #22c55e; }
    .tab-btn.active { background: #22c55e; color: #fff; border-color: #22c55e; }
    .tab-panel { display: none; }
    .tab-panel.active { display: block; animation: fade .2s ease; }
    @keyframes fade { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }
    /* summary cards */
    .summary-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 28px; }
    .sc {
      flex: 1; min-width: 160px; background: var(--card-bg);
      border-radius: 14px; padding: 20px; border: 1px solid var(--border);
      box-shadow: 0 6px 20px rgba(0,0,0,.4);
    }
    .sc .label { font-size: .78rem; color: #9a9ab5; margin-bottom: 8px; }
    .sc .val   { font-size: 1.6rem; font-weight: 700; color: var(--text-light); }
    .sc .val.green  { color: var(--green); }
    .sc .val.yellow { color: #fbbf24; }
    .sc .val.red    { color: var(--danger); }
    .sc .bar-track  { background: #333; border-radius: 20px; height: 6px; overflow: hidden; margin-top: 10px; }
    .sc .bar-fill   { height: 100%; border-radius: 20px; background: #22c55e; transition: width .5s; }
    /* section cards */
    .s-card { background: var(--card-bg); border-radius: 16px; padding: 24px;
               box-shadow: 0 10px 30px rgba(0,0,0,.4); margin-bottom: 26px; }
    .s-card h2 { font-size: 1.1rem; color: #fbbf24; margin-bottom: 20px;
                  display: flex; align-items: center; gap: 10px; }
    /* MRO dept cards */
    .mro-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); gap: 18px; }
    .mcard { background: #1a1a2e; border-radius: 14px; padding: 20px; border: 1px solid var(--border); }
    .mcard .icon   { font-size: 1.6rem; margin-bottom: 10px; }
    .mcard .name   { font-size: 1rem; font-weight: 700; color: var(--text-light); margin-bottom: 4px; }
    .mcard .bar-track { background: #333; border-radius: 20px; height: 8px; overflow: hidden; margin: 10px 0; }
    .mcard .bar-fill  { height: 100%; border-radius: 20px; transition: width .5s; }
    .mcard .nums   { font-size: .83rem; color: #9a9ab5; display: flex; flex-direction: column; gap: 3px; }
    .mcard .nums strong { color: var(--text-light); }
    .mcard .pct    { font-size: .76rem; color: #9a9ab5; margin-top: 6px; }
    /* charts row */
    .charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 26px; }
    @media(max-width:800px){ .charts-row { grid-template-columns: 1fr; } }
    .chart-card { background: var(--card-bg); border-radius: 16px; padding: 24px;
                   box-shadow: 0 10px 30px rgba(0,0,0,.4); }
    .chart-card h3 { font-size: .95rem; color: #fbbf24; margin-bottom: 16px;
                      display: flex; align-items: center; gap: 8px; }
    .chart-card canvas { width: 100% !important; height: 100% !important; }
    /* export bar */
    .export-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
    .export-bar span { color: #9a9ab5; font-size: .85rem; }
    .exp-btn {
      background: #1e1e2e; border: 1px solid #444; color: #e0e0ff;
      padding: 9px 18px; border-radius: 10px; cursor: pointer; font-size: .88rem;
      font-weight: 600; font-family: inherit; display: inline-flex; align-items: center;
      gap: 8px; transition: .2s;
    }
    .exp-btn:hover { border-color: #22c55e; color: #22c55e; }
    .exp-btn.pdf   { border-color: #f87171; color: #f87171; }
    .exp-btn.pdf:hover { background: #f87171; color: #fff; }
    .exp-btn.csv   { border-color: #34d399; color: #34d399; }
    .exp-btn.csv:hover { background: #34d399; color: #fff; }
    /* form */
    .fg { display: grid; gap: 14px; }
    .fg.c3 { grid-template-columns: repeat(3,1fr); }
    .fg.c2 { grid-template-columns: repeat(2,1fr); }
    @media(max-width:900px){ .fg.c3,.fg.c2 { grid-template-columns: 1fr; } }
    .field label { display: block; font-size: .8rem; color: #9a9ab5; margin-bottom: 5px; }
    .field input, .field select, .field textarea {
      width: 100%; background: #1a1a2e; border: 1px solid var(--border);
      color: var(--text-light); padding: 11px 13px; border-radius: 10px;
      font-size: .93rem; font-family: inherit; outline: none; transition: border-color .2s;
    }
    .field input:focus, .field select:focus, .field textarea:focus { border-color: #22c55e; }
    .s-btn {
      background: #22c55e; color: #fff; border: none; padding: 11px 20px;
      border-radius: 12px; cursor: pointer; font-size: .93rem; font-weight: 600;
      font-family: inherit; display: inline-flex; align-items: center; gap: 8px;
      transition: .2s; margin-top: 14px;
    }
    .s-btn:hover { background: #16a34a; }
    .s-btn.secondary { background: #2a2a3a; border: 1px solid #444; color: #e0e0ff; }
    .s-btn.secondary:hover { border-color: #22c55e; color: #22c55e; }
    /* white table */
    .tw { background: #fff; border-radius: 12px; overflow-x: auto; border: 1px solid #e5e7eb; }
    .tw table { width: 100%; border-collapse: collapse; }
    .tw thead  { background: #f1f5f9; }
    .tw th { text-align: left; color: #64748b; font-weight: 600; font-size: .72rem;
              text-transform: uppercase; letter-spacing: .4px;
              padding: 12px 14px; border-bottom: 1px solid #e5e7eb; }
    .tw td { padding: 12px 14px; border-bottom: 1px solid #e5e7eb; color: #1f2937; font-size: .9rem; }
    .tw tbody tr:last-child td { border-bottom: none; }
    .tw tbody tr:hover { background: #f8fafc; }
    .tw .empty { text-align: center; color: #94a3b8; padding: 30px; font-style: italic; }
    .badge { display: inline-flex; padding: 3px 10px; border-radius: 20px; font-size: .73rem; font-weight: 600; }
    .badge.add      { background: #dbeafe; color: #1d4ed8; }
    .badge.deduct   { background: #fee2e2; color: #991b1b; }
    .badge.allocate { background: #fef9c3; color: #854d0e; }
    .badge.spend    { background: #fee2e2; color: #991b1b; }
    .badge.refund   { background: #dcfce7; color: #15803d; }
    .badge.adjust   { background: #f1f5f9; color: #64748b; }
    /* ── PRINT STYLES ─────────────────────────────────── */
    @media print {
      body { background: #fff !important; font-family: "Segoe UI", sans-serif; }
      .sidebar, .tabs, .export-bar, .s-btn, .s-card:has(#allocDept),
      .tab-panel#tab-total, button { display: none !important; }
      .main-content { margin-left: 0 !important; padding: 0 !important; }
      .tab-panel#tab-mro { display: block !important; }
      /* report header */
      .print-header { display: flex !important; }
      /* charts */
      .charts-row { display: grid !important; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
      .chart-card { background: #fff !important; border: 1px solid #ddd; border-radius: 8px;
                     padding: 16px; box-shadow: none !important; page-break-inside: avoid; }
      .chart-card h3 { color: #333 !important; }
      /* dept summary cards */
      .mcard { background: #f9fafb !important; border: 1px solid #ddd !important;
                page-break-inside: avoid; }
      .mcard .name, .mcard .nums strong { color: #111 !important; }
      .mcard .nums, .mcard .pct { color: #555 !important; }
      .mcard .bar-track { background: #e5e7eb !important; }
      /* MRO title card */
      .s-card { background: #fff !important; box-shadow: none !important;
                 border: 1px solid #ddd; border-radius: 8px; margin-bottom: 16px; page-break-inside: avoid; }
      .s-card h2 { color: #1f2937 !important; border-bottom: 2px solid #22c55e; padding-bottom: 8px; }
      /* transaction table */
      .tw { border: 1px solid #ddd; }
      .tw th { background: #f1f5f9 !important; color: #374151 !important; }
      .tw td { color: #111 !important; }
      .badge.add      { background: #dbeafe !important; color: #1d4ed8 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .badge.allocate { background: #fef9c3 !important; color: #854d0e !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .badge.spend    { background: #fee2e2 !important; color: #991b1b !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .badge.refund   { background: #dcfce7 !important; color: #15803d !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      canvas { max-height: 220px !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
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
        <li><a href="finance-budget.php" class="active"><i class="fas fa-wallet"></i> Budget</a></li>
        <li><a href="withdrawals.php"><i class="fas fa-rotate-left"></i> Withdrawals</a></li>
        <li><a href="history.html"><i class="fas fa-history"></i> History</a></li>
      </ul>
    </nav>
    <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Budget Management</h1>
    <p class="page-sub">Manage total company budget and allocate funds to MRO categories.</p>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn active" data-tab="total"><i class="fas fa-wallet"></i> Total Budget</button>
      <button class="tab-btn" data-tab="mro"><i class="fas fa-chart-pie"></i> MRO Breakdown</button>
    </div>

    <!-- ===== TAB 1: TOTAL BUDGET ===== -->
    <div class="tab-panel active" id="tab-total">
      <div class="summary-row">
        <div class="sc">
          <div class="label">Total Budget</div>
          <div class="val" id="totalBudget">₱<?= number_format($main['total_budget'] ?? 0, 2) ?></div>
        </div>
        <div class="sc">
          <div class="label">Allocated to MRO</div>
          <div class="val yellow">₱<?= number_format($main['allocated_budget'] ?? 0, 2) ?></div>
        </div>
        <div class="sc">
          <div class="label">Spent</div>
          <div class="val red">₱<?= number_format($main['spent_budget'] ?? 0, 2) ?></div>
          <div class="bar-track">
            <?php $spentPct = $main['total_budget'] > 0 ? min(100, round($main['spent_budget']/$main['total_budget']*100)) : 0; ?>
            <div class="bar-fill" style="width:<?= $spentPct ?>%;background:#f87171;"></div>
          </div>
        </div>
        <div class="sc">
          <div class="label">Remaining</div>
          <div class="val green">₱<?= number_format($main['remaining_budget'] ?? 0, 2) ?></div>
          <?php $remPct = $main['total_budget'] > 0 ? min(100, round($main['remaining_budget']/$main['total_budget']*100)) : 0; ?>
          <div class="bar-track"><div class="bar-fill" style="width:<?= $remPct ?>%;"></div></div>
        </div>
      </div>
      <div class="s-card">
        <h2><i class="fas fa-sliders"></i> Manage Total Budget</h2>
        <div class="fg c3">
          <div class="field"><label>Action *</label>
            <select id="budgetAction">
              <option value="add">Add Budget</option>
              <option value="deduct">Deduct Budget</option>
            </select>
          </div>
          <div class="field"><label>Amount (₱) *</label>
            <input id="budgetAmount" type="number" min="1" step="0.01" placeholder="e.g. 50000"/>
          </div>
          <div class="field"><label>Description *</label>
            <input id="budgetDesc" placeholder="e.g. Q3 budget allocation"/>
          </div>
        </div>
        <button class="s-btn" onclick="manageBudget()"><i class="fas fa-paper-plane"></i> Apply</button>
      </div>
      <div class="s-card">
        <h2><i class="fas fa-clock-rotate-left"></i> Recent Transactions</h2>
        <div class="tw">
          <table>
            <thead><tr><th>Type</th><th>Amount</th><th>Department</th><th>Description</th><th>Date</th></tr></thead>
            <tbody>
              <?php if(empty($txs)): ?>
              <tr><td colspan="5" class="empty">No transactions yet</td></tr>
              <?php else: foreach($txs as $tx): ?>
              <tr>
                <td><span class="badge <?= htmlspecialchars($tx['transaction_type']) ?>"><?= ucfirst(htmlspecialchars($tx['transaction_type'])) ?></span></td>
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
    </div>

    <!-- ===== TAB 2: MRO BREAKDOWN ===== -->
    <div class="tab-panel" id="tab-mro">

      <!-- Print-only report header (hidden on screen) -->
      <div class="print-header" style="display:none; align-items:center; gap:16px; margin-bottom:24px; padding-bottom:16px; border-bottom:3px solid #22c55e;">
        <div>
          <div style="font-size:1.5rem;font-weight:800;color:#111;">Procurement System</div>
          <div style="font-size:.9rem;color:#555;">MRO Budget Report — Generated <?= $reportDate ?></div>
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
          <div style="position:relative;height:280px;">
            <canvas id="barChart"></canvas>
          </div>
        </div>
        <div class="chart-card">
          <h3><i class="fas fa-chart-pie"></i> Budget Utilization</h3>
          <div style="position:relative;height:280px;">
            <canvas id="doughnutChart"></canvas>
          </div>
        </div>
      </div>

      <!-- MRO dept cards -->
      <div class="s-card">
        <h2><i class="fas fa-chart-pie"></i> MRO Budget Breakdown</h2>
        <div class="mro-grid">
          <?php
          $mroMeta = [
            'Maintenance' => ['#fbbf24', 'fa-wrench'],
            'Repair'      => ['#f87171', 'fa-screwdriver-wrench'],
            'Operations'  => ['#a78bfa', 'fa-industry'],
          ];
          foreach($depts as $d):
            $key = $d['department_name'];
            [$col,$ico] = $mroMeta[$key] ?? ['#9a9ab5','fa-circle'];
            $pct = $d['allocated_amount'] > 0 ? round(($d['spent_amount']/$d['allocated_amount'])*100,1) : 0;
          ?>
          <div class="mcard">
            <div class="icon" style="color:<?= $col ?>"><i class="fas <?= $ico ?>"></i></div>
            <div class="name"><?= htmlspecialchars($key) ?></div>
            <div class="bar-track"><div class="bar-fill" style="width:<?= min($pct,100) ?>%;background:<?= $col ?>;"></div></div>
            <div class="nums">
              <span>Allocated: <strong>₱<?= number_format($d['allocated_amount'],2) ?></strong></span>
              <span>Spent: <strong>₱<?= number_format($d['spent_amount'],2) ?></strong></span>
              <span>Remaining: <strong>₱<?= number_format($d['remaining_amount'],2) ?></strong></span>
            </div>
            <div class="pct"><?= $pct ?>% utilized</div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Allocate to MRO -->
      <div class="s-card">
        <h2><i class="fas fa-hand-holding-dollar"></i> Allocate Budget to MRO Category</h2>
        <div class="fg c3">
          <div class="field"><label>Category *</label>
            <select id="allocDept">
              <option value="">Select category</option>
              <option value="Maintenance">Maintenance</option>
              <option value="Repair">Repair</option>
              <option value="Operations">Operations</option>
            </select>
          </div>
          <div class="field"><label>Amount (₱) *</label>
            <input id="allocAmount" type="number" min="1" step="0.01" placeholder="e.g. 20000"/>
          </div>
          <div class="field"><label>Description</label>
            <input id="allocDesc" placeholder="e.g. Q3 Repair Fund"/>
          </div>
        </div>
        <button class="s-btn" onclick="allocateMRO()"><i class="fas fa-paper-plane"></i> Allocate</button>
      </div>

      <!-- MRO transaction history -->
      <div class="s-card">
        <h2><i class="fas fa-clock-rotate-left"></i> MRO Transaction History</h2>
        <div class="tw">
          <table id="mro-tx-table">
            <thead><tr><th>Type</th><th>Amount</th><th>Category</th><th>Description</th><th>Date</th></tr></thead>
            <tbody>
              <?php if(empty($mrotxs)): ?>
              <tr><td colspan="5" class="empty">No MRO transactions yet</td></tr>
              <?php else: foreach($mrotxs as $tx): ?>
              <tr>
                <td><span class="badge <?= htmlspecialchars($tx['transaction_type']) ?>"><?= ucfirst(htmlspecialchars($tx['transaction_type'])) ?></span></td>
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
    </div><!-- /tab-mro -->

  </main>
</div>

<script>
// ── PHP data passed to JS ─────────────────────────
const depts   = <?= $deptsJson ?>;
const mrotxs  = <?= $mrotxsJson ?>;

// ── tabs ──────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(b => b.addEventListener('click', () => {
  document.querySelectorAll('.tab-btn').forEach(x => x.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(x => x.classList.remove('active'));
  b.classList.add('active');
  document.getElementById('tab-' + b.dataset.tab).classList.add('active');
}));

// ── Chart.js setup ────────────────────────────────
const labels  = depts.map(d => d.department_name);
const colors  = { Maintenance:'#fbbf24', Repair:'#f87171', Operations:'#a78bfa' };
const bgColors = labels.map(l => colors[l] || '#9a9ab5');

// Bar chart — Allocated vs Spent
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
    maintainAspectRatio: false,
    plugins: {
      legend: { labels: { color: '#e0e0ff', font: { size: 12 } } },
      tooltip: {
        callbacks: {
          label: ctx => ` ₱${ctx.parsed.y.toLocaleString('en-PH', {minimumFractionDigits:2})}`
        }
      }
    },
    scales: {
      x: { ticks: { color: '#b8b8d4' }, grid: { color: '#2a2a3a' } },
      y: {
        ticks: {
          color: '#b8b8d4',
          callback: v => '₱' + v.toLocaleString()
        },
        grid: { color: '#2a2a3a' }
      }
    }
  }
});

// Doughnut chart — utilization per dept
window._doughnutChart = new Chart(document.getElementById('doughnutChart'), {
  type: 'doughnut',
  data: {
    labels: labels.map(l => {
      const d = depts.find(x => x.department_name === l);
      const pct = d.allocated_amount > 0
        ? ((d.spent_amount / d.allocated_amount) * 100).toFixed(1)
        : 0;
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
    maintainAspectRatio: false,
    cutout: '65%',
    plugins: {
      legend: { position: 'bottom', labels: { color: '#e0e0ff', padding: 16, font: { size: 12 } } },
      tooltip: {
        callbacks: {
          label: ctx => ` ₱${ctx.parsed.toLocaleString('en-PH', {minimumFractionDigits:2})} spent`
        }
      }
    }
  }
});

// ── Print / PDF ───────────────────────────────────
function printMROReport() {
  // Make sure MRO tab is active before printing
  document.querySelectorAll('.tab-btn').forEach(x => x.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(x => x.classList.remove('active'));
  document.querySelector('[data-tab="mro"]').classList.add('active');
  document.getElementById('tab-mro').classList.add('active');
  setTimeout(() => window.print(), 300);
}

// ── CSV Export ────────────────────────────────────
function exportMROCSV() {
  if (!mrotxs || mrotxs.length === 0) {
    Swal.fire({ icon:'info', title:'No Data', text:'No MRO transactions to export.', confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff' }); return;
  }
  const headers = ['Type','Amount (PHP)','Category','Description','Date'];
  const rows = mrotxs.map(tx => [
    tx.transaction_type,
    parseFloat(tx.amount).toFixed(2),
    tx.department || '',
    (tx.description || '').replace(/,/g, ';'),
    tx.created_at
  ]);
  const csv = [headers, ...rows].map(r => r.map(v => `"${v}"`).join(',')).join('\n');
  const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = url;
  a.download = `MRO_Transactions_<?= date('Y-m-d') ?>.csv`;
  a.click();
  URL.revokeObjectURL(url);
}

// ── Realtime budget refresh ───────────────────────
const SNAP = '../../Admin/PHP/get_budget_snapshot.php';

function fmt(n){ return '₱' + Number(n).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }

async function refreshBudget(){
  const res = await fetch(SNAP);
  const data = await res.json();
  if(!data.success) return;

  const m = data.main;

  // ── Summary cards (tab-total) ──
  const totalEl = document.getElementById('totalBudget');
  if(totalEl) totalEl.textContent = fmt(m.total_budget);
  const cards = document.querySelectorAll('.sc .val');
  // cards order: Total, Allocated, Spent, Remaining
  const scVals = document.querySelectorAll('#tab-total .sc .val');
  if(scVals[0]) scVals[0].textContent = fmt(m.total_budget);
  if(scVals[1]) scVals[1].textContent = fmt(m.allocated_budget);
  if(scVals[2]) scVals[2].textContent = fmt(m.spent_budget);
  if(scVals[3]) scVals[3].textContent = fmt(m.remaining_budget);

  // ── Progress bars (tab-total) ──
  const spentPct  = m.total_budget > 0 ? Math.min(100, Math.round(m.spent_budget/m.total_budget*100)) : 0;
  const remPct    = m.total_budget > 0 ? Math.min(100, Math.round(m.remaining_budget/m.total_budget*100)) : 0;
  const bars = document.querySelectorAll('#tab-total .sc .bar-fill');
  if(bars[0]) bars[0].style.width = spentPct + '%';
  if(bars[1]) bars[1].style.width = remPct + '%';

  // ── MRO dept cards ──
  const mroColors = {Maintenance:'#fbbf24',Repair:'#f87171',Operations:'#a78bfa'};
  data.depts.forEach(d => {
    const pct = d.allocated_amount > 0 ? Math.round((d.spent_amount/d.allocated_amount)*100*10)/10 : 0;
    document.querySelectorAll('#tab-mro .mcard').forEach(card => {
      if(card.querySelector('.name')?.textContent.trim() !== d.department_name) return;
      const bar = card.querySelector('.bar-fill');
      if(bar) bar.style.width = Math.min(pct,100) + '%';
      const nums = card.querySelectorAll('.nums strong');
      if(nums[0]) nums[0].textContent = fmt(d.allocated_amount);
      if(nums[1]) nums[1].textContent = fmt(d.spent_amount);
      if(nums[2]) nums[2].textContent = fmt(d.remaining_amount);
      const pctEl = card.querySelector('.pct');
      if(pctEl) pctEl.textContent = pct + '% utilized';
    });
  });

  // ── Recent Transactions table (tab-total) ──
  const txTbody = document.querySelector('#tab-total .tw tbody');
  if(txTbody && data.txs.length){
    txTbody.innerHTML = data.txs.map(tx=>`
      <tr>
        <td><span class="badge ${tx.transaction_type}">${tx.transaction_type.charAt(0).toUpperCase()+tx.transaction_type.slice(1)}</span></td>
        <td>${fmt(tx.amount)}</td>
        <td>${tx.department || '—'}</td>
        <td>${tx.description || '—'}</td>
        <td>${new Date(tx.created_at).toLocaleString('en-PH',{year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}</td>
      </tr>`).join('');
  }

  // ── MRO Transaction History table (tab-mro) ──
  const mroTbody = document.querySelector('#mro-tx-table tbody');
  if(mroTbody){
    if(!data.mrotxs.length){
      mroTbody.innerHTML = '<tr><td colspan="5" class="empty">No MRO transactions yet</td></tr>';
    } else {
      mroTbody.innerHTML = data.mrotxs.map(tx=>`
        <tr>
          <td><span class="badge ${tx.transaction_type}">${tx.transaction_type.charAt(0).toUpperCase()+tx.transaction_type.slice(1)}</span></td>
          <td>${fmt(tx.amount)}</td>
          <td>${tx.department || '—'}</td>
          <td>${tx.description || '—'}</td>
          <td>${new Date(tx.created_at).toLocaleString('en-PH',{year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}</td>
        </tr>`).join('');
    }
  }

  // ── Update Chart.js charts ──
  if(window._barChart && window._doughnutChart){
    window._barChart.data.datasets[0].data = data.depts.map(d=>parseFloat(d.allocated_amount));
    window._barChart.data.datasets[1].data = data.depts.map(d=>parseFloat(d.spent_amount));
    window._barChart.update('active');

    window._doughnutChart.data.datasets[0].data = data.depts.map(d=>parseFloat(d.spent_amount)||0.001);
    const dColors = {Maintenance:'#fbbf24',Repair:'#f87171',Operations:'#a78bfa'};
    window._doughnutChart.data.labels = data.depts.map(d=>{
      const pct = d.allocated_amount>0 ? ((d.spent_amount/d.allocated_amount)*100).toFixed(1) : 0;
      return `${d.department_name} (${pct}%)`;
    });
    window._doughnutChart.update('active');
  }
}

// Poll every 15 seconds for changes made by others (Finance approvals etc.)
setInterval(refreshBudget, 15000);

// ── Manage total budget ───────────────────────────
async function manageBudget(){
  const action = document.getElementById('budgetAction').value;
  const amount = parseFloat(document.getElementById('budgetAmount').value);
  const desc   = document.getElementById('budgetDesc').value.trim();
  if(!amount || amount <= 0 || !desc){
    Swal.fire({icon:'warning',title:'Missing fields',text:'Enter a valid amount and description.'}); return;
  }
  const label = action === 'add' ? 'Add' : 'Deduct';
  const r = await Swal.fire({title:`${label} ₱${amount.toLocaleString()} ?`,icon:'question',showCancelButton:true,confirmButtonColor:'#22c55e',confirmButtonText:label});
  if(!r.isConfirmed) return;
  const res = await fetch('../../Admin/PHP/add_budget.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action,amount,description:desc})});
  const d = await res.json();
  if(d.success){
    Swal.fire({icon:'success',title:'Done',text:d.message,confirmButtonColor:'#22c55e'});
    document.getElementById('budgetAmount').value = '';
    document.getElementById('budgetDesc').value = '';
    refreshBudget();
  } else {
    Swal.fire({icon:'error',title:'Error',text:d.message});
  }
}

// ── Allocate to MRO ───────────────────────────────
async function allocateMRO(){
  const dept   = document.getElementById('allocDept').value;
  const amount = parseFloat(document.getElementById('allocAmount').value);
  const desc   = document.getElementById('allocDesc').value.trim();
  if(!dept || !amount || amount <= 0){
    Swal.fire({icon:'warning',title:'Missing fields',text:'Select a category and enter a valid amount.'}); return;
  }
  const r = await Swal.fire({title:`Allocate ₱${amount.toLocaleString()} to ${dept}?`,icon:'question',showCancelButton:true,confirmButtonColor:'#22c55e',confirmButtonText:'Allocate'});
  if(!r.isConfirmed) return;
  const res = await fetch('../../Admin/PHP/dept_budget.php?action=allocate',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({department:dept,amount,description:desc})});
  const d = await res.json();
  if(d.success){
    Swal.fire({icon:'success',title:'Done',text:d.message,confirmButtonColor:'#22c55e'});
    document.getElementById('allocDept').value = '';
    document.getElementById('allocAmount').value = '';
    document.getElementById('allocDesc').value = '';
    refreshBudget();
  } else {
    Swal.fire({icon:'error',title:'Error',text:d.message});
  }
}
</script>
</body>
</html>
