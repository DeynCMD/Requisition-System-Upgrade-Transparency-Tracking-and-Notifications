<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'BUYER') {
    header("Location: ../../Admin/HTML/ZE-Electronics.php"); exit;
}

function loadEnvironmentVariables() {
    $envFile = __DIR__ . '/../../Requesitor/Node/.env';
    if (!file_exists($envFile)) return false;
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name); $value = trim(trim($value), '"\'');
            $_ENV[$name] = $value; putenv("$name=$value");
        }
    }
    return true;
}
loadEnvironmentVariables();
$client_id     = $_ENV['DIGIKEY_CLIENT_ID'] ?? '';
$client_secret = $_ENV['DIGIKEY_CLIENT_SECRET'] ?? '';
$digikey_available = !empty($client_id) && !empty($client_secret);

require_once '../../Admin/PHP/db.php';

// DigiKey token
$token = false;
if ($digikey_available) {
    if (!isset($_SESSION['digikey_token']) || time() - ($_SESSION['digikey_token_time'] ?? 0) > 3500) {
        $ch = curl_init('https://api.digikey.com/v1/oauth2/token');
        curl_setopt_array($ch, [CURLOPT_POST=>1, CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_POSTFIELDS=>http_build_query(['client_id'=>$client_id,'client_secret'=>$client_secret,'grant_type'=>'client_credentials']),
            CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded']]);
        $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($code === 200) { $t = json_decode($res,true); $_SESSION['digikey_token']=$t['access_token']??false; $_SESSION['digikey_token_time']=time(); $token=$_SESSION['digikey_token']; }
    } else { $token = $_SESSION['digikey_token']; }
}

// Finance-approved PRs for combined export
$result = $conn->query("
    SELECT fa.id, fa.pr_number, fa.total_amount, fa.finance_approved_at, fa.created_at, fa.status,
           pr.mpn, pr.quantity, pr.unit_price, pr.currency
    FROM finance_approvals fa
    LEFT JOIN purchase_requests pr ON fa.pr_number = pr.pr_number
    WHERE fa.finance_approved_at IS NOT NULL
    ORDER BY fa.finance_approved_at DESC");
$approved_prs_map = [];
while ($row = $result->fetch_assoc()) {
    $pn = $row['pr_number'];
    if (!isset($approved_prs_map[$pn])) $approved_prs_map[$pn] = ['id'=>$row['id'],'pr_number'=>$pn,'total_amount'=>$row['total_amount'],'finance_approved_at'=>$row['finance_approved_at'],'items'=>[]];
    if ($row['mpn']) $approved_prs_map[$pn]['items'][] = ['mpn'=>$row['mpn'],'quantity'=>$row['quantity'],'unit_price'=>$row['unit_price'],'currency'=>$row['currency']];
}
$approved_prs = array_values($approved_prs_map);

// Suppliers + eligible PRs for Split PO
$suppliers    = $conn->query("SELECT id, name, contact FROM suppliers WHERE active=1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$eligible_prs = $conn->query("
    SELECT pr.id, pr.pr_number, pr.mpn, pr.quantity, pr.currency,
           COALESCE(SUM(po.quantity),0) AS allocated_qty
    FROM purchase_requests pr
    LEFT JOIN purchase_orders po ON po.pr_id = pr.id AND po.status != 'Cancelled'
    WHERE pr.status = 'approved'
    GROUP BY pr.id HAVING allocated_qty < pr.quantity
    ORDER BY pr.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$suppliersJson   = json_encode($suppliers);
$eligiblePrsJson = json_encode($eligible_prs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Export & PO — Buyer</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../CSS/dashboard.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../CSS/export.css?v=<?= time() ?>">
  <style>
    /* tabs */
    .tabs{display:flex;gap:10px;margin-bottom:26px;flex-wrap:wrap;}
    .tab-btn{background:#2a2a3a;border:1px solid #444;color:#b8b8d4;padding:12px 22px;border-radius:12px;cursor:pointer;font-size:.95rem;font-weight:500;display:flex;align-items:center;gap:10px;transition:all .25s;font-family:inherit;}
    .tab-btn:hover{color:#fff;border-color:#22c55e;}
    .tab-btn.active{background:#22c55e;color:#fff;border-color:#22c55e;}
    .tab-panel{display:none;}
    .tab-panel.active{display:block;animation:fade .2s ease;}
    @keyframes fade{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
    /* bulk bar */
    .bulk-actions{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding:15px 20px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:12px;box-shadow:0 4px 15px rgba(102,126,234,.3);}
    .bulk-actions-left{display:flex;align-items:center;gap:15px;}
    .select-all-container{display:flex;align-items:center;gap:10px;color:white;font-weight:500;}
    .select-all-container input[type="checkbox"]{width:20px;height:20px;cursor:pointer;}
    .selected-count{background:rgba(255,255,255,.2);padding:5px 15px;border-radius:20px;color:white;font-weight:600;}
    .bulk-export-btn{background:linear-gradient(135deg,#f093fb,#f5576c);color:white;border:none;padding:12px 30px;border-radius:25px;font-size:16px;font-weight:600;cursor:pointer;transition:all .3s ease;box-shadow:0 4px 15px rgba(245,87,108,.4);}
    .bulk-export-btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(245,87,108,.6);}
    .bulk-export-btn:disabled{background:#ccc;cursor:not-allowed;transform:none;box-shadow:none;}
    .pr-card{position:relative;cursor:pointer;transition:all .3s ease;}
    .pr-card.selected{border:3px solid #667eea;box-shadow:0 8px 25px rgba(102,126,234,.4);transform:scale(1.02);}
    .card-checkbox{position:absolute;top:15px;right:15px;width:24px;height:24px;cursor:pointer;z-index:10;}
    .pr-card-header{position:relative;padding-right:40px;}
    /* Split PO section */
    .s-card{background:#2a2a3a;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.4);margin-bottom:20px;}
    .s-card h2{font-size:1.1rem;color:#fbbf24;margin-bottom:16px;display:flex;align-items:center;gap:10px;}
    .pr-split-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px;}
    .pr-split-card{background:#1e1e2e;border:2px solid #333;border-radius:14px;padding:16px 18px;cursor:pointer;transition:.2s;position:relative;}
    .pr-split-card:hover{border-color:#22c55e;transform:translateY(-2px);box-shadow:0 8px 24px rgba(34,197,94,.15);}
    .pr-split-card .prc-num{font-size:1rem;font-weight:700;color:#4ade80;margin-bottom:6px;}
    .pr-split-card .prc-row{font-size:.82rem;color:#9a9ab5;margin-bottom:3px;}
    .pr-split-card .prc-row strong{color:#e0e0ff;}
    .pr-split-card .prc-rem{margin-top:8px;font-size:.75rem;font-weight:700;color:#4ade80;background:rgba(34,197,94,.12);padding:3px 9px;border-radius:20px;display:inline-block;}
    .empty-split{text-align:center;color:#9a9ab5;padding:32px;font-size:.9rem;}
    /* Split modal */
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:1000;align-items:flex-start;justify-content:center;padding:40px 20px;overflow-y:auto;}
    .modal-overlay.open{display:flex;}
    .modal-box{background:#2a2a3a;border-radius:20px;width:100%;max-width:820px;padding:32px;box-shadow:0 20px 60px rgba(0,0,0,.6);position:relative;}
    .modal-close-btn{position:absolute;top:16px;right:20px;background:transparent;border:none;color:#9a9ab5;font-size:1.6rem;cursor:pointer;line-height:1;}
    .modal-close-btn:hover{color:#fff;}
    .modal-box h2{color:#4ade80;font-size:1.2rem;margin-bottom:6px;}
    .pr-banner{background:linear-gradient(135deg,#1a1a2e,#1e2a1e);border:1px solid #22c55e44;border-radius:12px;padding:14px 18px;margin-bottom:18px;display:flex;gap:18px;flex-wrap:wrap;}
    .pr-banner .bi .bl{font-size:.72rem;color:#9a9ab5;text-transform:uppercase;letter-spacing:.3px;margin-bottom:2px;}
    .pr-banner .bi .bv{font-size:.9rem;font-weight:700;color:#e0e0ff;}
    .pr-banner .bi .bv.g{color:#4ade80;}
    .alloc-ctr{background:#1a1a2e;border-radius:10px;padding:10px 16px;margin-bottom:14px;font-size:.87rem;color:#9a9ab5;display:flex;gap:18px;flex-wrap:wrap;}
    .alloc-ctr span strong{color:#e0e0ff;}
    .po-line{background:#1e1e2e;padding:14px 16px;border-radius:12px;border:1px solid #333;margin-bottom:10px;}
    .po-line .lhdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;}
    .po-line .lnum{font-size:.8rem;font-weight:700;color:#fbbf24;}
    .po-line .ldel{background:transparent;border:1px solid #f87171;color:#f87171;border-radius:7px;padding:4px 9px;cursor:pointer;font-size:.78rem;display:flex;align-items:center;gap:4px;transition:.2s;}
    .po-line .ldel:hover{background:#f87171;color:#fff;}
    .sup-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:7px;margin-bottom:10px;}
    .sup-tile{background:#12121a;border:2px solid #333;border-radius:9px;padding:9px 11px;cursor:pointer;text-align:center;transition:.2s;}
    .sup-tile:hover{border-color:#60a5fa;}
    .sup-tile.sel{border-color:#22c55e;background:#0d1f15;}
    .sup-tile .stck{color:#22c55e;font-size:.85rem;margin-bottom:2px;display:none;}
    .sup-tile.sel .stck{display:block;}
    .sup-tile .stn{font-weight:700;color:#e0e0ff;font-size:.8rem;}
    .line-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;}
    @media(max-width:600px){.line-row{grid-template-columns:1fr;}}
    .fld label{display:block;font-size:.78rem;color:#9a9ab5;margin-bottom:4px;}
    .fld input{width:100%;background:#1a1a2e;border:1px solid #444;color:#e0e0ff;padding:10px 12px;border-radius:9px;font-size:.9rem;font-family:inherit;outline:none;transition:border-color .2s;}
    .fld input:focus{border-color:#22c55e;}
    .add-line-btn{background:transparent;border:1px dashed #22c55e;color:#4ade80;padding:8px 16px;border-radius:9px;cursor:pointer;font-size:.87rem;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;gap:7px;transition:.2s;}
    .add-line-btn:hover{background:rgba(34,197,94,.1);}
    .gen-btn{background:#22c55e;color:#fff;border:none;padding:10px 20px;border-radius:11px;cursor:pointer;font-size:.91rem;font-weight:600;font-family:inherit;display:inline-flex;align-items:center;gap:7px;transition:.2s;}
    .gen-btn:hover{background:#16a34a;}
    @media print{.bulk-actions,.sidebar,.modal-actions,.modal-close,.no-print,.page-header,.pr-grid,.tabs,.tab-panel#tab-split{display:none!important;}.main-content{margin-left:0!important;padding:0!important;}.modal-content{box-shadow:none!important;background:white!important;color:black!important;}.po-document{padding:20px;}}
  </style>
</head>
<body>
<div class="container">
  <aside class="sidebar">
    <div class="profile"><img src="../Assets/Avatar.jpg" alt="Buyer"/><span class="role">BUYER</span></div>
    <nav class="nav-menu"><ul>
      <li><a href="buyer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="buyer.php"><i class="fas fa-shopping-cart"></i> Purchase Requests</a></li>
      <li><a href="buyer_history.php"><i class="fas fa-history"></i> History</a></li>
      <li><a href="buyer_export.php" class="active"><i class="fas fa-file-export"></i> Export PO</a></li>
      <li><a href="buyer_returns.php"><i class="fas fa-rotate-left"></i> Returns</a></li>
    </ul></nav>
    <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1><i class="fas fa-file-export"></i> Export & Purchase Orders</h1>
      <p class="subtitle">Generate combined PO documents or create split POs across multiple suppliers</p>
    </div>

    <div class="tabs">
      <button class="tab-btn active" data-tab="export"><i class="fas fa-file-pdf"></i> Combined PO Export</button>
      <button class="tab-btn" data-tab="split"><i class="fas fa-scissors"></i> Split PO</button>
    </div>

    <!-- TAB 1 — COMBINED PO EXPORT -->
    <div class="tab-panel active" id="tab-export">
      <?php if(empty($approved_prs)): ?>
        <div class="empty-state">
          <i class="fas fa-file-alt fa-4x"></i>
          <h3>No Finance-Approved PRs Found</h3>
          <p>Once finance approves your requests, they will appear here for PO generation.</p>
        </div>
      <?php else: ?>
        <div class="bulk-actions">
          <div class="bulk-actions-left">
            <div class="select-all-container">
              <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
              <label for="selectAll">Select All</label>
            </div>
            <span class="selected-count" id="selectedCount">0 selected</span>
          </div>
          <button class="bulk-export-btn" id="bulkExportBtn" onclick="generateCombinedPO()" disabled>
            <i class="fas fa-file-pdf"></i> Generate Combined PO
          </button>
        </div>
        <div class="pr-grid">
          <?php foreach($approved_prs as $i => $pr): ?>
          <div class="pr-card" data-pr-index="<?= $i ?>" onclick="toggleCard(<?= $i ?>)">
            <input type="checkbox" class="card-checkbox" id="cb-<?= $i ?>" onclick="event.stopPropagation();toggleCard(<?= $i ?>)">
            <div class="pr-card-header">
              <h3><?= htmlspecialchars($pr['pr_number']) ?></h3>
              <span class="status-badge approved">Finance Approved</span>
            </div>
            <div class="pr-card-info">
              <p><i class="fas fa-calendar-alt"></i> <?= date('M d, Y', strtotime($pr['finance_approved_at'])) ?></p>
              <p><i class="fas fa-dollar-sign"></i> $<?= number_format($pr['total_amount'],2) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- TAB 2 — SPLIT PO -->
    <div class="tab-panel" id="tab-split">
      <div class="s-card">
        <h2><i class="fas fa-scissors"></i> Select a Requisition to Split</h2>
        <p style="color:#9a9ab5;font-size:.85rem;margin-bottom:20px;">Click a card to open the split form. Each line creates a separate PO assigned to a different supplier.</p>
        <?php if(empty($eligible_prs)): ?>
          <div class="empty-split"><i class="fas fa-inbox" style="font-size:2rem;color:#444;display:block;margin-bottom:10px;"></i>No approved requisitions with remaining unallocated quantity.</div>
        <?php else: ?>
          <div class="pr-split-grid">
            <?php foreach($eligible_prs as $pr):
              $rem = $pr['quantity'] - $pr['allocated_qty'];
            ?>
            <div class="pr-split-card" onclick="openSplit(<?= htmlspecialchars(json_encode([
              'id'=>$pr['id'],'pr'=>$pr['pr_number'],'mpn'=>$pr['mpn'],
              'qty'=>(int)$pr['quantity'],'alloc'=>(int)$pr['allocated_qty'],'cur'=>$pr['currency']
            ])) ?>)">
              <div class="prc-num"><?= htmlspecialchars($pr['pr_number']) ?></div>
              <div class="prc-row"><i class="fas fa-microchip" style="color:#a78bfa;width:14px"></i> <?= htmlspecialchars($pr['mpn']??'—') ?></div>
              <div class="prc-row"><i class="fas fa-boxes-stacked" style="color:#60a5fa;width:14px"></i> Total: <strong><?= number_format($pr['quantity']) ?> <?= htmlspecialchars($pr['currency']) ?></strong></div>
              <div class="prc-rem"><i class="fas fa-hourglass-half"></i> <?= number_format($rem) ?> remaining</div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<!-- Combined PO Modal -->
<div class="modal" id="combinedPOModal">
  <div class="modal-content" style="max-width:1200px;">
    <span class="modal-close" onclick="document.getElementById('combinedPOModal').style.display='none'">×</span>
    <div id="combinedPOContent"></div>
    <div class="modal-actions no-print">
      <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print PO</button>
      <button onclick="window.print()" class="btn-download"><i class="fas fa-download"></i> Download PDF</button>
    </div>
  </div>
</div>

<!-- Split PO Modal -->
<div class="modal-overlay" id="splitOverlay" onclick="closeSplitBg(event)">
  <div class="modal-box">
    <button class="modal-close-btn" onclick="closeSplit()">×</button>
    <h2><i class="fas fa-scissors"></i> Manual PO Split</h2>
    <p style="color:#9a9ab5;font-size:.85rem;margin-bottom:18px;">Add one line per supplier. Each line generates a separate Purchase Order.</p>
    <div class="pr-banner" id="split-banner"></div>
    <div class="alloc-ctr" id="alloc-ctr">
      <span>Total: <strong id="ac-tot">—</strong></span>
      <span>Allocated: <strong id="ac-alloc">—</strong></span>
      <span>This split: <strong id="ac-new">0</strong></span>
      <span>Remaining after: <strong id="ac-rem" style="color:#4ade80">—</strong></span>
    </div>
    <div id="split-lines"></div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:14px;">
      <button class="add-line-btn" onclick="addLine()"><i class="fas fa-plus"></i> Add Supplier Line</button>
      <button class="gen-btn" onclick="submitSplit()"><i class="fas fa-file-invoice"></i> Generate POs</button>
    </div>
  </div>
</div>

<script>
// ── Tab switching ─────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(b => b.addEventListener('click', () => {
  document.querySelectorAll('.tab-btn').forEach(x => x.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(x => x.classList.remove('active'));
  b.classList.add('active');
  document.getElementById('tab-' + b.dataset.tab).classList.add('active');
}));

// ── Combined PO ───────────────────────────────────
const allPRs = <?= json_encode($approved_prs) ?>;
const selectedPRs = new Set();
const token = "<?= addslashes($token ?? '') ?>";
const clientId = "<?= addslashes($client_id ?? '') ?>";

function toggleCard(i) {
  const card = document.querySelector(`[data-pr-index="${i}"]`);
  const cb = document.getElementById(`cb-${i}`);
  if (selectedPRs.has(i)) { selectedPRs.delete(i); card.classList.remove('selected'); cb.checked = false; }
  else { selectedPRs.add(i); card.classList.add('selected'); cb.checked = true; }
  updateSelUI();
}
function toggleSelectAll(cb) {
  document.querySelectorAll('.pr-card').forEach((card, i) => {
    if (cb.checked) { selectedPRs.add(i); card.classList.add('selected'); document.getElementById(`cb-${i}`).checked = true; }
    else { selectedPRs.delete(i); card.classList.remove('selected'); document.getElementById(`cb-${i}`).checked = false; }
  });
  updateSelUI();
}
function updateSelUI() {
  const n = selectedPRs.size, total = document.querySelectorAll('.pr-card').length;
  document.getElementById('selectedCount').textContent = `${n} selected`;
  document.getElementById('bulkExportBtn').disabled = n === 0;
  const sa = document.getElementById('selectAll');
  sa.checked = n === total && n > 0; sa.indeterminate = n > 0 && n < total;
}

function fallbackDesc(mpn) {
  if (!mpn) return 'Electronic Component';
  mpn = mpn.toUpperCase();
  if (mpn.includes('BAV99')) return 'Dual High-Speed Switching Diode, SOT-23';
  if (mpn.includes('LM358')) return 'Dual Operational Amplifier, SOIC-8';
  return 'Electronic Component — ' + mpn;
}

async function generateCombinedPO() {
  if (!selectedPRs.size) return;
  const btn = document.getElementById('bulkExportBtn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...'; btn.disabled = true;
  try {
    const list = Array.from(selectedPRs).map(i => allPRs[i]);
    const prnums = list.map(p => p.pr_number).join(', ');
    const today = new Date().toISOString().slice(0,10).replace(/-/g,'');
    const poNum = `PO-${today}-${String(list[0].id).padStart(4,'0')}`;
    let grand = 0, rows = '', ctr = 1;
    for (const pr of list) {
      for (const item of (pr.items || [])) {
        let desc = fallbackDesc(item.mpn);
        if (token) {
          try {
            const r = await fetch(`https://api.digikey.com/Search/v3/Products/Keyword?Keywords=${encodeURIComponent(item.mpn)}&RecordCount=1`,
              {headers:{'Authorization':`Bearer ${token}`,'X-DIGIKEY-Client-Id':clientId,'Content-Type':'application/json'}});
            if (r.ok) { const d = await r.json(); if (d.Products?.[0]?.ProductDescription) desc = d.Products[0].ProductDescription; }
          } catch(e) {}
        }
        const tot = (parseFloat(item.quantity) * parseFloat(item.unit_price)).toFixed(2);
        grand += parseFloat(tot);
        rows += `<tr><td>${ctr++}</td><td>${item.mpn||'N/A'}</td><td>${desc}</td><td>${item.quantity}</td><td>$${parseFloat(item.unit_price).toFixed(4)}</td><td>$${tot}</td></tr>`;
      }
    }
    document.getElementById('combinedPOContent').innerHTML = `
      <div class="po-document">
        <div class="po-header">
          <div class="company-info"><h1>Procurement System</h1><p>VAT Reg No: 123-456-789-000</p></div>
          <div class="po-info"><h2>PURCHASE ORDER</h2><p><strong>PO Number:</strong> ${poNum}</p><p><strong>Date:</strong> ${new Date().toLocaleDateString('en-PH')}</p><p><strong>PR(s):</strong> ${prnums}</p></div>
        </div>
        <table class="po-items">
          <thead><tr><th>#</th><th>MPN</th><th>Description</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
          <tbody>${rows || '<tr><td colspan="6">No items.</td></tr>'}
            <tr class="total-row"><td colspan="5" style="text-align:right">TOTAL:</td><td>$${grand.toFixed(2)}</td></tr>
          </tbody>
        </table>
        <div class="po-footer"><p>Generated: ${new Date().toLocaleString('en-PH')}</p></div>
      </div>`;
    document.getElementById('combinedPOModal').style.display = 'flex';
  } catch(e) { Swal.fire({ icon:'error', title:'Error', text:'Error generating PO. Try again.', confirmButtonColor:'#ef4444', background:'#2a2a3a', color:'#e0e0ff' }); }
  finally { btn.innerHTML = '<i class="fas fa-file-pdf"></i> Generate Combined PO'; btn.disabled = false; updateSelUI(); }
}

document.getElementById('combinedPOModal')?.addEventListener('click', e => { if (e.target === document.getElementById('combinedPOModal')) e.target.style.display = 'none'; });

// ── Split PO ──────────────────────────────────────
const SUPPLIERS = <?= $suppliersJson ?>;
let splitPR = null, lineCount = 0;
const esc = s => String(s??'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

function openSplit(data) {
  splitPR = typeof data==='string' ? JSON.parse(data) : data;
  splitPR.remaining = splitPR.qty - splitPR.alloc;
  lineCount = 0;
  document.getElementById('split-banner').innerHTML = `
    <div class="bi"><div class="bl">PR</div><div class="bv g">${esc(splitPR.pr)}</div></div>
    <div class="bi"><div class="bl">MPN</div><div class="bv">${esc(splitPR.mpn||'—')}</div></div>
    <div class="bi"><div class="bl">Total Qty</div><div class="bv">${splitPR.qty.toLocaleString()} ${esc(splitPR.cur)}</div></div>
    <div class="bi"><div class="bl">Allocated</div><div class="bv">${splitPR.alloc.toLocaleString()}</div></div>
    <div class="bi"><div class="bl">Remaining</div><div class="bv g">${splitPR.remaining.toLocaleString()}</div></div>`;
  document.getElementById('ac-tot').textContent   = splitPR.qty.toLocaleString();
  document.getElementById('ac-alloc').textContent = splitPR.alloc.toLocaleString();
  document.getElementById('split-lines').innerHTML = '';
  addLine(); updateCounter();
  document.getElementById('splitOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeSplit() { document.getElementById('splitOverlay').classList.remove('open'); document.body.style.overflow = ''; }
function closeSplitBg(e) { if (e.target === document.getElementById('splitOverlay')) closeSplit(); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSplit(); });

function updateCounter() {
  if (!splitPR) return;
  let n = 0;
  document.querySelectorAll('#split-lines .po-line').forEach(l => { n += parseInt(l.querySelector('[name="qty[]"]')?.value)||0; });
  const rem = splitPR.qty - splitPR.alloc - n;
  document.getElementById('ac-new').textContent = n.toLocaleString();
  const re = document.getElementById('ac-rem');
  re.textContent = rem.toLocaleString(); re.style.color = rem < 0 ? '#f87171' : '#4ade80';
}

function buildTiles(lid) {
  if (!SUPPLIERS.length) return `<div style="color:#9a9ab5;font-size:.82rem;font-style:italic;grid-column:1/-1">No active suppliers.</div>`;
  return SUPPLIERS.map(s => `<div class="sup-tile" data-id="${s.id}" data-name="${esc(s.name)}" onclick="selSup(this,'${lid}')"><div class="stck"><i class="fas fa-circle-check"></i></div><div class="stn">${esc(s.name)}</div></div>`).join('');
}

function addLine() {
  if (!splitPR) return;
  lineCount++;
  const id = 'sl-' + lineCount;
  document.getElementById('split-lines').insertAdjacentHTML('beforeend', `
    <div class="po-line" id="${id}">
      <div class="lhdr">
        <span class="lnum"><i class="fas fa-truck"></i> Line ${lineCount}</span>
        <button class="ldel" onclick="rmLine('${id}')"><i class="fas fa-trash"></i> Remove</button>
      </div>
      <label style="display:block;font-size:.78rem;color:#9a9ab5;margin-bottom:7px;">Supplier * <span id="${id}-lbl" style="color:#4ade80;font-weight:700;margin-left:6px;"></span></label>
      <div class="sup-grid">${buildTiles(id)}</div>
      <input type="hidden" id="${id}-sid" name="supplier_id[]"/>
      <div class="line-row">
        <div class="fld"><label>Quantity *</label><input type="number" name="qty[]" min="1" placeholder="e.g. 100" oninput="updateCounter()"/></div>
        <div class="fld"><label>Unit Price (${esc(splitPR.cur)}) *</label><input type="number" name="price[]" min="0.0001" step="0.0001" placeholder="0.0000"/></div>
        <div class="fld"><label>Delivery Date *</label><input type="date" name="date[]"/></div>
      </div>
    </div>`);
}

function selSup(tile, lid) {
  tile.closest('.po-line').querySelectorAll('.sup-tile').forEach(t => t.classList.remove('sel'));
  tile.classList.add('sel');
  document.getElementById(lid+'-sid').value = tile.dataset.id;
  document.getElementById(lid+'-lbl').textContent = '— ' + tile.dataset.name;
}

function rmLine(id) { document.getElementById(id)?.remove(); updateCounter(); }

async function submitSplit() {
  if (!splitPR) return;
  const lines = [...document.querySelectorAll('#split-lines .po-line')];
  if (!lines.length) { Swal.fire({icon:'warning',title:'Add at least one supplier line'}); return; }
  const payload = { pr_id: parseInt(splitPR.id), lines: [] };
  let newQty = 0;
  for (const l of lines) {
    const sid   = l.querySelector('[name="supplier_id[]"]').value;
    const qty   = parseInt(l.querySelector('[name="qty[]"]').value)||0;
    const price = parseFloat(l.querySelector('[name="price[]"]').value)||0;
    const date  = l.querySelector('[name="date[]"]').value;
    if (!sid)           { Swal.fire({icon:'warning',title:'Select a supplier for every line'}); return; }
    if (qty<1||price<=0||!date) { Swal.fire({icon:'warning',title:'Fill qty, price and delivery date for every line'}); return; }
    newQty += qty;
    payload.lines.push({supplier_id:parseInt(sid),quantity:qty,unit_price:price,delivery_date:date});
  }
  if (splitPR.alloc + newQty > splitPR.qty) { Swal.fire({icon:'warning',title:`Total exceeds requested qty (${splitPR.qty})`}); return; }
  const conf = await Swal.fire({title:`Generate ${payload.lines.length} PO(s)?`,html:`<strong>${splitPR.pr}</strong> — ${splitPR.mpn}`,icon:'question',showCancelButton:true,confirmButtonColor:'#22c55e',confirmButtonText:'Generate'});
  if (!conf.isConfirmed) return;
  const res  = await fetch('../PHP/buyer_po.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  const data = await res.json();
  if (data.success) {
    closeSplit();
    await Swal.fire({icon:'success',title:'POs Generated!',html:data.po_numbers.map(n=>`<strong>${n}</strong>`).join('<br>'),confirmButtonColor:'#22c55e'});
    location.reload();
  } else Swal.fire({icon:'error',title:'Error',text:data.message});
}
</script>
</body>
</html>
