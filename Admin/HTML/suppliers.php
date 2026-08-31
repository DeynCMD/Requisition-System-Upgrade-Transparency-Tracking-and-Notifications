<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header('Location: ../../Admin/HTML/ZE-Electronics.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Suppliers & Bidding — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../CSS/admin_style.css?v=<?= time() ?>">
  <style>
    .main-content { margin-left:260px; padding:36px 40px; }
    .page-title   { color:var(--green); font-size:2rem; margin-bottom:6px; }
    .page-sub     { color:#9a9ab5; margin-bottom:24px; font-size:.95rem; }
    .tabs { display:flex; gap:10px; margin-bottom:26px; flex-wrap:wrap; }
    .tab-btn {
      background:var(--card-bg); border:1px solid var(--border); color:#b8b8d4;
      padding:12px 22px; border-radius:12px; cursor:pointer; font-size:.95rem;
      font-weight:500; display:flex; align-items:center; gap:10px; transition:all .25s; font-family:inherit;
    }
    .tab-btn:hover { color:#fff; border-color:var(--green); }
    .tab-btn.active { background:#22c55e; color:#fff; border-color:#22c55e; }
    .tab-panel { display:none; }
    .tab-panel.active { display:block; animation:fade .2s ease; }
    @keyframes fade { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }
    .s-card { background:var(--card-bg); border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,.4); margin-bottom:26px; }
    .s-card h2 { font-size:1.1rem; color:var(--yellow); margin-bottom:20px; display:flex; align-items:center; gap:10px; }
    .fg { display:grid; gap:14px; }
    .fg.c4 { grid-template-columns:repeat(4,1fr); }
    .fg.c3 { grid-template-columns:repeat(3,1fr); }
    .fg.c2 { grid-template-columns:repeat(2,1fr); }
    @media(max-width:1100px){ .fg.c4{grid-template-columns:repeat(2,1fr);} }
    @media(max-width:700px){ .fg.c4,.fg.c2,.fg.c3{grid-template-columns:1fr;} }
    .field label { display:block; font-size:.8rem; color:#9a9ab5; margin-bottom:6px; }
    .field input,.field select,.field textarea {
      width:100%; background:#1e1e2e; border:1px solid var(--border); color:var(--text-light);
      padding:11px 13px; border-radius:10px; font-size:.93rem; font-family:inherit; outline:none; transition:border-color .2s;
    }
    .field input:focus,.field select:focus { border-color:var(--green); }
    .field.end { display:flex; align-items:flex-end; }
    .s-btn {
      background:#22c55e; color:#fff; border:none; padding:11px 20px; border-radius:12px;
      cursor:pointer; font-size:.93rem; font-weight:600; font-family:inherit;
      display:inline-flex; align-items:center; gap:8px; transition:.2s;
    }
    .s-btn:hover { background:#16a34a; }
    .s-btn:disabled { opacity:.45; cursor:not-allowed; }
    .s-btn.red  { background:#ef4444; } .s-btn.red:hover  { background:#dc2626; }
    .s-btn.blue { background:#3b82f6; } .s-btn.blue:hover { background:#2563eb; }
    .s-btn.sm   { padding:7px 13px; font-size:.82rem; border-radius:9px; }
    .tw { background:#fff; border-radius:12px; overflow-x:auto; border:1px solid #e5e7eb; }
    .tw table { width:100%; border-collapse:collapse; }
    .tw thead  { background:#f1f5f9; }
    .tw th { text-align:left; color:#64748b; font-weight:600; font-size:.72rem; text-transform:uppercase;
             letter-spacing:.4px; padding:13px 15px; border-bottom:1px solid #e5e7eb; }
    .tw td { padding:12px 15px; border-bottom:1px solid #e5e7eb; color:#1f2937; font-size:.9rem; }
    .tw tbody tr:last-child td { border-bottom:none; }
    .tw tbody tr:hover { background:#f8fafc; }
    .tw .empty { text-align:center; color:#94a3b8; padding:30px; font-style:italic; }
    .tw .dash  { color:#cbd5e1; }
    .badge { display:inline-flex; padding:4px 11px; border-radius:20px; font-size:.74rem; font-weight:600; }
    .badge.green  { background:#dcfce7; color:#15803d; }
    .badge.gray   { background:#f1f5f9; color:#64748b; }
    .badge.blue   { background:#dbeafe; color:#1d4ed8; }
    .badge.yellow { background:#fef9c3; color:#854d0e; }
    .badge.red    { background:#fee2e2; color:#991b1b; }
    .badge.purple { background:#ede9fe; color:#6d28d9; }
    .ib { background:#fff; border:1px solid #e5e7eb; color:#64748b; width:32px; height:32px;
          border-radius:9px; cursor:pointer; transition:.2s; display:inline-flex; align-items:center; justify-content:center; }
    .ib:hover { color:#111; border-color:#94a3b8; }
    .ib.del:hover { color:#fff; background:#ef4444; border-color:#ef4444; }
    .ra { display:flex; gap:6px; }
    .creds-box {
      background:#0d1f15; border:1px solid #22c55e44; border-radius:10px;
      padding:10px 14px; margin-top:6px; font-size:.82rem; color:#9a9ab5;
    }
    .creds-box strong { color:var(--green); }
    .portal-info {
      background: linear-gradient(135deg,#1a1a2e,#1e2a1e);
      border:1px solid #22c55e33; border-radius:14px; padding:16px 20px;
      margin-bottom:24px; display:flex; align-items:center; gap:16px;
    }
    .portal-info i { font-size:2rem; color:#22c55e; flex-shrink:0; }
    .portal-info .pi-title { font-weight:700; color:var(--text-light); margin-bottom:3px; }
    .portal-info .pi-sub   { font-size:.83rem; color:#9a9ab5; }
    .pr-list { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:14px; margin-bottom:24px; }
    .pr-card {
      background:#1e1e2e; border:2px solid var(--border); border-radius:14px;
      padding:16px 18px; cursor:pointer; transition:.2s;
    }
    .pr-card:hover { border-color:#60a5fa; }
    .pr-card.selected { border-color:var(--green); box-shadow:0 0 0 1px var(--green); }
    .pr-card.has-bids  { border-left:4px solid var(--green); }
    .pr-card.has-winner{ border-left:4px solid #a78bfa; }
    .pr-card.has-po    { border-left:4px solid #60a5fa; opacity:.8; }
    .pr-card .pr-num  { font-size:1rem; font-weight:700; color:var(--green); margin-bottom:4px; }
    .pr-card .pr-meta { font-size:.82rem; color:#9a9ab5; line-height:1.8; }
    .pr-card .bid-pill {
      display:inline-flex; align-items:center; gap:5px;
      font-size:.72rem; font-weight:700; padding:3px 9px; border-radius:20px; margin-top:8px;
    }
    .pr-card .bid-pill.no-bids  { background:#1e1e2e; border:1px solid #444; color:#666; }
    .pr-card .bid-pill.some-bids{ background:#0d1f15; border:1px solid #22c55e44; color:#4ade80; }
    .pr-card .bid-pill.winner   { background:#1a1a2e; border:1px solid #a78bfa44; color:#a78bfa; }
    .pr-card .bid-pill.po-done  { background:#1a2035; border:1px solid #60a5fa44; color:#60a5fa; }
    .bid-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; margin-bottom:20px; }
    .bid-card {
      background:#1e1e2e; border:2px solid var(--border); border-radius:14px; padding:18px 20px;
      position:relative; transition:.2s;
    }
    .bid-card.winner { border-color:#22c55e; background:#0d1f15; }
    .bid-card.cheapest::after {
      content:'BEST PRICE'; position:absolute; top:-1px; right:12px;
      background:#22c55e; color:#fff; font-size:.65rem; font-weight:800;
      padding:3px 10px; border-radius:0 0 8px 8px; letter-spacing:.5px;
    }
    .bid-card .b-supplier { font-size:1rem; font-weight:700; color:var(--text-light); margin-bottom:10px; }
    .bid-card .b-price    { font-size:1.7rem; font-weight:800; color:#4ade80; margin-bottom:4px; }
    .bid-card .b-total    { font-size:.82rem; color:#9a9ab5; margin-bottom:10px; }
    .bid-card .b-delivery { font-size:.82rem; color:#b8b8d4; margin-bottom:12px; }
    .bid-card .b-notes    { font-size:.78rem; color:#9a9ab5; font-style:italic; margin-bottom:14px; }
    .win-btn {
      background:#22c55e; color:#fff; border:none; padding:9px 16px; border-radius:10px;
      cursor:pointer; font-weight:700; font-size:.85rem; font-family:inherit;
      display:inline-flex; align-items:center; gap:6px; transition:.2s; flex:1; justify-content:center;
    }
    .win-btn:hover { background:#16a34a; box-shadow:0 0 10px rgba(34,197,94,.4); }
    .del-bid-btn {
      background:#fff; border:1px solid #fee2e2; color:#ef4444; padding:9px 12px;
      border-radius:10px; cursor:pointer; font-family:inherit; transition:.2s;
    }
    .del-bid-btn:hover { background:#ef4444; color:#fff; border-color:#ef4444; }
    .pr-detail {
      background:linear-gradient(135deg,#1a1a2e,#1e2a1e);
      border:1px solid #22c55e44; border-radius:14px; padding:18px 22px;
      margin-bottom:22px; display:flex; gap:24px; flex-wrap:wrap; align-items:center;
    }
    .pr-detail .pd-label { font-size:.75rem; color:#9a9ab5; text-transform:uppercase; letter-spacing:.4px; margin-bottom:3px; }
    .pr-detail .pd-val   { font-size:.95rem; font-weight:700; color:var(--text-light); }
    .pr-detail .pd-val.big { font-size:1.3rem; color:#4ade80; }
    .bid-empty { text-align:center; color:#9a9ab5; padding:32px; font-size:.9rem; }
    .bid-empty i { font-size:2rem; margin-bottom:10px; display:block; color:#444; }
    .po-issued    { background:#dbeafe; color:#1d4ed8; }
    .po-received  { background:#dcfce7; color:#15803d; }
    .po-cancelled { background:#fee2e2; color:#991b1b; }
    .toast { position:fixed; bottom:24px; right:24px; background:#22c55e; color:#fff;
             padding:13px 22px; border-radius:12px; font-weight:600; font-size:.93rem;
             box-shadow:0 10px 30px rgba(0,0,0,.5); opacity:0; transform:translateY(10px);
             transition:.25s; pointer-events:none; z-index:9999; }
    .toast.show { opacity:1; transform:none; }
    .toast.err  { background:#ef4444; }
    .pw-wrap { position:relative; }
    .pw-wrap input { padding-right:40px; }
    .pw-toggle { position:absolute; right:12px; top:50%; transform:translateY(-50%);
                 background:none; border:none; color:#666; cursor:pointer; font-size:.9rem; }
    .pw-toggle:hover { color:var(--green); }
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
      <li><a href="suppliers.php" class="active"><i class="fas fa-truck-field"></i> Suppliers</a></li>
      <li><a href="admin_price_prediction.php"><i class="fas fa-chart-line"></i> Price Prediction</a></li>
      <li><a href="admin_returns.php"><i class="fas fa-rotate-left"></i> Item Returns</a></li>
      <li><a href="HistoryZE.php"><i class="fas fa-history"></i> History</a></li>
    </ul></nav>
    <a href="../PHP/logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Supplier Management</h1>

    <div class="tabs">
      <button class="tab-btn active" data-tab="suppliers"><i class="fas fa-building"></i> Suppliers</button>
      <button class="tab-btn" data-tab="bidding"><i class="fas fa-gavel"></i> Supplier Bids</button>
      <button class="tab-btn" data-tab="pos"><i class="fas fa-file-invoice"></i> Purchase Orders</button>
    </div>

    <!-- TAB 1 — SUPPLIERS -->
    <div class="tab-panel active" id="tab-suppliers">

      <div class="s-card">
        <h2><i class="fas fa-plus-circle"></i> Register New Supplier</h2>
        <div class="fg c4">
          <div class="field"><label>Supplier Name *</label><input id="s-name" placeholder="e.g. TechTrend Inc."/></div>
          <div class="field"><label>Contact Person</label><input id="s-contact" placeholder="Maria Santos"/></div>
          <div class="field"><label>Email</label><input id="s-email" type="email" placeholder="sales@supplier.com"/></div>
          <div class="field"><label>Phone</label><input id="s-phone" placeholder="+63 917 000 0000"/></div>
        </div>
        <div class="fg c4" style="margin-top:14px">
          <div class="field" style="grid-column:span 2"><label>Address</label><input id="s-address" placeholder="City / Country"/></div>
          <div class="field">
            <label>Portal Username *</label>
            <input id="s-username" placeholder="e.g. techtrend_supplier"/>
          </div>
          <div class="field">
            <label>Portal Password * <small style="color:#666">(min 6 chars)</small></label>
            <div class="pw-wrap">
              <input id="s-password" type="password" placeholder="Set initial password"/>
              <button type="button" class="pw-toggle" onclick="togglePw('s-password',this)"><i class="fas fa-eye"></i></button>
            </div>
          </div>
        </div>
        <div style="margin-top:16px">
          <button class="s-btn" onclick="addSupplier()"><i class="fas fa-save"></i> Register Supplier</button>
        </div>
      </div>

      <div class="s-card">
        <h2><i class="fas fa-list"></i> Registered Suppliers <span id="s-count" style="font-size:.8rem;color:#9a9ab5;font-weight:400;margin-left:6px;"></span></h2>
        <div class="tw"><table>
          <thead><tr><th>Name</th><th>Contact</th><th>Email</th><th>Phone</th><th>Address</th><th>Portal Username</th><th>Status</th><th></th></tr></thead>
          <tbody id="supplier-rows"><tr><td colspan="8" class="empty">Loading…</td></tr></tbody>
        </table></div>
      </div>
    </div>

    <!-- TAB 2 — BIDDING -->
    <div class="tab-panel" id="tab-bidding">

      <div class="s-card">
        <h2><i class="fas fa-folder-open"></i> Approved Requests — Select one to review bids</h2>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;font-size:.78rem;">
          <span style="display:flex;align-items:center;gap:5px;color:#666"><span style="width:10px;height:10px;background:#22c55e;border-radius:50%;display:inline-block"></span>Has bids</span>
          <span style="display:flex;align-items:center;gap:5px;color:#666"><span style="width:10px;height:10px;background:#a78bfa;border-radius:50%;display:inline-block"></span>Winner selected</span>
          <span style="display:flex;align-items:center;gap:5px;color:#666"><span style="width:10px;height:10px;background:#60a5fa;border-radius:50%;display:inline-block"></span>PO issued</span>
          <span style="display:flex;align-items:center;gap:5px;color:#666"><span style="width:10px;height:10px;background:#444;border-radius:50%;display:inline-block"></span>No bids yet</span>
        </div>
        <div id="pr-list-wrap">
          <div class="bid-empty"><i class="fas fa-spinner fa-spin"></i> Loading approved requests…</div>
        </div>
      </div>

      <div id="bid-workspace" style="display:none">
        <div class="pr-detail" id="pr-detail-banner"></div>
        <div class="s-card">
          <h2 style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <span><i class="fas fa-scale-balanced"></i> Bids Received
              <span id="bid-count-pill" style="font-size:.78rem;color:#9a9ab5;font-weight:400;margin-left:8px;"></span>
            </span>
            <button class="s-btn" style="padding:7px 14px;font-size:.82rem;" onclick="loadBids()">
              <i class="fas fa-rotate-right"></i> Refresh Bids
            </button>
          </h2>
          <p style="color:#9a9ab5;font-size:.85rem;margin-bottom:18px;">
            Bids are sorted cheapest first. Click <strong style="color:#4ade80">Choose Winner</strong> on the best bid — a Purchase Order is generated automatically.
          </p>
          <div id="bid-cards-wrap">
            <div class="bid-empty"><i class="fas fa-inbox"></i> No bids yet for this request.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3 — PURCHASE ORDERS -->
    <div class="tab-panel" id="tab-pos">
      <div class="s-card">
        <h2><i class="fas fa-file-invoice"></i> Purchase Orders</h2>
        <div class="tw">
          <table>
            <thead><tr><th>PO #</th><th>PR #</th><th>MPN</th><th>Supplier</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Currency</th><th>Delivery</th><th>Status</th></tr></thead>
            <tbody id="po-rows"><tr><td colspan="10" class="empty">Loading…</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE  = '../PHP/';
const BIDS  = '../PHP/supplier_bids.php';
let selectedPR = null;

// ── toast ────────────────────────────────────────
let _tt;
function toast(msg, err=false){
  const t=document.getElementById('toast');
  t.textContent=msg; t.className='toast show'+(err?' err':'');
  clearTimeout(_tt); _tt=setTimeout(()=>t.className='toast',2600);
}

const esc = s=>String(s??'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const SYMBOLS = {PHP:'₱',USD:'$',EUR:'€',JPY:'¥',GBP:'£',SGD:'S$',CNY:'¥'};
const money = (n, cur='PHP') => {
  const sym = SYMBOLS[(cur||'PHP').toUpperCase()] || (cur+' ');
  return sym + Number(n).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:4});
};
const phpEquiv = (n, cur) => {
  const PHP_RATES = {PHP:1,USD:58.50,EUR:64.30,JPY:0.39,GBP:74.20,SGD:43.50,CNY:8.10};
  if(!cur||cur==='PHP') return '';
  const php = n * (PHP_RATES[cur.toUpperCase()]||1);
  return ` <span style="font-size:.75rem;color:#9a9ab5">≈ ₱${php.toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2})}</span>`;
};

function togglePw(id, btn){
  const el = document.getElementById(id);
  if(el.type==='password'){ el.type='text'; btn.innerHTML='<i class="fas fa-eye-slash"></i>'; }
  else { el.type='password'; btn.innerHTML='<i class="fas fa-eye"></i>'; }
}

// ── TABS ─────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(b=>b.addEventListener('click',()=>{
  document.querySelectorAll('.tab-btn').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(x=>x.classList.remove('active'));
  b.classList.add('active');
  document.getElementById('tab-'+b.dataset.tab).classList.add('active');
  if(b.dataset.tab==='bidding') loadPRs();
  if(b.dataset.tab==='pos')     loadPOs();
}));

// Open a specific tab programmatically (e.g. from URL hash)
function openTab(name) {
  const btn = document.querySelector(`.tab-btn[data-tab="${name}"]`);
  if (btn) btn.click();
}

// Check URL hash on load
if(location.hash === '#bidding') openTab('bidding');
if(location.hash === '#pos')     openTab('pos');

// ══════════════════════════════════════════════
//  TAB 1 — SUPPLIERS
// ══════════════════════════════════════════════
async function loadSuppliers(){
  const res  = await fetch(BASE+'suppliers.php?action=list');
  const d    = await res.json();
  const sups = d.suppliers || [];
  document.getElementById('s-count').textContent = sups.length + ' registered';
  const tb = document.getElementById('supplier-rows');
  if(!sups.length){
    tb.innerHTML=`<tr><td colspan="8" class="empty">No suppliers yet.</td></tr>`; return;
  }
  tb.innerHTML = sups.map(s=>`
    <tr>
      <td><strong>${esc(s.name)}</strong></td>
      <td>${esc(s.contact)||'<span class="dash">—</span>'}</td>
      <td>${esc(s.email)||'<span class="dash">—</span>'}</td>
      <td>${esc(s.phone)||'<span class="dash">—</span>'}</td>
      <td>${esc(s.address)||'<span class="dash">—</span>'}</td>
      <td>
        ${s.username
          ? `<code style="background:#f1f5f9;padding:3px 8px;border-radius:6px;font-size:.82rem;color:#1d4ed8">${esc(s.username)}</code>`
          : '<span class="dash">—</span>'}
      </td>
      <td><span class="badge ${s.active=='1'?'green':'gray'}">${s.active=='1'?'Active':'Inactive'}</span></td>
      <td><div class="ra">
        <button class="ib" title="Toggle active/inactive" onclick="toggleSupplier(${s.id})"><i class="fas fa-power-off"></i></button>
        <button class="ib" title="Reset portal password" onclick="resetPassword(${s.id},'${esc(s.name)}')"><i class="fas fa-key"></i></button>
        <button class="ib del" title="Delete supplier" onclick="deleteSupplier(${s.id},'${esc(s.name)}')"><i class="fas fa-trash"></i></button>
      </div></td>
    </tr>`).join('');
}

async function addSupplier(){
  const name     = document.getElementById('s-name').value.trim();
  const username = document.getElementById('s-username').value.trim();
  const password = document.getElementById('s-password').value.trim();

  if(!name)     { toast('Supplier name is required', true); return; }
  if(!username) { toast('Portal username is required', true); return; }
  if(!password) { toast('Portal password is required', true); return; }

  const body = {
    name, username, password,
    contact: document.getElementById('s-contact').value.trim(),
    email:   document.getElementById('s-email').value.trim(),
    phone:   document.getElementById('s-phone').value.trim(),
    address: document.getElementById('s-address').value.trim()
  };
  const res = await fetch(BASE+'suppliers.php?action=add',{method:'POST',body:JSON.stringify(body)});
  const d   = await res.json();
  if(d.success){
    ['s-name','s-contact','s-email','s-phone','s-address','s-username','s-password'].forEach(x=>document.getElementById(x).value='');
    await loadSuppliers();
    toast('Supplier registered with portal access');
  } else toast(d.message, true);
}

async function toggleSupplier(id){
  await fetch(BASE+'suppliers.php?action=toggle',{method:'POST',body:JSON.stringify({id})});
  await loadSuppliers(); toast('Status updated');
}

async function resetPassword(id, name){
  const r = await Swal.fire({
    title:`Reset password for "${name}"?`,
    input:'password',
    inputLabel:'New password (min 6 characters)',
    inputPlaceholder:'Enter new password',
    inputAttributes:{ minlength:'6', autocomplete:'new-password' },
    showCancelButton:true,
    confirmButtonColor:'#22c55e',
    confirmButtonText:'Reset Password',
    inputValidator: v => { if(!v || v.length < 6) return 'Password must be at least 6 characters'; }
  });
  if(!r.isConfirmed || !r.value) return;
  const res = await fetch(BASE+'suppliers.php?action=reset_password',{
    method:'POST', body:JSON.stringify({id, password: r.value})
  });
  const d = await res.json();
  if(d.success) toast('Password reset successfully');
  else toast(d.message, true);
}

async function deleteSupplier(id, name){
  const r = await Swal.fire({title:`Remove "${name}"?`,text:'Cannot be undone.',icon:'warning',
    showCancelButton:true,confirmButtonColor:'#ef4444',confirmButtonText:'Delete'});
  if(!r.isConfirmed) return;
  const res = await fetch(BASE+'suppliers.php?action=delete',{method:'POST',body:JSON.stringify({id})});
  const d   = await res.json();
  if(d.success){ await loadSuppliers(); toast('Supplier removed'); }
  else toast(d.message, true);
}

// ══════════════════════════════════════════════
//  TAB 2 — BIDDING (read-only review)
// ══════════════════════════════════════════════
async function loadPRs(){
  const wrap = document.getElementById('pr-list-wrap');
  wrap.innerHTML = '<div class="bid-empty"><i class="fas fa-spinner fa-spin"></i> Loading…</div>';

  let d;
  try {
    const res = await fetch(BIDS+'?action=requests');
    d = await res.json();
  } catch(e) {
    wrap.innerHTML = `<div class="bid-empty" style="color:var(--red)"><i class="fas fa-triangle-exclamation"></i> Failed to load requests. Check console.</div>`;
    console.error('loadPRs error:', e); return;
  }

  if(!d.success) {
    wrap.innerHTML = `<div class="bid-empty" style="color:var(--red)"><i class="fas fa-lock"></i> ${esc(d.message||'Unauthorized')}</div>`;
    return;
  }

  const prs = d.requests || [];
  if(!prs.length){
    wrap.innerHTML = `<div class="bid-empty"><i class="fas fa-inbox"></i>No approved purchase requests available for bidding yet.</div>`;
    document.getElementById('bid-workspace').style.display='none';
    return;
  }

  // Store PRs in a map for quick lookup by id
  window._prMap = {};
  prs.forEach(pr => { window._prMap[pr.id] = pr; });

  wrap.innerHTML = `<div class="pr-list">${prs.map(pr=>{
    const bidCount  = parseInt(pr.bid_count  || 0);
    const hasWinner = parseInt(pr.has_winner || 0) > 0;
    const hasPO     = parseInt(pr.has_po     || 0) > 0;

    // Left-border class
    let cardClass = selectedPR?.id==pr.id ? ' selected' : '';
    if(hasPO)         cardClass += ' has-po';
    else if(hasWinner)cardClass += ' has-winner';
    else if(bidCount) cardClass += ' has-bids';

    // Bid pill
    let pill = '';
    if(hasPO){
      pill = `<div class="bid-pill po-done"><i class="fas fa-file-invoice"></i> PO Issued</div>`;
    } else if(hasWinner){
      pill = `<div class="bid-pill winner"><i class="fas fa-trophy"></i> Winner Selected</div>`;
    } else if(bidCount > 0){
      pill = `<div class="bid-pill some-bids"><i class="fas fa-gavel"></i> ${bidCount} bid${bidCount>1?'s':''} received</div>`;
    } else {
      pill = `<div class="bid-pill no-bids"><i class="fas fa-clock"></i> No bids yet</div>`;
    }

    // Urgency
    const urgency = pr.urgency || '';
    const urgHtml = urgency
      ? `<span style="font-size:.65rem;font-weight:800;text-transform:uppercase;padding:2px 7px;border-radius:20px;margin-left:6px;
                      background:${urgency==='High'?'#fee2e2':urgency==='Low'?'#dcfce7':'#f1f5f9'};
                      color:${urgency==='High'?'#991b1b':urgency==='Low'?'#15803d':'#64748b'}">${urgency}</span>`
      : '';

    return `
    <div class="pr-card${cardClass}"
         data-prid="${pr.id}"
         onclick="selectPR(this, ${Number(pr.id)})">
      <div class="pr-num">${esc(pr.pr_number)}${urgHtml}</div>
      <div class="pr-meta">
        <span><i class="fas fa-microchip" style="color:#a78bfa;width:14px"></i> ${esc(pr.mpn||'—')}</span><br>
        <span><i class="fas fa-tag"        style="color:#fbbf24;width:14px"></i> ${esc(pr.category||'—')}</span><br>
        <span><i class="fas fa-boxes-stacked" style="color:#60a5fa;width:14px"></i> ${Number(pr.quantity).toLocaleString()} units</span><br>
        <span><i class="fas fa-user"       style="color:#9a9ab5;width:14px"></i> ${esc(pr.requestor_name)}</span>
      </div>
      ${pill}
    </div>`;
  }).join('')}</div>`;
}

async function selectPR(cardEl, prId){
  selectedPR = window._prMap[prId];
  if(!selectedPR){ toast('PR data not found', true); return; }

  document.querySelectorAll('.pr-card').forEach(c=>c.classList.remove('selected'));
  cardEl.classList.add('selected');

  const ws  = document.getElementById('bid-workspace');
  ws.style.display = 'block';
  const cur = selectedPR.currency || 'PHP';

  document.getElementById('pr-detail-banner').innerHTML = `
    <div><div class="pd-label">PR Number</div><div class="pd-val big">${esc(selectedPR.pr_number)}</div></div>
    <div><div class="pd-label">MPN</div><div class="pd-val">${esc(selectedPR.mpn||'—')}</div></div>
    <div><div class="pd-label">Category</div><div class="pd-val">${esc(selectedPR.category||'—')}</div></div>
    <div><div class="pd-label">Qty Requested</div><div class="pd-val">${Number(selectedPR.quantity).toLocaleString()} units</div></div>
    <div><div class="pd-label">Currency</div><div class="pd-val">${esc(cur)} (${SYMBOLS[cur]||cur})</div></div>
    <div><div class="pd-label">Requestor</div><div class="pd-val">${esc(selectedPR.requestor_name)}</div></div>`;

  ws.scrollIntoView({behavior:'smooth', block:'start'});
  await loadBids();
}

async function loadBids(){
  if(!selectedPR) return;
  const wrap   = document.getElementById('bid-cards-wrap');
  const prId   = Number(selectedPR.id);  // force integer
  wrap.innerHTML = '<div class="bid-empty"><i class="fas fa-spinner fa-spin"></i> Loading bids…</div>';

  let d, bids = [];
  try {
    const res = await fetch(`${BIDS}?action=list&pr_id=${prId}`);
    const text = await res.text();
    try { d = JSON.parse(text); }
    catch(e) { console.error('loadBids — invalid JSON:', text); toast('Server error loading bids', true); return; }
  } catch(e) {
    console.error('loadBids fetch error:', e);
    toast('Network error loading bids', true); return;
  }

  if(!d.success) {
    wrap.innerHTML = `<div class="bid-empty" style="color:var(--red)">${esc(d.message||'Error')}</div>`;
    return;
  }
  bids = d.bids || [];
  document.getElementById('bid-count-pill').textContent = bids.length ? `${bids.length} bid${bids.length>1?'s':''} received` : 'No bids yet';

  const poRes     = await fetch(`${BIDS}?action=pos`);
  const poData    = await poRes.json();
  const existingPO = (poData.pos||[]).find(p=>p.pr_id==selectedPR.id);

  if(!bids.length){
    wrap.innerHTML = `
      <div style="text-align:center;padding:40px 20px;">
        <div style="width:56px;height:56px;border-radius:50%;background:#1e1e2e;border:2px solid #444;
                    display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.6rem;color:#444">
          <i class="fas fa-gavel"></i>
        </div>
        <div style="font-size:1rem;font-weight:700;color:#9a9ab5;margin-bottom:6px">No bids received yet</div>
        <div style="font-size:.83rem;color:#555">Suppliers will submit bids from their portal.<br>Bids appear here automatically — refresh to check for new ones.</div>
        <button onclick="loadBids()" style="margin-top:16px;background:#1e1e2e;border:1px solid #444;color:#9a9ab5;
                padding:8px 18px;border-radius:10px;cursor:pointer;font-size:.85rem;font-family:inherit;transition:.2s"
                onmouseover="this.style.borderColor='#22c55e';this.style.color='#4ade80'"
                onmouseout="this.style.borderColor='#444';this.style.color='#9a9ab5'">
          <i class="fas fa-rotate-right"></i> Refresh
        </button>
      </div>`;
    return;
  }

  const sorted     = [...bids].sort((a,b)=>a.unit_price-b.unit_price);
  const cheapestId = sorted[0].id;
  const qty        = Number(selectedPR.quantity);
  const cur        = selectedPR.currency || 'PHP';

  wrap.innerHTML = `<div class="bid-grid">` + sorted.map(b=>{
    const total      = parseFloat(b.unit_price) * qty;
    const isCheapest = b.id==cheapestId;
    const isWinner   = b.status === 'selected';
    const isRejected = b.status === 'rejected';
    const phpEq      = phpEquiv(parseFloat(b.unit_price), cur);
    const phpTotEq   = phpEquiv(total, cur);
    return `
    <div class="bid-card${isCheapest&&!isWinner?' cheapest':''}${isWinner?' winner':''}">
      <div class="b-supplier"><i class="fas fa-building" style="color:#9a9ab5;margin-right:6px"></i>${esc(b.supplier_name)}</div>
      ${b.supplier_email ? `<div style="font-size:.75rem;color:#9a9ab5;margin-bottom:10px">${esc(b.supplier_email)}</div>` : ''}
      <div class="b-price">${money(b.unit_price,cur)}${phpEq}<span style="font-size:.75rem;color:#9a9ab5;font-weight:400"> / unit</span></div>
      <div class="b-total">Total for ${qty.toLocaleString()} units: <strong>${money(total,cur)}</strong>${phpTotEq}</div>
      <div class="b-delivery"><i class="fas fa-calendar-check" style="color:#60a5fa;margin-right:5px"></i>Delivery: ${b.delivery_date||'—'}</div>
      ${b.notes?`<div class="b-notes"><i class="fas fa-quote-left" style="margin-right:4px"></i>${esc(b.notes)}</div>`:''}
      ${isWinner
        ? `<div style="color:#4ade80;font-weight:700;font-size:.85rem"><i class="fas fa-trophy"></i> Winner — PO Generated</div>`
        : isRejected
          ? `<div style="color:#9a9ab5;font-size:.82rem"><i class="fas fa-xmark-circle"></i> Not selected</div>`
          : existingPO
            ? `<div style="color:#9a9ab5;font-size:.82rem">PO already issued for this PR</div>`
            : `<div style="display:flex;gap:8px">
                 <button class="win-btn" onclick="chooseWinner(${b.id},'${esc(b.supplier_name)}',${b.unit_price},'${b.delivery_date}')">
                   <i class="fas fa-trophy"></i> Choose Winner
                 </button>
                 <button class="del-bid-btn" title="Remove bid" onclick="deleteBid(${b.id})">
                   <i class="fas fa-trash"></i>
                 </button>
               </div>`
      }
    </div>`;
  }).join('') + `</div>`;
}

async function deleteBid(bidId){
  const r = await Swal.fire({title:'Remove this bid?',icon:'warning',showCancelButton:true,
    confirmButtonColor:'#ef4444',confirmButtonText:'Remove'});
  if(!r.isConfirmed) return;
  const res = await fetch(`${BIDS}?action=delete`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:bidId})});
  const d   = await res.json();
  if(d.success){ await loadBids(); toast('Bid removed'); }
  else toast(d.message, true);
}

async function chooseWinner(bidId, supplierName, unitPrice, deliveryDate){
  const qty   = Number(selectedPR.quantity);
  const total = (unitPrice * qty).toFixed(2);
  const sym   = selectedPR.currency==='PHP'?'₱':'$';
  const r = await Swal.fire({
    title:`Choose ${supplierName}?`,
    html:`<div style="text-align:left;font-size:.93rem;line-height:2">
      <strong>Supplier:</strong> ${supplierName}<br>
      <strong>Unit Price:</strong> ${sym}${unitPrice}<br>
      <strong>Qty:</strong> ${qty.toLocaleString()}<br>
      <strong>Total:</strong> ${sym}${Number(total).toLocaleString()}<br>
      <strong>Delivery:</strong> ${deliveryDate}<br><br>
      <span style="color:#fbbf24"><i class="fas fa-triangle-exclamation"></i>
      The bid will be marked as selected. The <strong>Buyer</strong> will generate the Purchase Order.</span>
    </div>`,
    icon:'question', showCancelButton:true,
    confirmButtonColor:'#22c55e', confirmButtonText:'<i class="fas fa-trophy"></i> Confirm Winner'
  });
  if(!r.isConfirmed) return;
  const res = await fetch(`${BIDS}?action=select_winner`,{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({bid_id: bidId})
  });
  const d = await res.json();
  if(d.success){
    await Swal.fire({icon:'success', title:'Winner Selected!',
      html:`<strong>${supplierName}</strong> has been selected as the winning supplier.<br><small style="color:#9a9ab5">The Buyer will now generate the Purchase Order.</small>`,
      confirmButtonColor:'#22c55e'});
    await loadBids(); await loadPRs();
  } else toast(d.message, true);
}

// ══════════════════════════════════════════════
//  TAB 3 — PURCHASE ORDERS
// ══════════════════════════════════════════════
async function loadPOs(){
  const tb = document.getElementById('po-rows');
  tb.innerHTML = `<tr><td colspan="10" class="empty"><i class="fas fa-spinner fa-spin"></i> Loading…</td></tr>`;
  const res = await fetch(`${BIDS}?action=pos`);
  const d   = await res.json();
  const pos = d.pos || [];
  if(!pos.length){
    tb.innerHTML=`<tr><td colspan="10" class="empty">No purchase orders yet.</td></tr>`; return;
  }
  tb.innerHTML = pos.map(p=>`
    <tr>
      <td><strong>${esc(p.po_number)}</strong></td>
      <td>${esc(p.pr_number)}</td>
      <td>${esc(p.mpn||'—')}</td>
      <td>${esc(p.supplier_name)}</td>
      <td>${Number(p.quantity).toLocaleString()}</td>
      <td>${money(p.unit_price,p.currency)}</td>
      <td>${money(p.total_amount,p.currency)}</td>
      <td>${esc(p.currency)}</td>
      <td>${p.delivery_date||'—'}</td>
      <td><span class="badge po-${(p.status||'Issued').toLowerCase()}">${esc(p.status||'Issued')}</span></td>
    </tr>`).join('');
}

loadSuppliers();
// Pre-load PRs in background so bidding tab is instant
loadPRs();
</script>
</body>
</html>
