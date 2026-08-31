<?php
require_once '../PHP/supplier_guard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>My Bids — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      <li><a href="supplier_my_bids.php" class="active"><i class="fas fa-gavel"></i> My Bids</a></li>
      <li><a href="supplier_my_pos.php"><i class="fas fa-file-invoice"></i> Purchase Orders</a></li>
    </ul></nav>
    <a href="../PHP/supplier_logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">My Bids</h1>

    <!-- Summary pills -->
    <div id="summary-pills" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;"></div>

    <div class="s-card">
      <h2><i class="fas fa-gavel"></i> Bid History</h2>

      <div style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;">
        <button class="filter-btn active" onclick="filterBids('all',this)">All</button>
        <button class="filter-btn"        onclick="filterBids('pending',this)">Pending</button>
        <button class="filter-btn"        onclick="filterBids('selected',this)">Won</button>
        <button class="filter-btn"        onclick="filterBids('rejected',this)">Not Selected</button>
      </div>

      <table class="dark-table" style="width:100%">
        <thead>
          <tr>
            <th>PR #</th>
            <th>MPN</th>
            <th>Category</th>
            <th>Qty Requested</th>
            <th>My Unit Price</th>
            <th>PHP Equivalent</th>
            <th>Total Value</th>
            <th>Delivery Date</th>
            <th>Status</th>
            <th>PO #</th>
            <th>Submitted</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="bids-tbody">
          <tr class="empty-row"><td colspan="12">Loading…</td></tr>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Edit bid modal -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeEdit()"><i class="fas fa-xmark"></i></button>
    <h2><i class="fas fa-pen"></i> Edit Bid</h2>
    <p class="modal-sub" id="edit-sub"></p>
    <div class="fg c2" style="margin-bottom:14px;">
      <div class="field">
        <label>Unit Price *</label>
        <input type="number" id="edit-price" step="0.0001" min="0.0001" oninput="updateEditHint()"/>
        <div id="edit-php-hint" class="php-equiv-block" style="min-height:1.1em;margin-top:4px;"></div>
      </div>
      <div class="field">
        <label>Delivery Date *</label>
        <input type="date" id="edit-date"/>
      </div>
    </div>
    <div class="field" style="margin-bottom:20px;">
      <label>Notes</label>
      <textarea id="edit-notes" placeholder="Any updates…"></textarea>
    </div>
    <div style="display:flex;gap:10px;">
      <button class="s-btn yellow" onclick="saveEdit()"><i class="fas fa-save"></i> Save Changes</button>
      <button class="s-btn red"    onclick="closeEdit()">Cancel</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API = '../PHP/supplier_api.php';
let editingBidId = null;
let editCur = 'PHP';
let editQty = 1;

