<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'BUYER') {
    header("Location: ../../Admin/HTML/ZE-Electronics.php"); exit;
}
require_once '../../Admin/PHP/currency_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Returns — Buyer</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../CSS/buyer_dashboard.css?v=<?= time() ?>"/>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:"Segoe UI",sans-serif;}
    body{background:#12121a;color:#e0e0ff;min-height:100vh;}
    .container{display:flex;min-height:100vh;}
    .sidebar{width:260px;background:#2d2d44;padding:30px 20px;display:flex;flex-direction:column;position:fixed;height:100%;overflow-y:auto;}
    .profile{margin-bottom:32px;text-align:center;}
    .profile img{width:100px;height:100px;border-radius:50%;object-fit:cover;border:4px solid #a78bfa;}
    .role{background:#22c55e;color:#fff;font-size:.8rem;padding:6px 16px;border-radius:30px;display:inline-block;margin-top:12px;font-weight:600;}
    .nav-menu ul{list-style:none;}
    .nav-menu a{color:#b0b0cc;text-decoration:none;display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:12px;transition:.3s;font-weight:500;}
    .nav-menu a:hover,.nav-menu a.active{background:#22c55e;color:#fff;}
    .nav-menu i{width:20px;text-align:center;}
    .logout-btn{background:#ef4444;color:#fff;padding:14px;border-radius:12px;font-weight:700;font-size:1rem;text-decoration:none;text-align:center;display:block;margin-top:auto;transition:.3s;}
    .logout-btn:hover{background:#f87171;}
    .main-content{margin-left:260px;padding:36px 40px;}
    .page-title{color:#4ade80;font-size:2rem;margin-bottom:6px;}
    .page-sub{color:#9a9ab5;margin-bottom:28px;font-size:.95rem;}
    /* tabs */
    .tabs{display:flex;gap:10px;margin-bottom:26px;}
    .tab-btn{background:#2a2a3a;border:1px solid #444;color:#b8b8d4;padding:11px 20px;border-radius:12px;cursor:pointer;font-size:.92rem;font-weight:500;font-family:inherit;display:flex;align-items:center;gap:8px;transition:.2s;}
    .tab-btn:hover{border-color:#4ade80;color:#fff;}
    .tab-btn.active{background:#22c55e;color:#fff;border-color:#22c55e;}
    .tab-panel{display:none;} .tab-panel.active{display:block;}
    /* cards */
    .s-card{background:#2a2a3a;border-radius:16px;padding:24px;margin-bottom:22px;box-shadow:0 4px 16px rgba(0,0,0,.3);}
    .s-card h2{font-size:1.05rem;color:#fbbf24;margin-bottom:18px;display:flex;align-items:center;gap:10px;}
    /* PO cards */
    .po-card{background:#1e1e2e;border:1px solid #444;border-radius:14px;padding:18px 20px;margin-bottom:14px;transition:.2s;}
    .po-card:hover{border-color:#555;}
    .po-top{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:12px;}
    .po-num{font-size:1rem;font-weight:800;color:#a78bfa;}
    .po-mpn{font-size:1.1rem;font-weight:700;color:#e0e0ff;margin-top:2px;}
    .po-meta{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:14px;}
    .po-meta-item{display:flex;align-items:center;gap:6px;font-size:.83rem;color:#9a9ab5;}
    .po-meta-item i{width:14px;color:#555;}
    .po-meta-item strong{color:#e0e0ff;}
    /* badge */
    .badge{display:inline-flex;padding:4px 10px;border-radius:20px;font-size:.73rem;font-weight:700;}
    .badge.green{background:#dcfce7;color:#15803d;}
    .badge.yellow{background:#fef9c3;color:#854d0e;}
    .badge.red{background:#fee2e2;color:#991b1b;}
    .badge.blue{background:#dbeafe;color:#1d4ed8;}
    .badge.gray{background:#f1f5f9;color:#64748b;}
    .badge.purple{background:#ede9fe;color:#6d28d9;}
    /* return status */
    .rs-pending{background:#fef9c3;color:#854d0e;}
    .rs-approved{background:#dcfce7;color:#15803d;}
    .rs-rejected{background:#fee2e2;color:#991b1b;}
    .rs-returned{background:#ede9fe;color:#6d28d9;}
    /* btn */
    .btn-return{background:#ef4444;color:#fff;border:none;padding:9px 18px;border-radius:10px;cursor:pointer;font-weight:600;font-size:.85rem;font-family:inherit;display:inline-flex;align-items:center;gap:7px;transition:.2s;}
    .btn-return:hover{background:#dc2626;}
    /* table */
    .dark-table{width:100%;border-collapse:collapse;}
    .dark-table th{text-align:left;color:#9a9ab5;font-weight:normal;padding-bottom:10px;font-size:.85rem;border-bottom:1px solid #444;}
    .dark-table td{padding:12px 0;border-top:1px solid #333;font-size:.88rem;color:#e0e0ff;}
    .dark-table tr:hover td{background:rgba(34,197,94,.04);}
    .dark-table .empty-row td{text-align:center;color:#9a9ab5;padding:32px;font-style:italic;}
    /* modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;pointer-events:none;transition:opacity .2s;}
    .modal-overlay.open{opacity:1;pointer-events:all;}
    .modal-box{background:#2a2a3a;border:1px solid #444;border-radius:20px;padding:28px 32px;width:100%;max-width:540px;box-shadow:0 20px 60px rgba(0,0,0,.6);transform:translateY(12px);transition:transform .2s;position:relative;max-height:90vh;overflow-y:auto;}
    .modal-overlay.open .modal-box{transform:none;}
    .modal-box h2{font-size:1.15rem;color:#f87171;margin-bottom:4px;}
    .modal-sub{font-size:.85rem;color:#9a9ab5;margin-bottom:20px;}
    .modal-close{position:absolute;top:14px;right:18px;background:none;border:none;color:#9a9ab5;font-size:1.4rem;cursor:pointer;}
    .modal-close:hover{color:#f87171;}
    .field label{display:block;font-size:.8rem;color:#9a9ab5;margin-bottom:6px;}
    .field input,.field select,.field textarea{width:100%;background:#1e1e2e;border:1px solid #444;color:#e0e0ff;padding:10px 12px;border-radius:10px;font-size:.92rem;font-family:inherit;outline:none;transition:border-color .2s;}
    .field input:focus,.field select:focus,.field textarea:focus{border-color:#f87171;}
    .field textarea{resize:vertical;min-height:90px;}
    .fg.c2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .s-btn{background:#22c55e;color:#fff;border:none;padding:10px 20px;border-radius:10px;cursor:pointer;font-size:.92rem;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;gap:8px;transition:.2s;}
    .s-btn:hover{background:#16a34a;}
    .s-btn.red{background:#ef4444;}.s-btn.red:hover{background:#dc2626;}
    /* po info strip in modal */
    .po-info{background:#1e1e2e;border:1px solid #333;border-radius:10px;padding:12px 16px;margin-bottom:18px;display:flex;flex-wrap:wrap;gap:16px;}
    .pi-item .pi-label{font-size:.7rem;color:#9a9ab5;text-transform:uppercase;margin-bottom:2px;}
    .pi-item .pi-val{font-size:.9rem;font-weight:700;color:#e0e0ff;}
    .pi-item .pi-val.accent{color:#a78bfa;}
    /* toast */
    .toast{position:fixed;bottom:24px;right:24px;background:#22c55e;color:#fff;padding:12px 20px;border-radius:12px;font-weight:600;font-size:.9rem;box-shadow:0 10px 30px rgba(0,0,0,.5);opacity:0;transform:translateY(10px);transition:.25s;pointer-events:none;z-index:9999;}
    .toast.show{opacity:1;transform:none;}
    .toast.err{background:#ef4444;}
  </style>
</head>
<body>
<div class="container">

  <aside class="sidebar">
    <div class="profile">
      <img src="../Assets/Avatar.jpg" alt="Buyer"/>
      <span class="role">BUYER</span>
    </div>
    <nav class="nav-menu"><ul>
      <li><a href="buyer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="buyer.php"><i class="fas fa-shopping-cart"></i> Purchase Requests</a></li>
      <li><a href="buyer_history.php"><i class="fas fa-history"></i> History</a></li>
      <li><a href="buyer_export.php"><i class="fas fa-file-export"></i> Export PO</a></li>
      <li><a href="buyer_returns.php" class="active"><i class="fas fa-rotate-left"></i> Returns</a></li>
    </ul></nav>
    <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Item Returns</h1>

    <div class="tabs">
      <button class="tab-btn active" data-tab="new-return"><i class="fas fa-plus-circle"></i> Request Return</button>
      <button class="tab-btn"        data-tab="my-returns"><i class="fas fa-list"></i> My Returns</button>
    </div>

    <!-- TAB 1: Request new return -->
    <div class="tab-panel active" id="tab-new-return">
      <div class="s-card">
        <h2><i class="fas fa-boxes-stacked"></i> Received Purchase Orders
          <span style="font-size:.78rem;color:#9a9ab5;font-weight:400;margin-left:8px;">Select a PO to request a return</span>
        </h2>
        <div id="po-list-wrap">
          <div style="color:#9a9ab5;padding:20px;text-align:center"><i class="fas fa-spinner fa-spin"></i> Loading…</div>
        </div>
      </div>
    </div>

    <!-- TAB 2: My return history -->
    <div class="tab-panel" id="tab-my-returns">
      <div class="s-card">
        <h2><i class="fas fa-rotate-left"></i> My Return Requests</h2>
        <table class="dark-table">
          <thead>
            <tr>
              <th>PO #</th><th>PR #</th><th>MPN</th><th>Qty Returned</th>
              <th>Reason</th><th>Status</th><th>Admin Notes</th><th>Submitted</th>
            </tr>
          </thead>
          <tbody id="returns-tbody">
            <tr class="empty-row"><td colspan="8">Loading…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<!-- RETURN REQUEST MODAL -->
<div class="modal-overlay" id="return-modal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    <h2><i class="fas fa-rotate-left"></i> Request Item Return</h2>
    <p class="modal-sub" id="modal-sub"></p>

    <div class="po-info" id="modal-po-info"></div>

    <div class="fg c2" style="margin-bottom:14px">
      <div class="field">
        <label>Quantity to Return *</label>
        <input type="number" id="ret-qty" min="1" value="1"/>
        <div id="qty-hint" style="font-size:.75rem;color:#9a9ab5;margin-top:4px;"></div>
      </div>
      <div class="field">
        <label>Reason *</label>
        <select id="ret-reason">
          <option value="defective">Defective / Not Working</option>
          <option value="wrong_item">Wrong Item Delivered</option>
          <option value="damaged_shipping">Damaged During Shipping</option>
          <option value="overdelivery">Over-delivery</option>
          <option value="other">Other</option>
        </select>
      </div>
    </div>
    <div class="field" style="margin-bottom:20px">
      <label>Description * <small style="color:#666">(min 5 chars — describe the issue)</small></label>
      <textarea id="ret-desc" placeholder="e.g. 3 units arrived with cracked casing and fail power-on test…"></textarea>
    </div>
    <div style="display:flex;gap:10px;">
      <button class="s-btn red" onclick="submitReturn()"><i class="fas fa-paper-plane"></i> Submit Return Request</button>
      <button class="s-btn" style="background:#444" onclick="closeModal()">Cancel</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API = '../PHP/buyer_returns.php';
const SYMBOLS = {PHP:'₱',USD:'$',EUR:'€',JPY:'¥',GBP:'£',SGD:'S$',CNY:'¥'};
let activePO = null;

let _tt;
function toast(msg, err=false){
  const t=document.getElementById('toast');
  t.textContent=msg; t.className='toast show'+(err?' err':'');
  clearTimeout(_tt); _tt=setTimeout(()=>t.className='toast',2800);
}
const esc = s => String(s??'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const sym = cur => SYMBOLS[(cur||'PHP').toUpperCase()] || cur+' ';

// ── TABS ──────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(b => b.addEventListener('click', () => {
  document.querySelectorAll('.tab-btn').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(x=>x.classList.remove('active'));
  b.classList.add('active');
  document.getElementById('tab-'+b.dataset.tab).classList.add('active');
  if(b.dataset.tab==='my-returns') loadMyReturns();
}));

// ── Load eligible POs ─────────────────────────────
async function loadEligiblePOs(){
  const wrap = document.getElementById('po-list-wrap');
  wrap.innerHTML = '<div style="text-align:center;padding:32px;color:#9a9ab5"><i class="fas fa-spinner fa-spin" style="font-size:1.4rem;display:block;margin-bottom:10px"></i> Loading eligible POs…</div>';

  let d;
  try {
    const res = await fetch(`${API}?action=eligible_pos`);
    d = await res.json();
  } catch(e) {
    wrap.innerHTML = `<div style="text-align:center;padding:40px;color:#f87171">
      <i class="fas fa-triangle-exclamation" style="font-size:2rem;display:block;margin-bottom:10px"></i>
      Could not load POs. Please refresh the page.
    </div>`;
    return;
  }

  if (!d.success) {
    wrap.innerHTML = `<div style="text-align:center;padding:40px;color:#f87171">
      <i class="fas fa-triangle-exclamation" style="font-size:2rem;display:block;margin-bottom:10px"></i>
      ${esc(d.message || 'Error loading POs.')}
    </div>`;
    return;
  }

  const pos = d.pos || [];

  if(!pos.length){
    wrap.innerHTML = `<div style="text-align:center;padding:48px 20px;color:#9a9ab5">
      <i class="fas fa-box-open" style="font-size:3rem;color:#333;display:block;margin-bottom:16px"></i>
      <p style="font-size:.95rem">No received POs eligible for return at the moment.</p>
      <p style="font-size:.83rem;margin-top:8px;color:#666">POs must be in <strong>Received</strong> status to request a return.</p>
    </div>`;
    return;
  }

  wrap.innerHTML = pos.map(po => {
    const s     = sym(po.currency);
    const total = parseFloat(po.total_amount);
    return `
    <div class="po-card">
      <div class="po-top">
        <div>
          <div class="po-num">${esc(po.po_number)}</div>
          <div class="po-mpn">${esc(po.mpn||'—')}</div>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-start">
          <span class="badge purple">${esc(po.category||'—')}</span>
          ${po.return_status==='rejected'?'<span class="badge red">Previously Rejected</span>':''}
        </div>
      </div>
      <div class="po-meta">
        <div class="po-meta-item"><i class="fas fa-building"></i>${esc(po.supplier_name)}</div>
        <div class="po-meta-item"><i class="fas fa-boxes-stacked"></i>Qty: <strong>${Number(po.quantity).toLocaleString()} units</strong></div>
        <div class="po-meta-item"><i class="fas fa-tag"></i><strong>${s}${Number(po.unit_price).toFixed(4)}</strong> / unit</div>
        <div class="po-meta-item"><i class="fas fa-receipt"></i>Total: <strong>${s}${total.toLocaleString('en-PH',{minimumFractionDigits:2})}</strong></div>
        ${po.delivery_date ? `<div class="po-meta-item"><i class="fas fa-calendar-check"></i>Delivered: <strong>${new Date(po.delivery_date).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})}</strong></div>` : ''}
        <div class="po-meta-item"><i class="fas fa-user"></i>${esc(po.requestor_name)}</div>
      </div>
      <button class="btn-return" onclick='openModal(${JSON.stringify({
        id: po.id, po_number: po.po_number, pr_number: po.pr_number,
        supplier_name: po.supplier_name, mpn: po.mpn||"", category: po.category||"",
        quantity: parseInt(po.quantity), unit_price: po.unit_price,
        currency: po.currency, total_amount: po.total_amount
      })})'>
        <i class="fas fa-rotate-left"></i> Request Return
      </button>
    </div>`;
  }).join('');
}

// ── Modal ─────────────────────────────────────────
function openModal(po){
  activePO = po;
  const s = sym(po.currency);
  document.getElementById('modal-sub').textContent = `Return request for PO ${po.po_number}`;
  document.getElementById('modal-po-info').innerHTML = `
    <div class="pi-item"><div class="pi-label">PO #</div><div class="pi-val accent">${esc(po.po_number)}</div></div>
    <div class="pi-item"><div class="pi-label">MPN</div><div class="pi-val">${esc(po.mpn||'—')}</div></div>
    <div class="pi-item"><div class="pi-label">Supplier</div><div class="pi-val">${esc(po.supplier_name)}</div></div>
    <div class="pi-item"><div class="pi-label">Qty Received</div><div class="pi-val">${Number(po.quantity).toLocaleString()} units</div></div>
    <div class="pi-item"><div class="pi-label">Unit Price</div><div class="pi-val">${s}${Number(po.unit_price).toFixed(4)}</div></div>
    <div class="pi-item"><div class="pi-label">Total</div><div class="pi-val">${s}${Number(po.total_amount).toLocaleString('en-PH',{minimumFractionDigits:2})}</div></div>
  `;
  document.getElementById('ret-qty').max   = po.quantity;
  document.getElementById('ret-qty').value = 1;
  document.getElementById('qty-hint').textContent = `Max: ${po.quantity} units`;
  document.getElementById('ret-reason').value = 'defective';
  document.getElementById('ret-desc').value   = '';
  document.getElementById('return-modal').classList.add('open');
}
function closeModal(){
  document.getElementById('return-modal').classList.remove('open');
  activePO = null;
}

async function submitReturn(){
  const qty    = parseInt(document.getElementById('ret-qty').value);
  const reason = document.getElementById('ret-reason').value;
  const desc   = document.getElementById('ret-desc').value.trim();

  if(!qty || qty < 1){ toast('Quantity must be at least 1', true); return; }
  if(qty > activePO.quantity){ toast(`Max returnable: ${activePO.quantity}`, true); return; }
  if(desc.length < 5){ toast('Description must be at least 5 characters', true); return; }

  const res = await fetch(`${API}?action=submit`,{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({po_id: activePO.id, quantity_returned: qty, reason, description: desc})
  });
  const d = await res.json();
  if(d.success){
    closeModal(); toast('Return request submitted');
    loadEligiblePOs();
    loadMyReturns();
    // switch to my-returns tab
    document.querySelector('[data-tab="my-returns"]').click();
  } else toast(d.message, true);
}

document.getElementById('return-modal').addEventListener('click', e=>{
  if(e.target.id==='return-modal') closeModal();
});

// ── My Returns ────────────────────────────────────
async function loadMyReturns(){
  const tbody = document.getElementById('returns-tbody');
  tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#9a9ab5"><i class="fas fa-spinner fa-spin"></i> Loading…</td></tr>`;

  let d;
  try {
    const res = await fetch(`${API}?action=list`);
    d = await res.json();
  } catch(e) {
    tbody.innerHTML = `<tr class="empty-row"><td colspan="8"><i class="fas fa-triangle-exclamation" style="color:#f87171;margin-right:6px"></i>Could not load returns. Please refresh.</td></tr>`;
    return;
  }

  const rows = d.returns || [];

  if(!rows.length){
    tbody.innerHTML = `<tr class="empty-row"><td colspan="8">
      <i class="fas fa-check-circle" style="font-size:2rem;color:#22c55e;display:block;margin:0 auto 10px"></i>
      No return requests submitted yet.
    </td></tr>`;
    return;
  }

  const REASON_LABELS = {
    defective:'Defective',wrong_item:'Wrong Item',
    damaged_shipping:'Damaged Shipping',overdelivery:'Over-delivery',other:'Other'
  };
  const STATUS_BADGES = {
    pending:'rs-pending',approved:'rs-approved',rejected:'rs-rejected',returned:'rs-returned'
  };

  tbody.innerHTML = rows.map(r => {
    const s       = sym(r.currency);
    const subDate = new Date(r.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
    const badge   = STATUS_BADGES[r.status] || 'gray';
    return `<tr>
      <td><strong style="color:#a78bfa">${esc(r.po_number)}</strong></td>
      <td>${esc(r.pr_number)}</td>
      <td>${esc(r.mpn||'—')}</td>
      <td>${Number(r.quantity_returned).toLocaleString()} units</td>
      <td>${REASON_LABELS[r.reason]||r.reason}</td>
      <td><span class="badge ${badge}">${r.status.charAt(0).toUpperCase()+r.status.slice(1)}</span></td>
      <td style="font-size:.82rem;color:#9a9ab5;max-width:180px">${r.admin_notes ? esc(r.admin_notes) : '—'}</td>
      <td>${subDate}</td>
    </tr>`;
  }).join('');
}

// Initialize both on page load
loadEligiblePOs();
loadMyReturns();
</script>
</body>
</html>
