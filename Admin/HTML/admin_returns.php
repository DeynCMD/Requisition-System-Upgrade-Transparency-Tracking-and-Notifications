<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header('Location: ZE-Electronics.php'); exit;
}
require_once '../PHP/currency_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Item Returns — Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../CSS/admin_style.css?v=<?= time() ?>">
  <style>
    .main-content{margin-left:260px;padding:36px 40px;}
    .page-title{color:var(--green);font-size:2rem;margin-bottom:6px;}
    .page-sub{color:#9a9ab5;margin-bottom:28px;font-size:.95rem;}
    /* filter bar */
    .filter-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;align-items:center;}
    .filter-btn{background:var(--card-bg);border:1px solid var(--border);color:#b8b8d4;padding:9px 18px;border-radius:12px;cursor:pointer;font-size:.88rem;font-family:inherit;transition:.2s;}
    .filter-btn:hover,.filter-btn.active{background:#22c55e;color:#fff;border-color:#22c55e;}
    /* s-card */
    .s-card{background:var(--card-bg);border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.4);margin-bottom:26px;}
    .s-card h2{font-size:1.05rem;color:var(--yellow);margin-bottom:18px;display:flex;align-items:center;gap:10px;}
    /* return cards */
    .ret-card{background:#1e1e2e;border:1px solid var(--border);border-radius:14px;padding:20px 22px;margin-bottom:16px;transition:.2s;}
    .ret-card:hover{border-color:#555;}
    .ret-card.pending{border-left:4px solid var(--yellow);}
    .ret-card.approved{border-left:4px solid var(--green);}
    .ret-card.rejected{border-left:4px solid var(--red);}
    .ret-card.returned{border-left:4px solid var(--purple);}
    .ret-top{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:14px;}
    .ret-po{font-size:1rem;font-weight:800;color:#a78bfa;}
    .ret-mpn{font-size:1.1rem;font-weight:700;color:var(--text-light);margin-top:2px;}
    .ret-meta{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:12px;}
    .ret-meta-item{display:flex;align-items:center;gap:6px;font-size:.83rem;color:#9a9ab5;}
    .ret-meta-item i{width:14px;color:#555;}
    .ret-meta-item strong{color:var(--text-light);}
    .desc-box{background:#12121a;border-left:3px solid #444;border-radius:0 8px 8px 0;padding:10px 14px;font-size:.83rem;color:#9a9ab5;font-style:italic;margin-bottom:14px;line-height:1.5;}
    /* badges */
    .badge{display:inline-flex;padding:4px 11px;border-radius:20px;font-size:.73rem;font-weight:700;}
    .badge.green{background:#dcfce7;color:#15803d;}
    .badge.yellow{background:#fef9c3;color:#854d0e;}
    .badge.red{background:#fee2e2;color:#991b1b;}
    .badge.blue{background:#dbeafe;color:#1d4ed8;}
    .badge.purple{background:#ede9fe;color:#6d28d9;}
    .badge.gray{background:#f1f5f9;color:#64748b;}
    /* reason badge */
    .reason-defective{background:#fee2e2;color:#991b1b;}
    .reason-wrong_item{background:#fef9c3;color:#854d0e;}
    .reason-damaged_shipping{background:#ffedd5;color:#9a3412;}
    .reason-overdelivery{background:#dbeafe;color:#1d4ed8;}
    .reason-other{background:#f1f5f9;color:#64748b;}
    /* action buttons */
    .s-btn{background:#22c55e;color:#fff;border:none;padding:9px 18px;border-radius:10px;cursor:pointer;font-weight:600;font-size:.85rem;font-family:inherit;display:inline-flex;align-items:center;gap:7px;transition:.2s;}
    .s-btn:hover{background:#16a34a;}
    .s-btn.red{background:#ef4444;}.s-btn.red:hover{background:#dc2626;}
    .s-btn.blue{background:#3b82f6;}.s-btn.blue:hover{background:#2563eb;}
    .s-btn.sm{padding:7px 13px;font-size:.82rem;border-radius:9px;}
    .actions-row{display:flex;gap:8px;flex-wrap:wrap;}
    /* summary chips */
    .chip-row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;}
    .chip{background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:12px 18px;min-width:120px;}
    .chip .cl{font-size:.72rem;color:var(--gray);text-transform:uppercase;margin-bottom:3px;}
    .chip .cv{font-size:1.5rem;font-weight:800;}
    /* empty state */
    .empty-state{text-align:center;padding:48px;color:#9a9ab5;}
    .empty-state i{font-size:2.5rem;color:#333;display:block;margin-bottom:12px;}
    /* toast */
    .toast{position:fixed;bottom:24px;right:24px;background:#22c55e;color:#fff;padding:12px 20px;border-radius:12px;font-weight:600;font-size:.9rem;box-shadow:0 10px 30px rgba(0,0,0,.5);opacity:0;transform:translateY(10px);transition:.25s;pointer-events:none;z-index:9999;}
    .toast.show{opacity:1;transform:none;}
    .toast.err{background:#ef4444;}
  </style>
</head>
<body>
<div class="container">

  <aside class="sidebar">
    <div class="profile"><img src="../Assets/Avatar.jpg" alt="Admin"/><span class="role">ADMIN</span></div>
    <nav class="nav-menu"><ul>
      <li><a href="AdminZE.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="Admin-users.php"><i class="fas fa-users"></i> User Management</a></li>
      <li><a href="Pending-approvals.php"><i class="fas fa-clock"></i> Pending Approvals</a></li>
      <li><a href="suppliers.php"><i class="fas fa-truck-field"></i> Suppliers</a></li>
      <li><a href="admin_price_prediction.php"><i class="fas fa-chart-line"></i> Price Prediction</a></li>
      <li><a href="admin_returns.php" class="active"><i class="fas fa-rotate-left"></i> Item Returns</a></li>
      <li><a href="HistoryZE.php"><i class="fas fa-history"></i> History</a></li>
    </ul></nav>
    <a href="../PHP/logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Item Returns</h1>

    <div class="chip-row" id="chip-row">
      <div class="chip"><div class="cl">Total</div><div class="cv" id="c-total">—</div></div>
      <div class="chip" style="border-color:#fbbf2444"><div class="cl">Pending</div><div class="cv" style="color:var(--yellow)" id="c-pending">—</div></div>
      <div class="chip" style="border-color:#22c55e44"><div class="cl">Approved</div><div class="cv" style="color:var(--green)" id="c-approved">—</div></div>
      <div class="chip" style="border-color:#f8717144"><div class="cl">Rejected</div><div class="cv" style="color:var(--red)" id="c-rejected">—</div></div>
      <div class="chip" style="border-color:#a78bfa44"><div class="cl">Completed</div><div class="cv" style="color:var(--purple)" id="c-returned">—</div></div>
    </div>

    <div class="filter-bar">
      <button class="filter-btn active" onclick="setFilter('all',this)">All</button>
      <button class="filter-btn"        onclick="setFilter('pending',this)">Pending</button>
      <button class="filter-btn"        onclick="setFilter('approved',this)">Approved</button>
      <button class="filter-btn"        onclick="setFilter('rejected',this)">Rejected</button>
      <button class="filter-btn"        onclick="setFilter('returned',this)">Completed</button>
    </div>

    <div id="returns-wrap">
      <div style="text-align:center;padding:40px;color:#9a9ab5"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
    </div>
  </main>
</div>

<div class="toast" id="toast"></div>

<script>
const API = '../PHP/admin_returns.php';
const SYMBOLS = {PHP:'₱',USD:'$',EUR:'€',JPY:'¥',GBP:'£',SGD:'S$',CNY:'¥'};
const sym = cur => SYMBOLS[(cur||'PHP').toUpperCase()] || cur+' ';
const esc = s => String(s??'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

let _tt;
function toast(msg,err=false){
  const t=document.getElementById('toast');
  t.textContent=msg; t.className='toast show'+(err?' err':'');
  clearTimeout(_tt); _tt=setTimeout(()=>t.className='toast',2800);
}

let currentFilter = 'all';

function setFilter(f, btn){
  currentFilter = f;
  document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  loadReturns();
}

const REASON_LABELS = {
  defective:'Defective / Not Working',
  wrong_item:'Wrong Item',
  damaged_shipping:'Damaged in Shipping',
  overdelivery:'Over-delivery',
  other:'Other'
};
const STATUS_BADGE = {
  pending:'yellow',approved:'green',rejected:'red',returned:'purple'
};

async function loadReturns(){
  const wrap = document.getElementById('returns-wrap');
  wrap.innerHTML = '<div style="text-align:center;padding:40px;color:#9a9ab5"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';

  const res  = await fetch(`${API}?action=list&status=${currentFilter}`);
  const d    = await res.json();
  const rows = d.returns || [];

  // Update chips (always fetch all for counts)
  const all = (await (await fetch(`${API}?action=list&status=all`)).json()).returns || [];
  document.getElementById('c-total').textContent   = all.length;
  document.getElementById('c-pending').textContent = all.filter(r=>r.status==='pending').length;
  document.getElementById('c-approved').textContent= all.filter(r=>r.status==='approved').length;
  document.getElementById('c-rejected').textContent= all.filter(r=>r.status==='rejected').length;
  document.getElementById('c-returned').textContent= all.filter(r=>r.status==='returned').length;

  if(!rows.length){
    wrap.innerHTML = `<div class="empty-state">
      <i class="fas fa-box-open"></i>
      <p>No return requests found.</p>
    </div>`;
    return;
  }

  wrap.innerHTML = rows.map(r => {
    const s         = sym(r.currency);
    const badge     = STATUS_BADGE[r.status] || 'gray';
    const reasonCls = 'reason-'+r.status; // reuse class for reason
    const subDate   = new Date(r.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
    const revDate   = r.reviewed_at ? new Date(r.reviewed_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : null;

    const actionsHtml = (() => {
      if(r.status === 'pending') return `
        <div class="actions-row">
          <button class="s-btn sm" onclick="approveReturn(${r.id})">
            <i class="fas fa-check"></i> Approve
          </button>
          <button class="s-btn sm red" onclick="rejectReturn(${r.id})">
            <i class="fas fa-xmark"></i> Reject
          </button>
        </div>`;
      if(r.status === 'approved') return `
        <div class="actions-row">
          <button class="s-btn sm blue" onclick="completeReturn(${r.id},'${esc(r.po_number)}')">
            <i class="fas fa-box-open"></i> Mark as Returned
          </button>
        </div>`;
      return '';
    })();

    return `
    <div class="ret-card ${r.status}">
      <div class="ret-top">
        <div>
          <div class="ret-po">${esc(r.po_number)} <span style="font-size:.78rem;color:#9a9ab5;font-weight:400">→ ${esc(r.pr_number)}</span></div>
          <div class="ret-mpn">${esc(r.mpn||'—')}</div>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-start;flex-wrap:wrap">
          <span class="badge ${badge}">${r.status.charAt(0).toUpperCase()+r.status.slice(1)}</span>
          <span class="badge reason-${r.reason}">${REASON_LABELS[r.reason]||r.reason}</span>
        </div>
      </div>
      <div class="ret-meta">
        <div class="ret-meta-item"><i class="fas fa-building"></i>${esc(r.supplier_name)}</div>
        <div class="ret-meta-item"><i class="fas fa-boxes-stacked"></i>Qty Returned: <strong>${Number(r.quantity_returned).toLocaleString()} of ${Number(r.po_qty).toLocaleString()} units</strong></div>
        <div class="ret-meta-item"><i class="fas fa-tag"></i>${esc(r.category||'—')}</div>
        <div class="ret-meta-item"><i class="fas fa-user"></i>Requested by <strong>${esc(r.requested_by_name)}</strong></div>
        <div class="ret-meta-item"><i class="fas fa-calendar"></i>${subDate}</div>
        ${revDate ? `<div class="ret-meta-item"><i class="fas fa-calendar-check"></i>Reviewed: <strong>${revDate}</strong> by ${esc(r.reviewed_by_name||'—')}</div>` : ''}
      </div>
      <div class="desc-box"><i class="fas fa-quote-left" style="margin-right:6px;color:#555"></i>${esc(r.description)}</div>
      ${r.admin_notes ? `<div style="background:#1a1a2e;border-left:3px solid var(--blue);border-radius:0 8px 8px 0;padding:8px 14px;font-size:.82rem;color:#9a9ab5;margin-bottom:12px">
        <strong style="color:var(--blue)">Admin notes:</strong> ${esc(r.admin_notes)}
      </div>` : ''}
      ${actionsHtml}
    </div>`;
  }).join('');
}

async function approveReturn(retId){
  const r = await Swal.fire({
    title: 'Approve this return?',
    input: 'textarea',
    inputLabel: 'Admin notes (optional)',
    inputPlaceholder: 'Any instructions for the buyer or supplier…',
    showCancelButton: true,
    confirmButtonColor: '#22c55e',
    confirmButtonText: '<i class="fas fa-check"></i> Approve Return'
  });
  if(!r.isConfirmed) return;
  const res = await fetch(`${API}?action=approve`,{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({return_id: retId, admin_notes: r.value||''})
  });
  const d = await res.json();
  if(d.success){ toast('Return approved'); loadReturns(); }
  else toast(d.message, true);
}

async function rejectReturn(retId){
  const r = await Swal.fire({
    title: 'Reject this return?',
    input: 'textarea',
    inputLabel: 'Rejection reason *',
    inputPlaceholder: 'Explain why the return is rejected (min 5 chars)…',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    confirmButtonText: '<i class="fas fa-xmark"></i> Reject Return',
    inputValidator: v => { if(!v||v.trim().length<5) return 'Please provide a rejection reason (min 5 chars)'; }
  });
  if(!r.isConfirmed||!r.value) return;
  const res = await fetch(`${API}?action=reject`,{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({return_id: retId, admin_notes: r.value})
  });
  const d = await res.json();
  if(d.success){ toast('Return rejected'); loadReturns(); }
  else toast(d.message, true);
}

async function completeReturn(retId, poNum){
  const r = await Swal.fire({
    title: `Mark return as completed?`,
    html: `<div style="text-align:left;font-size:.93rem;line-height:2">
      <strong>PO:</strong> ${esc(poNum)}<br>
      <span style="color:#fbbf24"><i class="fas fa-triangle-exclamation"></i>
      This will mark the PO as Cancelled and close the return.</span>
    </div>`,
    icon:'question', showCancelButton:true,
    confirmButtonColor:'#3b82f6', confirmButtonText:'<i class="fas fa-box-open"></i> Confirm Returned'
  });
  if(!r.isConfirmed) return;
  const res = await fetch(`${API}?action=complete`,{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({return_id: retId})
  });
  const d = await res.json();
  if(d.success){ toast('Return completed. PO cancelled.'); loadReturns(); }
  else toast(d.message, true);
}

loadReturns();
</script>
</body>
</html>