const esc = s => String(s??'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
const ucf = s => s ? s.charAt(0).toUpperCase()+s.slice(1) : '';

let _tt;
function toast(msg,err=false){
  const t=document.getElementById('toast');
  t.textContent=msg; t.className='toast show'+(err?' err':'');
  clearTimeout(_tt); _tt=setTimeout(()=>t.className='toast',2800);
}

let allBids=[], currentFilter='all';

async function loadBids(){
  const res  = await fetch(`${API}?action=my_bids`);
  const data = await res.json();
  allBids = data.bids || [];

  const total    = allBids.length;
  const pending  = allBids.filter(b=>b.status==='pending').length;
  const selected = allBids.filter(b=>b.status==='selected').length;
  const rejected = allBids.filter(b=>b.status==='rejected').length;

  document.getElementById('summary-pills').innerHTML = `
    <div class="stat-chip"><div class="sc-label">Total</div><div class="sc-val">${total}</div></div>
    <div class="stat-chip" style="border-color:#fbbf2444;background:#1e1e2e"><div class="sc-label">Pending</div><div class="sc-val" style="color:var(--yellow)">${pending}</div></div>
    <div class="stat-chip" style="border-color:#22c55e44;background:#1a2a1a"><div class="sc-label">Won</div><div class="sc-val" style="color:var(--green)">${selected}</div></div>
    <div class="stat-chip" style="border-color:#f8717144;background:#1e1a1a"><div class="sc-label">Not Selected</div><div class="sc-val" style="color:var(--red)">${rejected}</div></div>
  `;
  renderBids();
}

function filterBids(f,btn){
  currentFilter=f;
  document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  renderBids();
}

function renderBids(){
  const tbody   = document.getElementById('bids-tbody');
  const filtered = currentFilter==='all' ? allBids : allBids.filter(b=>b.status===currentFilter);

  if(!filtered.length){
    tbody.innerHTML=`<tr class="empty-row"><td colspan="12">No bids found.</td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map(b => {
    const sym   = Currency.symbol(b.currency);
    const unitP = parseFloat(b.unit_price);
    const total = unitP * parseInt(b.quantity);

    const phpUnitStr = b.currency!=='PHP'
      ? `<div class="php-equiv-block">≈ ₱${Currency.toPHP(unitP, b.currency).toLocaleString('en-PH',{minimumFractionDigits:4,maximumFractionDigits:4})}</div>`
      : '';
    const phpTotStr = b.currency!=='PHP'
      ? `<div class="php-equiv-block">≈ ₱${Currency.toPHP(total, b.currency).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2})}</div>`
      : '';
    const phpEquivCol = b.currency!=='PHP'
      ? `₱${Currency.toPHP(unitP, b.currency).toLocaleString('en-PH',{minimumFractionDigits:4,maximumFractionDigits:4})}`
      : '<span style="color:var(--gray)">—</span>';

    const actions = b.status==='pending' ? `
      <div class="ra">
        <button class="ib edit" title="Edit" onclick='openEdit(${JSON.stringify({
          bid_id: b.id, pr_number: b.pr_number, mpn: b.mpn||"",
          currency: b.currency, quantity: parseInt(b.quantity),
          unit_price: b.unit_price, delivery_date: b.delivery_date, notes: b.notes||""
        })})'><i class="fas fa-pen"></i></button>
        <button class="ib del" title="Withdraw" onclick="withdrawBid(${b.id},'${esc(b.pr_number)}')">
          <i class="fas fa-xmark"></i>
        </button>
      </div>` : '—';

    const delDate = b.delivery_date ? new Date(b.delivery_date).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—';
    const subDate = new Date(b.created_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});

    return `<tr>
      <td><strong style="color:var(--green)">${esc(b.pr_number)}</strong></td>
      <td>${esc(b.mpn||'—')}</td>
      <td>${esc(b.category||'—')}</td>
      <td>${Number(b.quantity).toLocaleString()} <small style="color:var(--gray)">units (${esc(b.currency)})</small></td>
      <td><strong>${sym}${unitP.toFixed(4)}</strong>${phpUnitStr}</td>
      <td>${phpEquivCol}</td>
      <td>${sym}${total.toLocaleString('en-PH',{minimumFractionDigits:2})}${phpTotStr}</td>
      <td>${delDate}</td>
      <td><span class="bid-status-${b.status}">${ucf(b.status)}</span></td>
      <td>${b.po_number ? `<strong style="color:var(--purple)">${esc(b.po_number)}</strong>` : '—'}</td>
      <td>${subDate}</td>
      <td>${actions}</td>
    </tr>`;
  }).join('');
}

function openEdit(data){
  editingBidId = data.bid_id;
  editCur      = data.currency;
  editQty      = data.quantity;
  document.getElementById('edit-sub').textContent   = `${data.pr_number} — ${data.mpn}`;
  document.getElementById('edit-price').value       = data.unit_price;
  document.getElementById('edit-date').value        = data.delivery_date;
  document.getElementById('edit-notes').value       = data.notes;
  const tom = new Date(); tom.setDate(tom.getDate()+1);
  document.getElementById('edit-date').min = tom.toISOString().split('T')[0];
  updateEditHint();
  document.getElementById('edit-modal').classList.add('open');
}
function closeEdit(){ document.getElementById('edit-modal').classList.remove('open'); editingBidId=null; }

function updateEditHint(){
  const price = parseFloat(document.getElementById('edit-price').value);
  const div   = document.getElementById('edit-php-hint');
  if(!price||price<=0||editCur==='PHP'){ div.innerHTML=''; return; }
  const phpU = Currency.toPHP(price, editCur);
  const phpT = phpU * editQty;
  div.innerHTML = `≈ <strong style="color:var(--green)">₱${phpU.toLocaleString('en-PH',{minimumFractionDigits:4,maximumFractionDigits:4})}</strong> / unit · Total <strong style="color:var(--green)">₱${phpT.toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2})}</strong>`;
}

async function saveEdit(){
  const price    = parseFloat(document.getElementById('edit-price').value);
  const date     = document.getElementById('edit-date').value;
  const notes    = document.getElementById('edit-notes').value.trim();
  if(!price||price<=0||!date){ toast('Price and date required',true); return; }
  const res = await fetch(`${API}?action=update_bid`,{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({bid_id: editingBidId, unit_price: price, delivery_date: date, notes})
  });
  const d = await res.json();
  if(d.success){ closeEdit(); toast('Bid updated'); loadBids(); }
  else toast(d.message,true);
}

async function withdrawBid(bidId, prNum){
  const result = await Swal.fire({
    title: 'Withdraw Bid?',
    html: `Remove your bid for <strong>${prNum}</strong>?<br>
           <span style="font-size:.85rem;color:#9a9ab5">This cannot be undone.</span>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#444',
    confirmButtonText: '<i class="fas fa-xmark"></i> Yes, Withdraw',
    cancelButtonText: 'Keep Bid',
    background: '#2a2a3a',
    color: '#e0e0ff'
  });
  if(!result.isConfirmed) return;
  const res = await fetch(`${API}?action=withdraw_bid`,{
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({bid_id: bidId})
  });
  const d = await res.json();
  if(d.success){ toast('Bid withdrawn'); loadBids(); }
  else toast(d.message,true);
}

document.getElementById('edit-modal').addEventListener('click',e=>{ if(e.target.id==='edit-modal') closeEdit(); });
loadBids();
</script>
</body>
</html>
