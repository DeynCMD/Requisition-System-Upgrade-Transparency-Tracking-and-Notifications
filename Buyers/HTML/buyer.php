<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'BUYER') {
    header("Location: ../../Admin/HTML/ZE-Electronics.php"); exit;
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
require_once '../../Admin/PHP/db.php';
require_once '../../Admin/PHP/currency_config.php';
require_once '../../Admin/PHP/send_pr_status_email.php';

// ── Mark as purchased ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_purchase'])) {
    $id = (int)$_POST['request_id'];
    $stmt = $conn->prepare("SELECT pr_number, requestor_name, user_id FROM purchase_requests WHERE id=?");
    $stmt->bind_param("i", $id); $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if ($req) {
        $conn->query("UPDATE purchase_requests SET buyer_status='purchased', updated_at=NOW() WHERE id=$id");
        $performed = $_SESSION['username'] ?? 'Buyer';
        $desc = "Purchased {$req['pr_number']}";
        $stmt2 = $conn->prepare("INSERT INTO activity_logs (activity_type, performed_by, description, created_at) VALUES ('purchase', ?, ?, NOW())");
        $stmt2->bind_param("ss", $performed, $desc); $stmt2->execute(); $stmt2->close();
        if (!empty($req['user_id'])) {
            $er = $conn->query("SELECT email FROM users WHERE id={$req['user_id']}")->fetch_assoc();
            if (!empty($er['email']) && function_exists('sendPRStatusEmail'))
                sendPRStatusEmail($conn, $req['pr_number'], 'purchased', $er['email'], $req['requestor_name']);
        }
    }
    header("Location: buyer.php?done=" . urlencode($req['pr_number'] ?? '')); exit;
}

