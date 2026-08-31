<?php
require_once '../PHP/supplier_guard.php';
require_once '../../Admin/PHP/db.php';
require_once '../../Admin/PHP/currency_config.php';

$open_prs = $conn->query("
    SELECT
        pr.id, pr.pr_number, pr.mpn, pr.manufacturer, pr.category, pr.subcategory,
        pr.quantity, pr.unit_price, pr.currency, pr.reason, pr.urgency,
        pr.required_by, pr.created_at,
        (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id) AS total_bids,
        (SELECT sb2.id           FROM supplier_bids sb2 WHERE sb2.pr_id = pr.id AND sb2.supplier_id = $supplier_id LIMIT 1) AS my_bid_id,
        (SELECT sb2.unit_price   FROM supplier_bids sb2 WHERE sb2.pr_id = pr.id AND sb2.supplier_id = $supplier_id LIMIT 1) AS my_unit_price,
        (SELECT sb2.delivery_date FROM supplier_bids sb2 WHERE sb2.pr_id = pr.id AND sb2.supplier_id = $supplier_id LIMIT 1) AS my_delivery,
        (SELECT sb2.notes        FROM supplier_bids sb2 WHERE sb2.pr_id = pr.id AND sb2.supplier_id = $supplier_id LIMIT 1) AS my_notes,
        (SELECT sb2.status       FROM supplier_bids sb2 WHERE sb2.pr_id = pr.id AND sb2.supplier_id = $supplier_id LIMIT 1) AS my_bid_status,
        (SELECT COUNT(*) FROM purchase_orders po WHERE po.pr_id = pr.id) AS po_issued
    FROM purchase_requests pr
    WHERE pr.status = 'approved'
    ORDER BY pr.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Open Requests — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../CSS/supplier_style.css?v=<?= time() ?>">
  <script src="../../Admin/JS/currency.js"></script>
  <style>
    .pr-row:hover { background: rgba(74,222,128,.06) !important; }
    .pr-row:hover td:first-child { border-left: 3px solid var(--green); }
  </style>
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
      <li><a href="supplier_open_requests.php" class="active"><i class="fas fa-folder-open"></i> Open Requests</a></li>
      <li><a href="supplier_my_bids.php"><i class="fas fa-gavel"></i> My Bids</a></li>
      <li><a href="supplier_my_pos.php"><i class="fas fa-file-invoice"></i> Purchase Orders</a></li>
    </ul></nav>
    <a href="../PHP/supplier_logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Open Requests</h1>

    <div class="filter-bar">
      <input type="text" id="search-input" placeholder="Search by PR #, MPN, or category…" oninput="filterCards()"/>
      <button class="filter-btn active" onclick="setFilter('all',this)">All</button>
      <button class="filter-btn"        onclick="setFilter('no-bid',this)">Not Bid Yet</button>
      <button class="filter-btn"        onclick="setFilter('bid-sent',this)">Bid Submitted</button>
    </div>

    <div id="pr-container">
    <?php if (empty($open_prs)): ?>
      <div class="empty-state">
        <i class="fas fa-folder-open"></i>
        <p>No open purchase requests at the moment. Check back later.</p>
      </div>
    <?php else: ?>

    <div class="s-card" style="padding:0;overflow:hidden;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#1a1a2e;">
            <th style="padding:13px 16px;text-align:left;font-size:.72rem;color:#9a9ab5;font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border);">PR # / MPN</th>
            <th style="padding:13px 16px;text-align:left;font-size:.72rem;color:#9a9ab5;font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border);">Category</th>
            <th style="padding:13px 16px;text-align:left;font-size:.72rem;color:#9a9ab5;font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border);">Qty</th>
            <th style="padding:13px 16px;text-align:left;font-size:.72rem;color:#9a9ab5;font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border);">Ref Price</th>
            <th style="padding:13px 16px;text-align:left;font-size:.72rem;color:#9a9ab5;font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border);">Required By</th>
            <th style="padding:13px 16px;text-align:center;font-size:.72rem;color:#9a9ab5;font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border);">Bids</th>
            <th style="padding:13px 16px;text-align:left;font-size:.72rem;color:#9a9ab5;font-weight:600;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--border);">Status</th>
          </tr>
        </thead>
        <tbody>
      <?php foreach ($open_prs as $pr):
        $hasBid   = !empty($pr['my_bid_id']);
        $poIssued = $pr['po_issued'] > 0;
        $urgClass = match(strtolower($pr['urgency'] ?? '')) { 'high' => 'high', 'low' => 'low', default => 'normal' };
        $sym      = CURRENCY_SYMBOLS[$pr['currency']] ?? $pr['currency'].' ';
        $phpEquivOriginal = '';
        if ($pr['currency'] !== 'PHP' && $pr['unit_price']) {
            $phpEquivOriginal = '<br><span class="php-equiv">≈ ₱'.number_format(to_php((float)$pr['unit_price'], $pr['currency']), 4).'</span>';
        }
        $rowBg = $poIssued ? 'rgba(167,139,250,.04)' : ($hasBid ? 'rgba(34,197,94,.04)' : 'transparent');
        // Build the full data payload for the popup
        $popupData = json_encode([
          "id"            => (int)$pr["id"],
          "pr_number"     => $pr["pr_number"],
          "mpn"           => $pr["mpn"] ?? "",
          "manufacturer"  => $pr["manufacturer"] ?? "",
          "category"      => $pr["category"] ?? "",
          "subcategory"   => $pr["subcategory"] ?? "",
          "quantity"      => (int)$pr["quantity"],
          "currency"      => $pr["currency"],
          "unit_price"    => (float)($pr["unit_price"] ?? 0),
          "reason"        => $pr["reason"] ?? "",
          "urgency"       => $pr["urgency"] ?? "",
          "required_by"   => $pr["required_by"] ?? "",
          "total_bids"    => (int)$pr["total_bids"],
          "created_at"    => $pr["created_at"],
          "hasBid"        => $hasBid,
          "poIssued"      => $poIssued,
          "my_bid_id"     => (int)($pr["my_bid_id"] ?? 0),
          "my_unit_price" => (float)($pr["my_unit_price"] ?? 0),
          "my_delivery"   => $pr["my_delivery"] ?? "",
          "my_notes"      => $pr["my_notes"] ?? "",
          "my_bid_status" => $pr["my_bid_status"] ?? "",
        ]);
      ?>
        <tr class="pr-row"
            data-search="<?= strtolower(htmlspecialchars($pr['pr_number'].' '.$pr['mpn'].' '.$pr['category'])) ?>"
            data-status="<?= $hasBid ? 'bid-sent' : 'no-bid' ?>"
            onclick="rowClick(<?= htmlspecialchars($popupData, ENT_QUOTES) ?>)"
            style="border-bottom:1px solid var(--border);cursor:pointer;background:<?= $rowBg ?>;transition:background .15s;">

          <td style="padding:14px 16px;">
            <div style="font-size:.88rem;font-weight:800;color:var(--green)"><?= htmlspecialchars($pr['pr_number']) ?></div>
            <div style="font-size:.93rem;font-weight:700;color:var(--text-light);margin-top:2px"><?= htmlspecialchars($pr['mpn'] ?? '—') ?></div>
            <?php if ($pr['manufacturer']): ?><div style="font-size:.74rem;color:#9a9ab5"><?= htmlspecialchars($pr['manufacturer']) ?></div><?php endif; ?>
          </td>

          <td style="padding:14px 16px;font-size:.85rem;color:#9a9ab5;">
            <?= htmlspecialchars($pr['category'] ?? '—') ?>
            <?php if ($pr['subcategory']): ?><div style="font-size:.74rem;color:#666"><?= htmlspecialchars($pr['subcategory']) ?></div><?php endif; ?>
          </td>

          <td style="padding:14px 16px;font-size:.88rem;color:var(--text-light);">
            <strong><?= number_format($pr['quantity']) ?></strong>
            <span style="font-size:.74rem;color:#9a9ab5"> <?= htmlspecialchars($pr['currency']) ?></span>
          </td>

          <td style="padding:14px 16px;font-size:.88rem;">
            <?php if ($pr['unit_price']): ?>
              <strong style="color:var(--text-light)"><?= $sym . number_format($pr['unit_price'], 4) ?></strong><?= $phpEquivOriginal ?>
            <?php else: ?><span style="color:#555">—</span><?php endif; ?>
          </td>

          <td style="padding:14px 16px;font-size:.85rem;">
            <?php if ($pr['required_by']): ?>
              <span style="color:var(--yellow);font-weight:600"><?= date('M d, Y', strtotime($pr['required_by'])) ?></span>
            <?php else: ?><span style="color:#555">—</span><?php endif; ?>
            <?php if ($pr['urgency']): ?>
              <div style="margin-top:4px"><span class="badge urgency-<?= $urgClass ?>"><?= htmlspecialchars($pr['urgency']) ?></span></div>
            <?php endif; ?>
          </td>

          <td style="padding:14px 16px;text-align:center;">
            <strong style="color:var(--text-light)"><?= $pr['total_bids'] ?></strong>
          </td>

          <td style="padding:14px 16px;">
            <?php if ($poIssued): ?>
              <span class="badge purple">PO Issued</span>
            <?php elseif ($hasBid): ?>
              <span class="badge green"><i class="fas fa-check" style="margin-right:3px"></i>Bid Sent</span>
            <?php else: ?>
              <span class="badge yellow">Open</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
    </div>
  </main>
</div>

<!-- SUBMIT BID MODAL -->
<div class="modal-overlay" id="bid-modal">
  <div class="modal-box" style="max-width:680px;">
    <button class="modal-close" onclick="closeBidModal()"><i class="fas fa-xmark"></i></button>
    <h2><i class="fas fa-gavel"></i> Submit Bid</h2>
    <p class="modal-sub" id="modal-sub"></p>

    <div id="modal-pr-strip"></div>

    <div id="modal-reason-box" style="display:none;background:#1e1e2e;border-left:3px solid var(--border);border-radius:0 8px 8px 0;padding:10px 14px;margin-bottom:18px;font-size:.83rem;color:#9a9ab5;font-style:italic;line-height:1.5;"></div>

    <div class="fg c2" style="margin-bottom:14px;">
      <div class="field">
        <label>Unit Price (<span id="modal-cur-label">PHP</span>) *</label>
        <input type="number" id="bid-unit-price" step="0.0001" min="0.0001" placeholder="e.g. 0.0420" oninput="updatePhpHint()"/>
        <div id="modal-php-hint" class="php-equiv-block" style="min-height:1.1em;margin-top:4px;"></div>
      </div>
      <div class="field">
        <label>Estimated Delivery Date *</label>
        <input type="date" id="bid-delivery"/>
      </div>
    </div>
    <div class="field" style="margin-bottom:20px;">
      <label>Notes (optional)</label>
      <textarea id="bid-notes" placeholder="Min order qty, lead time, warranty, payment terms…"></textarea>
    </div>
    <div id="bid-modal-actions" style="display:flex;gap:10px;">
      <button class="s-btn" id="submit-btn" onclick="submitBid()"><i class="fas fa-paper-plane"></i> Submit Bid</button>
      <button class="s-btn red" onclick="closeBidModal()">Cancel</button>
    </div>
  </div>
</div>


<div class="toast" id="toast"></div>
<script>
const API = '../PHP/supplier_api.php';
let activePR    = null;
let activeBidId = null;
let editCurrency = 'PHP';
let editQty      = 1;

const esc = s => String(s??'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

let _tt;
function toast(msg, err=false){
  const t = document.getElementById('toast');
  t.textContent = msg; t.className = 'toast show'+(err?' err':'');
  clearTimeout(_tt); _tt = setTimeout(()=>t.className='toast', 2800);
}

// ── Filter ──────────────────────────────────────────────
let currentFilter = 'all';
function setFilter(f, btn){
  currentFilter = f;
  document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  filterCards();
}
function filterCards(){
  const q = document.getElementById('search-input').value.toLowerCase();
  document.querySelectorAll('.pr-row').forEach(row=>{
    const ms = !q || row.dataset.search.includes(q);
    const mf = currentFilter==='all' || row.dataset.status===currentFilter;
    row.style.display = (ms && mf) ? '' : 'none';
  });
}

// ── PHP hint helper ─────────────────────────────────────
function phpHint(price, currency, qty){
  if(!price || price<=0 || currency==='PHP') return '';
  const phpUnit = Currency.toPHP(price, currency);
  const phpTot  = phpUnit * qty;
  return `≈ <strong style="color:var(--green)">₱${phpUnit.toLocaleString('en-PH',{minimumFractionDigits:4,maximumFractionDigits:4})}</strong> / unit`
       + ` &nbsp;·&nbsp; Total <strong style="color:var(--green)">₱${phpTot.toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2})}</strong> for ${qty.toLocaleString()} units`;
}

// ── Build PR detail strip (used in all modal states) ────
function buildPrStrip(pr){
  const sym = Currency.symbol(pr.currency);
  const cat = pr.category + (pr.subcategory ? ` / ${pr.subcategory}` : '');
  let refHtml = '—';
  if(pr.unit_price > 0){
    refHtml = `<strong>${sym}${Number(pr.unit_price).toFixed(4)}</strong>`;
    if(pr.currency !== 'PHP'){
      const phpVal = Currency.toPHP(pr.unit_price, pr.currency);
      refHtml += ` <span style="color:#9a9ab5;font-size:.82rem">≈ ₱${phpVal.toLocaleString('en-PH',{minimumFractionDigits:4,maximumFractionDigits:4})}</span>`;
    }
  }
  return `
    <div style="background:#1e1e2e;border:1px solid var(--border);border-radius:12px;padding:14px 18px;margin-bottom:16px;">
      <div style="display:flex;flex-wrap:wrap;gap:6px 22px;margin-bottom:10px;">
        <div><div style="font-size:.7rem;color:var(--gray);text-transform:uppercase;margin-bottom:2px">PR #</div>
             <div style="font-size:.95rem;font-weight:800;color:var(--green)">${esc(pr.pr_number)}</div></div>
        <div><div style="font-size:.7rem;color:var(--gray);text-transform:uppercase;margin-bottom:2px">MPN</div>
             <div style="font-size:.88rem;font-weight:600;color:var(--text-light)">${esc(pr.mpn||'—')}</div></div>
        ${pr.manufacturer?`<div><div style="font-size:.7rem;color:var(--gray);text-transform:uppercase;margin-bottom:2px">Manufacturer</div>
             <div style="font-size:.88rem;font-weight:600;color:var(--text-light)">${esc(pr.manufacturer)}</div></div>`:''}
        <div><div style="font-size:.7rem;color:var(--gray);text-transform:uppercase;margin-bottom:2px">Category</div>
             <div style="font-size:.88rem;font-weight:600;color:var(--text-light)">${esc(cat)}</div></div>
      </div>
      <hr style="border:none;border-top:1px solid var(--border);margin:8px 0"/>
      <div style="display:flex;flex-wrap:wrap;gap:6px 22px;">
        <div><div style="font-size:.7rem;color:var(--gray);text-transform:uppercase;margin-bottom:2px">Qty Required</div>
             <div style="font-size:.88rem;font-weight:600;color:var(--text-light)">${Number(pr.quantity).toLocaleString()} <small style="color:var(--gray)">units</small></div></div>
        <div><div style="font-size:.7rem;color:var(--gray);text-transform:uppercase;margin-bottom:2px">Currency</div>
             <div style="font-size:.88rem;font-weight:600;color:var(--text-light)">${esc(pr.currency)} (${sym})</div></div>
        <div><div style="font-size:.7rem;color:var(--gray);text-transform:uppercase;margin-bottom:2px">Ref Price</div>
             <div style="font-size:.88rem;font-weight:600">${refHtml}</div></div>
        ${pr.required_by?`<div><div style="font-size:.7rem;color:var(--gray);text-transform:uppercase;margin-bottom:2px">Required By</div>
             <div style="font-size:.88rem;font-weight:600;color:var(--yellow)">${pr.required_by}</div></div>`:''}
        ${pr.total_bids!==undefined?`<div><div style="font-size:.7rem;color:var(--gray);text-transform:uppercase;margin-bottom:2px">Bids Received</div>
             <div style="font-size:.88rem;font-weight:600;color:var(--text-light)">${pr.total_bids}</div></div>`:''}
      </div>
    </div>`;
}

// ── Reason box helper ───────────────────────────────────
function setReasonBox(reason){
  const box = document.getElementById('modal-reason-box');
  if(reason && reason.trim()){
    box.style.display = '';
    box.innerHTML = `<i class="fas fa-quote-left" style="color:#555;margin-right:6px"></i><em>${esc(reason)}</em>`;
  } else {
    box.style.display = 'none';
  }
}

// ── Row click router ────────────────────────────────────
function rowClick(pr){
  if(pr.poIssued){ openViewModal(pr); return; }
  if(pr.hasBid)   { openViewModal(pr); }
  else             { openBidModal(pr); }
}

// ── SUBMIT BID MODAL ────────────────────────────────────
function openBidModal(pr){
  activePR = pr;

  // Reset to submit state
  document.querySelector('#bid-modal h2').innerHTML = `<i class="fas fa-gavel"></i> Submit Bid`;
  document.getElementById('modal-sub').innerHTML =
    `<span style="color:var(--green);font-weight:800">${esc(pr.pr_number)}</span>` +
    (pr.urgency ? ` &nbsp;<span style="background:${pr.urgency==='High'?'#fee2e2':pr.urgency==='Low'?'#dcfce7':'#f1f5f9'};
      color:${pr.urgency==='High'?'#991b1b':pr.urgency==='Low'?'#15803d':'#64748b'};
      font-size:.72rem;font-weight:700;padding:2px 9px;border-radius:20px;text-transform:uppercase">${esc(pr.urgency)}</span>` : '');

  document.getElementById('modal-cur-label').textContent = pr.currency;
  document.getElementById('modal-pr-strip').innerHTML    = buildPrStrip(pr);
  setReasonBox(pr.reason);

  document.getElementById('bid-unit-price').readOnly = false;
  document.getElementById('bid-delivery').readOnly   = false;
  document.getElementById('bid-unit-price').value    = '';
  document.getElementById('bid-delivery').value      = '';
  document.getElementById('bid-notes').value         = '';
  document.getElementById('modal-php-hint').innerHTML = '';

  const tom = new Date(); tom.setDate(tom.getDate()+1);
  document.getElementById('bid-delivery').min = tom.toISOString().split('T')[0];

  document.getElementById('bid-modal-actions').innerHTML = `
    <button class="s-btn" id="submit-btn" onclick="submitBid()">
      <i class="fas fa-paper-plane"></i> Submit Bid
    </button>
    <button class="s-btn red" onclick="closeBidModal()">Cancel</button>`;

  document.getElementById('bid-modal').classList.add('open');
}

function closeBidModal(){
  document.getElementById('bid-modal').classList.remove('open');
  activePR = null;
}

function updatePhpHint(){
  const price = parseFloat(document.getElementById('bid-unit-price').value);
  const div   = document.getElementById('modal-php-hint');
  if(!activePR || !price || price<=0){ div.innerHTML=''; return; }
  div.innerHTML = phpHint(price, activePR.currency, activePR.quantity);
}

async function submitBid(){
  const price    = parseFloat(document.getElementById('bid-unit-price').value);
  const delivery = document.getElementById('bid-delivery').value;
  const notes    = document.getElementById('bid-notes').value.trim();
  if(!price || price<=0 || !delivery){ toast('Unit price and delivery date are required', true); return; }

  const btn = document.getElementById('submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';
  try {
    const res = await fetch(`${API}?action=submit_bid`,{
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({pr_id: activePR.id, unit_price: price, delivery_date: delivery, notes})
    });
    const d = await res.json();
    if(d.success){ closeBidModal(); toast('Bid submitted!'); setTimeout(()=>location.reload(), 900); }
    else toast(d.message, true);
  } catch { toast('Network error', true); }
  finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Bid';
  }
}

// ── VIEW / EDIT BID MODAL (for already-bid rows) ────────
function openViewModal(pr){
  activePR = pr;

  // Title + subtitle
  if(pr.poIssued){
    document.querySelector('#bid-modal h2').innerHTML = `<i class="fas fa-file-invoice"></i> Request Closed`;
    document.getElementById('modal-sub').innerHTML = pr.hasBid
      ? `Your bid was <strong style="color:${pr.my_bid_status==='selected'?'#4ade80':'#9a9ab5'}">${pr.my_bid_status==='selected'?'selected as winner':'not selected'}</strong>.`
      : `<span style="color:#a78bfa">Awarded to another supplier.</span>`;
  } else {
    const sc = {pending:'#fbbf24', selected:'#4ade80', rejected:'#f87171'}[pr.my_bid_status]||'#9a9ab5';
    document.querySelector('#bid-modal h2').innerHTML = `<i class="fas fa-gavel"></i> ${esc(pr.pr_number)}`;
    document.getElementById('modal-sub').innerHTML =
      `Your bid &nbsp;<span style="background:${sc}22;color:${sc};border:1px solid ${sc}44;
        font-size:.72rem;font-weight:700;padding:2px 9px;border-radius:20px;text-transform:uppercase">${pr.my_bid_status||'pending'}</span>`;
  }

  document.getElementById('modal-cur-label').textContent = pr.currency;
  document.getElementById('modal-pr-strip').innerHTML    = buildPrStrip(pr);
  setReasonBox(pr.reason);

  // Show existing bid values (read-only)
  document.getElementById('bid-unit-price').value    = pr.my_unit_price || '';
  document.getElementById('bid-delivery').value      = pr.my_delivery   || '';
  document.getElementById('bid-notes').value         = '';
  document.getElementById('bid-unit-price').readOnly = true;
  document.getElementById('bid-delivery').readOnly   = true;
  document.getElementById('modal-php-hint').innerHTML = pr.my_unit_price
    ? phpHint(parseFloat(pr.my_unit_price), pr.currency, pr.quantity) : '';

  // Action buttons depend on state
  const actionDiv = document.getElementById('bid-modal-actions');
  if(pr.poIssued || pr.my_bid_status !== 'pending'){
    actionDiv.innerHTML = `
      <button class="s-btn" style="background:#444" onclick="closeBidModal()">
        <i class="fas fa-xmark"></i> Close
      </button>`;
  } else {
    actionDiv.innerHTML = `
      <button class="s-btn yellow" onclick="switchToEdit()">
        <i class="fas fa-pen"></i> Edit Bid
      </button>
      <button class="s-btn red" onclick="withdrawBid(${pr.my_bid_id},'${esc(pr.pr_number)}')">
        <i class="fas fa-xmark"></i> Withdraw
      </button>
      <button class="s-btn" style="background:#444" onclick="closeBidModal()">Cancel</button>`;
  }

  document.getElementById('bid-modal').classList.add('open');
}

function switchToEdit(){
  document.getElementById('bid-unit-price').readOnly = false;
  document.getElementById('bid-delivery').readOnly   = false;
  document.getElementById('bid-unit-price').focus();
  document.getElementById('bid-modal-actions').innerHTML = `
    <button class="s-btn" onclick="saveEditedBid()">
      <i class="fas fa-save"></i> Save Changes
    </button>
    <button class="s-btn red" onclick="closeBidModal()">Cancel</button>`;
}

async function saveEditedBid(){
  const price    = parseFloat(document.getElementById('bid-unit-price').value);
  const delivery = document.getElementById('bid-delivery').value;
  const notes    = document.getElementById('bid-notes').value.trim();
  if(!price || price<=0 || !delivery){ toast('Price and delivery date required', true); return; }
  try {
    const res = await fetch(`${API}?action=update_bid`,{
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({bid_id: activePR.my_bid_id, unit_price: price, delivery_date: delivery, notes})
    });
    const d = await res.json();
    if(d.success){ closeBidModal(); toast('Bid updated'); setTimeout(()=>location.reload(), 900); }
    else toast(d.message, true);
  } catch { toast('Network error', true); }
}

// ── Withdraw ────────────────────────────────────────────
async function withdrawBid(bidId, prNum){
  const result = await Swal.fire({
    title: 'Withdraw Bid?',
    html: `Remove your bid for <strong>${esc(prNum)}</strong>?<br>
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
  try {
    const res = await fetch(`${API}?action=withdraw_bid`,{
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({bid_id: bidId})
    });
    const d = await res.json();
    if(d.success){ closeBidModal(); toast('Bid withdrawn'); setTimeout(()=>location.reload(), 900); }
    else toast(d.message, true);
  } catch { toast('Network error', true); }
}

// ── Close on overlay click ──────────────────────────────
document.getElementById('bid-modal').addEventListener('click', e=>{
  if(e.target.id === 'bid-modal') closeBidModal();
});
</script>

</body>
</html>
