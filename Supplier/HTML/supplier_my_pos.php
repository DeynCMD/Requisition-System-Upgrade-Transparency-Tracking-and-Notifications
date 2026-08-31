<?php
require_once '../PHP/supplier_guard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Purchase Orders — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="../CSS/supplier_style.css?v=<?= time() ?>">
  <script src="../../Admin/JS/currency.js"></script>
</head>
<body>
<div class="container">

  <aside class="sidebar">
    <div class="profile">
      <img src="../../Admin/Assets/Avatar.jpg" alt="Supplier"/>
      <span class="role">SUPPLIER</span>
    </div>
    <nav class="nav-menu"><ul>
      <li><a href="supplier_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="supplier_open_requests.php"><i class="fas fa-folder-open"></i> Open Requests</a></li>
      <li><a href="supplier_my_bids.php"><i class="fas fa-gavel"></i> My Bids</a></li>
      <li><a href="supplier_my_pos.php" class="active"><i class="fas fa-file-invoice"></i> Purchase Orders</a></li>
    </ul></nav>
    <a href="../PHP/supplier_logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">My Purchase Orders</h1>

    <div id="po-stats" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:28px;"></div>

    <div class="s-card">
      <h2><i class="fas fa-file-invoice"></i> Purchase Orders</h2>
      <table class="dark-table" style="width:100%">
        <thead>
          <tr>
            <th>PO #</th>
            <th>PR #</th>
            <th>MPN</th>
            <th>Category</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total Amount</th>
            <th>Delivery Date</th>
            <th>Status</th>
            <th>Issued On</th>
          </tr>
        </thead>
        <tbody id="po-tbody">
          <tr class="empty-row"><td colspan="10">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </main>
</div>

<div class="toast" id="toast"></div>
<script>
const API = '../PHP/supplier_api.php';
const esc = s => String(s??'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

let _tt;
function toast(msg,err=false){
  const t=document.getElementById('toast');
  t.textContent=msg; t.className='toast show'+(err?' err':'');
  clearTimeout(_tt); _tt=setTimeout(()=>t.className='toast',2800);
}

async function loadPOs(){
  const res  = await fetch(`${API}?action=my_pos`);
  const data = await res.json();
  const pos  = data.pos || [];

  const total     = pos.length;
  const issued    = pos.filter(p=>p.status==='Issued').length;
  const received  = pos.filter(p=>p.status==='Received').length;
  const cancelled = pos.filter(p=>p.status==='Cancelled').length;
  const totalValPHP = pos.filter(p=>p.status!=='Cancelled')
                         .reduce((s,p)=>s + Currency.toPHP(parseFloat(p.total_amount), p.currency), 0);

  document.getElementById('po-stats').innerHTML = `
    <div class="stat-chip"><div class="sc-label">Total POs</div><div class="sc-val">${total}</div></div>
    <div class="stat-chip" style="border-color:#3b82f644;background:var(--card-bg)"><div class="sc-label">Issued</div><div class="sc-val" style="color:var(--blue)">${issued}</div></div>
    <div class="stat-chip" style="border-color:#22c55e44;background:var(--card-bg)"><div class="sc-label">Received</div><div class="sc-val" style="color:var(--green)">${received}</div></div>
    <div class="stat-chip" style="border-color:#f8717144;background:var(--card-bg)"><div class="sc-label">Cancelled</div><div class="sc-val" style="color:var(--red)">${cancelled}</div></div>
    <div class="stat-chip" style="border-color:#a78bfa44;background:var(--card-bg)"><div class="sc-label">Total Value (PHP)</div><div class="sc-val" style="color:var(--purple)">₱${totalValPHP.toLocaleString('en-PH',{minimumFractionDigits:2})}</div></div>
  `;

  const tbody = document.getElementById('po-tbody');
  if(!pos.length){
    tbody.innerHTML=`<tr class="empty-row"><td colspan="10">No purchase orders yet. Win bids to receive POs.</td></tr>`;
    return;
  }

  tbody.innerHTML = pos.map(po => {
    const sym      = Currency.symbol(po.currency);
    const unitP    = parseFloat(po.unit_price);
    const totalAmt = parseFloat(po.total_amount);
    const badgeMap = {Issued:'blue', Received:'green', Cancelled:'red'};
    const badge    = badgeMap[po.status] || 'gray';

    const overdue = po.status==='Issued' && po.delivery_date && new Date(po.delivery_date)<new Date();
    const delDate = po.delivery_date
      ? new Date(po.delivery_date).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'})
      : '—';
    const issuedOn = new Date(po.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});

    const phpUnit = po.currency!=='PHP'
      ? `<div class="php-equiv-block">≈ ₱${Currency.toPHP(unitP, po.currency).toLocaleString('en-PH',{minimumFractionDigits:4,maximumFractionDigits:4})}</div>` : '';
    const phpTot  = po.currency!=='PHP'
      ? `<div class="php-equiv-block">≈ ₱${Currency.toPHP(totalAmt, po.currency).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2})}</div>` : '';

    return `<tr ${overdue?'style="background:rgba(248,113,113,.06)"':''}>
      <td><strong style="color:var(--purple)">${esc(po.po_number)}</strong></td>
      <td style="color:var(--green)">${esc(po.pr_number)}</td>
      <td>${esc(po.mpn||'—')}</td>
      <td>${esc(po.category||'—')}</td>
      <td>${Number(po.quantity).toLocaleString()} <small style="color:var(--gray)">units</small></td>
      <td>${sym}${unitP.toFixed(4)}${phpUnit}</td>
      <td><strong>${sym}${totalAmt.toLocaleString('en-PH',{minimumFractionDigits:2})}${phpTot}</strong></td>
      <td ${overdue?'style="color:var(--red);font-weight:700"':''}>${delDate}${overdue?' <small>(overdue)</small>':''}</td>
      <td><span class="badge ${badge}">${esc(po.status)}</span></td>
      <td>${issuedOn}</td>
    </tr>`;
  }).join('');
}

loadPOs();
</script>
</body>
</html>