// ── Fetch finance-approved PRs with winning bid info ──
$requests = $conn->query("
    SELECT
        pr.id, pr.pr_number, pr.mpn, pr.manufacturer, pr.quantity,
        pr.unit_price AS ref_price, pr.currency,
        pr.requestor_name, pr.created_at, pr.category, pr.subcategory,
        pr.selected_distributor_text, pr.buyer_status,
        sb.id         AS bid_id,
        sb.unit_price AS bid_price,
        sb.delivery_date AS bid_delivery,
        sb.notes      AS bid_notes,
        s.id          AS supplier_id,
        s.name        AS supplier_name,
        s.email       AS supplier_email,
        po.id         AS po_id,
        po.po_number
    FROM purchase_requests pr
    LEFT JOIN supplier_bids sb  ON sb.pr_id = pr.id AND sb.status = 'selected'
    LEFT JOIN suppliers s       ON s.id = sb.supplier_id
    LEFT JOIN purchase_orders po ON po.pr_id = pr.id AND po.status != 'Cancelled'
    WHERE pr.finance_status = 'approved'
      AND pr.buyer_status   = 'pending_payment'
    ORDER BY pr.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// ── PRs awaiting winner selection (admin approved, supplier bidding open) ──
$bidding_requests = $conn->query("
    SELECT
        pr.id, pr.pr_number, pr.mpn, pr.manufacturer, pr.category, pr.subcategory,
        pr.quantity, pr.currency, pr.urgency, pr.requestor_name, pr.created_at,
        (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id AND sb.status = 'pending') AS pending_bids,
        (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id) AS total_bids
    FROM purchase_requests pr
    WHERE pr.status         = 'approved'
      AND pr.finance_status = 'pending'
    ORDER BY pr.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Purchase Requests — Buyer</title>
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
    .alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:.93rem;display:flex;align-items:center;gap:10px;}
    .alert.success{background:rgba(34,197,94,.12);border:1px solid #22c55e;color:#4ade80;}
    /* PR cards */
    .pr-card{background:#2a2a3a;border-radius:16px;padding:22px 26px;margin-bottom:20px;box-shadow:0 4px 16px rgba(0,0,0,.3);border:1px solid #444;transition:.2s;}
    .pr-card:hover{border-color:#555;}
    .pr-card.has-winner{border-left:4px solid #22c55e;}
    .pr-card.no-winner{border-left:4px solid #fbbf24;}
    .pr-card.has-po{border-left:4px solid #60a5fa;opacity:.85;}
    .pr-header{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:16px;}
    .pr-num{font-size:1.1rem;font-weight:800;color:#4ade80;}
    .pr-mpn{font-size:1.25rem;font-weight:700;color:#e0e0ff;margin-top:2px;}
    .pr-meta{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:14px;}
    .pr-meta-item{display:flex;align-items:center;gap:6px;font-size:.85rem;color:#9a9ab5;}
    .pr-meta-item i{width:14px;color:#555;}
    .pr-meta-item strong{color:#e0e0ff;}
    /* winning bid box */
    .win-bid-box{background:#1a2e1a;border:1px solid #22c55e44;border-radius:12px;padding:14px 18px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:20px;align-items:center;}
    .wbb-label{font-size:.7rem;color:#9a9ab5;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;}
    .wbb-val{font-size:.92rem;font-weight:700;color:#4ade80;}
    .wbb-val.neutral{color:#e0e0ff;}
    /* no bid notice */
    .no-bid-notice{background:#2a2a1a;border:1px solid #fbbf2444;border-radius:10px;padding:12px 16px;font-size:.85rem;color:#fbbf24;margin-bottom:14px;}
    /* po already notice */
    .po-notice{background:#1a1a2e;border:1px solid #60a5fa44;border-radius:10px;padding:12px 16px;font-size:.85rem;color:#60a5fa;margin-bottom:14px;}
    /* badges */
    .badge{display:inline-flex;padding:4px 10px;border-radius:20px;font-size:.73rem;font-weight:700;}
    .badge.green{background:#dcfce7;color:#15803d;}
    .badge.yellow{background:#fef9c3;color:#854d0e;}
    .badge.blue{background:#dbeafe;color:#1d4ed8;}
    /* buttons */
    .btn-generate{background:#22c55e;color:#fff;border:none;padding:10px 20px;border-radius:10px;cursor:pointer;font-weight:700;font-size:.9rem;font-family:inherit;display:inline-flex;align-items:center;gap:8px;transition:.2s;}
    .btn-generate:hover{background:#16a34a;}
    .btn-purchase{background:#3b82f6;color:#fff;border:none;padding:10px 20px;border-radius:10px;cursor:pointer;font-weight:700;font-size:.9rem;font-family:inherit;display:inline-flex;align-items:center;gap:8px;transition:.2s;margin-left:8px;}
    .btn-purchase:hover{background:#2563eb;}
    .actions-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:4px;}
    /* search */
    .search-bar{background:#2a2a3a;border-radius:12px;padding:0 0 16px;margin-bottom:4px;}
    .search-input{width:100%;background:#1e1e2e;border:1px solid #444;color:#e0e0ff;padding:11px 14px 11px 40px;border-radius:10px;font-size:.93rem;font-family:inherit;outline:none;}
    .search-input:focus{border-color:#22c55e;}
    .search-wrap{position:relative;}
    .search-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#555;}
    .count-bar{font-size:.9rem;color:#9a9ab5;margin-bottom:18px;}
    .count-bar strong{color:#e0e0ff;}
    .empty-state{text-align:center;padding:60px 20px;color:#9a9ab5;}
    .empty-state i{font-size:3rem;color:#333;display:block;margin-bottom:16px;}
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
      <li><a href="buyer.php" class="active"><i class="fas fa-shopping-cart"></i> Purchase Requests</a></li>
      <li><a href="buyer_history.php"><i class="fas fa-history"></i> History</a></li>
      <li><a href="buyer_export.php"><i class="fas fa-file-export"></i> Export PO</a></li>
    </ul></nav>
    <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
  </aside>

  <main class="main-content">
    <h1 class="page-title">Purchase Requests</h1>
    <p class="page-sub">Finance-approved requests ready for purchase. Generate the PO once a winning bid has been selected by Admin.</p>

    <?php if (!empty($bidding_requests)): ?>
      <section style="margin-bottom:32px;">
        <h2 style="color:#fbbf24;font-size:1.25rem;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
          <i class="fas fa-gavel"></i> Awaiting Winning Bid Selection
          <span style="font-size:.85rem;color:#9a9ab5;font-weight:400;">(<?= count($bidding_requests) ?> PR<?= count($bidding_requests) !== 1 ? 's' : '' ?>)</span>
        </h2>
        <div style="display:flex;flex-direction:column;gap:14px;">
          <?php foreach ($bidding_requests as $b):
            $sym = CURRENCY_SYMBOLS[$b['currency']] ?? ($b['currency'].' ');
            $hasBids = ((int)$b['total_bids']) > 0;
          ?>
          <div class="pr-card no-winner" style="border-left-color:#fbbf24;">
            <div class="pr-header">
              <div>
                <div class="pr-num"><?= htmlspecialchars($b['pr_number']) ?></div>
                <div class="pr-mpn"><?= htmlspecialchars($b['mpn'] ?? '—') ?></div>
              </div>
              <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start;">
                <span class="badge yellow"><i class="fas fa-clock" style="margin-right:4px"></i>Awaiting Bid Selection</span>
                <span class="badge" style="background:#1e1e2e;color:#9a9ab5;border:1px solid #444;">
                  <?= (int)$b['total_bids'] ?> bid<?= ((int)$b['total_bids']) !== 1 ? 's' : '' ?>
                  <?= ((int)$b['pending_bids']) > 0 ? ' ('.(int)$b['pending_bids'].' pending)' : '' ?>
                </span>
              </div>
            </div>
            <div class="pr-meta">
              <?php if ($b['manufacturer']): ?>
                <div class="pr-meta-item"><i class="fas fa-industry"></i><?= htmlspecialchars($b['manufacturer']) ?></div>
              <?php endif; ?>
              <div class="pr-meta-item"><i class="fas fa-tag"></i><?= htmlspecialchars($b['category']??'—') ?><?= !empty($b['subcategory']) ? ' / '.htmlspecialchars($b['subcategory']) : '' ?></div>
              <div class="pr-meta-item"><i class="fas fa-boxes-stacked"></i>Qty: <strong><?= number_format($b['quantity']) ?> units</strong></div>
              <div class="pr-meta-item"><i class="fas fa-coins"></i><?= htmlspecialchars($b['currency']) ?> (<?= $sym ?>)</div>
              <div class="pr-meta-item"><i class="fas fa-user"></i><?= htmlspecialchars($b['requestor_name']) ?></div>
              <div class="pr-meta-item"><i class="fas fa-calendar"></i><?= date('M d, Y', strtotime($b['created_at'])) ?></div>
            </div>
            <div class="actions-row">
              <?php if ($hasBids): ?>
                <a class="btn-generate" href="buyer_select_winner.php?pr_id=<?= (int)$b['id'] ?>">
                  <i class="fas fa-gavel"></i> Select Winner
                </a>
              <?php else: ?>
                <button class="btn-generate" disabled style="background:#444;color:#888;cursor:not-allowed;">
                  <i class="fas fa-hourglass-half"></i> No bids yet
                </button>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if(isset($_GET['done'])): ?>
    <div class="alert success">
      <i class="fas fa-check-circle"></i>
      <strong><?= htmlspecialchars($_GET['done']) ?></strong> marked as purchased.
    </div>
    <?php endif; ?>

    <!-- Search -->
    <div class="search-wrap" style="margin-bottom:16px;">
      <i class="fas fa-search"></i>
      <input class="search-input" type="text" id="searchInput" placeholder="Search by PR #, MPN, supplier, requestor…" oninput="filterCards()"/>
    </div>
    <div class="count-bar" id="countBar"></div>

    <div id="pr-container">
    <?php if(empty($requests)): ?>
      <div class="empty-state">
        <i class="fas fa-check-circle" style="color:#22c55e"></i>
        <p>No pending purchase requests. All approved items have been processed.</p>
      </div>
    <?php else: foreach($requests as $r):
      $sym      = CURRENCY_SYMBOLS[$r['currency']] ?? $r['currency'].' ';
      $total    = $r['quantity'] * $r['ref_price'];
      $hasBid   = !empty($r['bid_id']);
      $hasPO    = !empty($r['po_id']);
      $cardCls  = $hasPO ? 'has-po' : ($hasBid ? 'has-winner' : 'no-winner');
      // PHP equiv of bid price
      $phpBidEquiv = '';
      if ($hasBid && $r['currency'] !== 'PHP') {
          $phpBidEquiv = ' <span style="font-size:.78rem;color:#9a9ab5">≈ ₱'.number_format(to_php((float)$r['bid_price'], $r['currency']), 4).'</span>';
      }
    ?>
    <div class="pr-card <?= $cardCls ?>"
         data-search="<?= strtolower(htmlspecialchars($r['pr_number'].' '.$r['mpn'].' '.($r['supplier_name']??'').' '.$r['requestor_name'].' '.$r['category'])) ?>">

      <div class="pr-header">
        <div>
          <div class="pr-num"><?= htmlspecialchars($r['pr_number']) ?></div>
          <div class="pr-mpn"><?= htmlspecialchars($r['mpn'] ?? '—') ?></div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start;">
          <?php if ($hasPO): ?>
            <span class="badge blue"><i class="fas fa-file-invoice" style="margin-right:4px"></i>PO <?= htmlspecialchars($r['po_number']) ?></span>
          <?php elseif ($hasBid): ?>
            <span class="badge green"><i class="fas fa-trophy" style="margin-right:4px"></i>Winner Selected</span>
          <?php else: ?>
            <span class="badge yellow"><i class="fas fa-clock" style="margin-right:4px"></i>Awaiting Bid Selection</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="pr-meta">
        <?php if ($r['manufacturer']): ?>
          <div class="pr-meta-item"><i class="fas fa-industry"></i><?= htmlspecialchars($r['manufacturer']) ?></div>
        <?php endif; ?>
        <div class="pr-meta-item"><i class="fas fa-tag"></i><?= htmlspecialchars($r['category']??'—') ?><?= $r['subcategory'] ? ' / '.htmlspecialchars($r['subcategory']) : '' ?></div>
        <div class="pr-meta-item"><i class="fas fa-boxes-stacked"></i>Qty: <strong><?= number_format($r['quantity']) ?> units</strong></div>
        <div class="pr-meta-item"><i class="fas fa-coins"></i>Currency: <strong><?= htmlspecialchars($r['currency']) ?> (<?= $sym ?>)</strong></div>
        <div class="pr-meta-item"><i class="fas fa-user"></i><?= htmlspecialchars($r['requestor_name']) ?></div>
        <div class="pr-meta-item"><i class="fas fa-calendar"></i><?= date('M d, Y', strtotime($r['created_at'])) ?></div>
      </div>

      <?php if ($hasPO): ?>
        <div class="po-notice">
          <i class="fas fa-file-invoice" style="margin-right:6px"></i>
          Purchase Order <strong><?= htmlspecialchars($r['po_number']) ?></strong> has been generated.
          Mark as purchased once the items arrive.
        </div>
        <div class="actions-row">
          <button class="btn-purchase"
            onclick="confirmPurchase(<?= $r['id'] ?>,'<?= htmlspecialchars($r['pr_number']) ?>','<?= $sym.number_format($r['quantity']*$r['bid_price'],2) ?>')">
            <i class="fas fa-box-open"></i> Mark as Purchased
          </button>
        </div>

      <?php elseif ($hasBid): ?>
        <div class="win-bid-box">
          <div>
            <div class="wbb-label">Winning Supplier</div>
            <div class="wbb-val"><?= htmlspecialchars($r['supplier_name']) ?></div>
            <?php if ($r['supplier_email']): ?>
              <div style="font-size:.75rem;color:#9a9ab5"><?= htmlspecialchars($r['supplier_email']) ?></div>
            <?php endif; ?>
          </div>
          <div>
            <div class="wbb-label">Bid Unit Price</div>
            <div class="wbb-val"><?= $sym ?><?= number_format($r['bid_price'], 4) ?><?= $phpBidEquiv ?></div>
          </div>
          <div>
            <div class="wbb-label">Total</div>
            <div class="wbb-val"><?= $sym ?><?= number_format($r['bid_price'] * $r['quantity'], 2) ?>
              <?php if ($r['currency'] !== 'PHP'): ?>
                <span style="font-size:.78rem;color:#9a9ab5"> ≈ ₱<?= number_format(to_php($r['bid_price'] * $r['quantity'], $r['currency']), 2) ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <div class="wbb-label">Delivery Date</div>
            <div class="wbb-val neutral"><?= $r['bid_delivery'] ? date('M d, Y', strtotime($r['bid_delivery'])) : '—' ?></div>
          </div>
          <?php if ($r['bid_notes']): ?>
          <div style="flex-basis:100%">
            <div class="wbb-label">Supplier Notes</div>
            <div style="font-size:.83rem;color:#9a9ab5;font-style:italic"><?= htmlspecialchars($r['bid_notes']) ?></div>
          </div>
          <?php endif; ?>
        </div>
        <div class="actions-row">
          <button class="btn-generate"
            onclick="generatePO(<?= $r['id'] ?>,'<?= htmlspecialchars($r['pr_number']) ?>',<?= $r['supplier_id'] ?>,'<?= htmlspecialchars($r['supplier_name']) ?>',<?= $r['bid_price'] ?>,<?= $r['quantity'] ?>,'<?= $r['bid_delivery'] ?>','<?= htmlspecialchars($r['currency']) ?>')">
            <i class="fas fa-file-invoice"></i> Generate PO
          </button>
        </div>

      <?php else: ?>
        <div class="no-bid-notice">
          <i class="fas fa-clock" style="margin-right:6px"></i>
          No winning bid selected yet. Admin needs to review bids and select a winner before you can generate a PO.
        </div>
      <?php endif; ?>

    </div>
    <?php endforeach; endif; ?>
    </div>
  </main>
</div>

<!-- Purchase form -->
<form id="purchaseForm" method="POST" style="display:none">
  <input type="hidden" name="complete_purchase" value="1"/>
  <input type="hidden" name="request_id" id="purchaseId"/>
</form>

<script>
const API = '../../Admin/PHP/currency_config.php';

function filterCards(){
  const q = document.getElementById('searchInput').value.toLowerCase();
  let visible = 0;
  document.querySelectorAll('.pr-card').forEach(c=>{
    const match = !q || c.dataset.search.includes(q);
    c.style.display = match ? '' : 'none';
    if(match) visible++;
  });
  document.getElementById('countBar').innerHTML = `Showing <strong>${visible}</strong> of <strong><?= count($requests) ?></strong> requests`;
}
document.getElementById('countBar').innerHTML = `<strong><?= count($requests) ?></strong> pending purchase request<?= count($requests)!==1?'s':'' ?>`;

// ── Generate PO ──────────────────────────────────
async function generatePO(prId, prNum, suppId, suppName, bidPrice, qty, deliveryDate, currency){
  const SYMBOLS = {PHP:'₱',USD:'$',EUR:'€',JPY:'¥',GBP:'£',SGD:'S$',CNY:'¥'};
  const sym   = SYMBOLS[currency] || currency+' ';
  const total = (bidPrice * qty).toFixed(2);

  const r = await Swal.fire({
    title: 'Generate Purchase Order?',
    html: `<div style="text-align:left;font-size:.93rem;line-height:2.2">
      <strong>PR #:</strong> ${prNum}<br>
      <strong>Supplier:</strong> ${suppName}<br>
      <strong>Unit Price:</strong> ${sym}${Number(bidPrice).toFixed(4)}<br>
      <strong>Quantity:</strong> ${Number(qty).toLocaleString()} units<br>
      <strong>Total:</strong> ${sym}${Number(total).toLocaleString()}<br>
      <strong>Delivery:</strong> ${deliveryDate || '—'}
    </div>`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#22c55e',
    confirmButtonText: '<i class="fas fa-file-invoice"></i> Generate PO',
    cancelButtonText: 'Cancel'
  });
  if (!r.isConfirmed) return;

  try {
    const res = await fetch('../../Buyers/PHP/buyer_po.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        action: 'create_split',
        pr_id:  prId,
        lines:  [{
          supplier_id:   suppId,
          quantity:      qty,
          unit_price:    bidPrice,
          delivery_date: deliveryDate
        }]
      })
    });
    const d = await res.json();
    if (d.success) {
      await Swal.fire({
        icon: 'success',
        title: 'PO Generated!',
        html: `Purchase Order <strong>${d.po_numbers?.[0] ?? ''}</strong> has been created for <strong>${suppName}</strong>.`,
        confirmButtonColor: '#22c55e'
      });
      location.reload();
    } else {
      Swal.fire({icon:'error', title:'Failed', text: d.message, confirmButtonColor:'#ef4444'});
    }
  } catch(e) {
    Swal.fire({icon:'error', title:'Error', text:'Network error. Please try again.', confirmButtonColor:'#ef4444'});
  }
}

// ── Mark as purchased ────────────────────────────
async function confirmPurchase(id, prNum, total){
  const r = await Swal.fire({
    title: `Mark ${prNum} as Purchased?`,
    html: `<div style="text-align:left;font-size:.93rem;line-height:2">
      <strong>Total:</strong> ${total}<br>
      <span style="color:#fbbf24;font-size:.85rem"><i class="fas fa-triangle-exclamation"></i>
      This marks the item as purchased and notifies the requestor.</span>
    </div>`,
    icon: 'question', showCancelButton: true,
    confirmButtonColor: '#22c55e', confirmButtonText: '<i class="fas fa-check"></i> Confirm',
    cancelButtonText: 'Cancel'
  });
  if(!r.isConfirmed) return;
  document.getElementById('purchaseId').value = id;
  document.getElementById('purchaseForm').submit();
}
</script>
</body>
</html>
