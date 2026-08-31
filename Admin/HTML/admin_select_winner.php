<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header('Location: ZE-Electronics.php');
    exit;
}
require_once '../../Admin/PHP/db.php';

$prId = (int)($_GET['pr_id'] ?? 0);
if ($prId <= 0) {
    header('Location: Pending-approvals.php');
    exit;
}

// Load the PR and verify it is in the bidding stage.
$pr = $conn->query("
    SELECT id, pr_number, mpn, manufacturer, category, subcategory, quantity, currency, reason, urgency, required_by, status
    FROM purchase_requests
    WHERE id = $prId AND status = 'approved'
")->fetch_assoc();

if (!$pr) {
    $_SESSION['flash_error'] = 'This PR is not in the bidding stage.';
    header('Location: Pending-approvals.php');
    exit;
}

// Load bids for this PR.
$bids = $conn->query("
    SELECT sb.id, sb.unit_price, sb.delivery_date, sb.notes, sb.status, sb.created_at,
           s.id AS supplier_id, s.name AS supplier_name, s.email AS supplier_email
    FROM supplier_bids sb
    JOIN suppliers s ON s.id = sb.supplier_id
    WHERE sb.pr_id = $prId
    ORDER BY sb.unit_price ASC, sb.created_at ASC
")->fetch_all(MYSQLI_ASSOC);

$hasBid = !empty($bids);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Select Winning Bid — <?= htmlspecialchars($pr['pr_number']) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: "Segoe UI", sans-serif; background: #12121a; color: #e0e0ff; margin: 0; padding: 24px; }
    .container { max-width: 1100px; margin: 0 auto; }
    h1 { color: #22c55e; font-size: 1.6rem; margin-bottom: 8px; }
    .pr-strip { background: #1e1e2e; border: 1px solid #2d2d44; border-radius: 12px; padding: 18px 22px; margin-bottom: 22px; }
    .pr-strip .row { display: flex; flex-wrap: wrap; gap: 16px 32px; margin-top: 8px; }
    .pr-strip .field { font-size: .85rem; }
    .pr-strip .field label { display: block; color: #9a9ab5; font-size: .72rem; text-transform: uppercase; margin-bottom: 2px; }
    .pr-strip .field .val { color: #e0e0ff; font-weight: 600; }
    table { width: 100%; border-collapse: collapse; background: #1e1e2e; border-radius: 12px; overflow: hidden; }
    th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #2d2d44; font-size: .88rem; }
    th { background: #1a1a2e; color: #9a9ab5; font-size: .72rem; text-transform: uppercase; letter-spacing: .4px; }
    tr.selected-winner td { background: rgba(34, 197, 94, .08); }
    .actions { display: flex; gap: 10px; }
    .btn { padding: 9px 16px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: .85rem; }
    .btn-primary { background: #22c55e; color: #fff; }
    .btn-primary:hover { background: #16a34a; }
    .btn-primary:disabled { background: #444; color: #888; cursor: not-allowed; }
    .btn-back { background: #2d2d44; color: #e0e0ff; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 8px; font-weight: 600; font-size: .85rem; }
    .empty-state { background: #1e1e2e; border: 1px dashed #2d2d44; border-radius: 12px; padding: 48px; text-align: center; color: #9a9ab5; }
    .empty-state i { font-size: 3rem; color: #444; margin-bottom: 14px; display: block; }
    .reason-box { background: #1e1e2e; border-left: 3px solid #4a4a6a; padding: 10px 14px; margin-bottom: 18px; font-size: .85rem; color: #9a9ab5; font-style: italic; }
  </style>
</head>
<body>
<div class="container">
  <a class="btn-back" href="Pending-approvals.php" style="margin-bottom: 16px;"><i class="fas fa-arrow-left"></i> Back to Pending Approvals</a>
  <h1><i class="fas fa-gavel"></i> Select Winning Bid</h1>
  <p style="color:#9a9ab5;margin-top:4px;">Choose the winning supplier bid. After selection, this PR moves to Finance for review.</p>

  <div class="pr-strip">
    <div style="font-size:.7rem;color:#9a9ab5;text-transform:uppercase;">PR Number</div>
    <div style="font-size:1.15rem;font-weight:800;color:#22c55e;"><?= htmlspecialchars($pr['pr_number']) ?></div>
    <div class="row">
      <div class="field"><label>MPN</label><div class="val"><?= htmlspecialchars($pr['mpn'] ?? '—') ?></div></div>
      <div class="field"><label>Manufacturer</label><div class="val"><?= htmlspecialchars($pr['manufacturer'] ?? '—') ?></div></div>
      <div class="field"><label>Category</label><div class="val"><?= htmlspecialchars($pr['category'] ?? '—') ?></div></div>
      <div class="field"><label>Quantity</label><div class="val"><?= (int)$pr['quantity'] ?> units</div></div>
      <div class="field"><label>Currency</label><div class="val"><?= htmlspecialchars($pr['currency']) ?></div></div>
      <div class="field"><label>Status</label><div class="val" style="color:#fbbf24;">Awaiting Supplier Bids</div></div>
    </div>
  </div>

  <?php if ($pr['reason']): ?>
    <div class="reason-box"><i class="fas fa-quote-left" style="color:#555;margin-right:6px"></i><?= htmlspecialchars($pr['reason']) ?></div>
  <?php endif; ?>

  <?php if (!$hasBid): ?>
    <div class="empty-state">
      <i class="fas fa-inbox"></i>
      <p>No supplier bids have been submitted yet.</p>
      <p style="font-size:.82rem;">Suppliers must submit at least one bid before a winner can be selected.</p>
    </div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Supplier</th>
          <th>Unit Price</th>
          <th>Delivery Date</th>
          <th>Notes</th>
          <th>Bid Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($bids as $bid): ?>
        <tr>
          <td><strong><?= htmlspecialchars($bid['supplier_name']) ?></strong><br><span style="font-size:.74rem;color:#9a9ab5;"><?= htmlspecialchars($bid['supplier_email']) ?></span></td>
          <td><strong><?= htmlspecialchars($pr['currency']) ?> <?= number_format((float)$bid['unit_price'], 4) ?></strong></td>
          <td><?= htmlspecialchars($bid['delivery_date']) ?></td>
          <td style="font-size:.82rem;color:#9a9ab5;"><?= htmlspecialchars($bid['notes'] ?? '—') ?></td>
          <td>
            <?php
              $sc = ['pending' => '#fbbf24', 'selected' => '#4ade80', 'rejected' => '#f87171'][$bid['status']] ?? '#9a9ab5';
            ?>
            <span style="background:<?= $sc ?>22;color:<?= $sc ?>;border:1px solid <?= $sc ?>44;font-size:.72rem;font-weight:700;padding:2px 9px;border-radius:20px;text-transform:uppercase;">
              <?= htmlspecialchars($bid['status']) ?>
            </span>
          </td>
          <td>
            <?php if ($bid['status'] === 'pending'): ?>
              <button class="btn btn-primary" onclick="selectWinner(<?= (int)$bid['id'] ?>, '<?= htmlspecialchars($bid['supplier_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($pr['pr_number'], ENT_QUOTES) ?>')">
                <i class="fas fa-check"></i> Select as Winner
              </button>
            <?php else: ?>
              <span style="color:#9a9ab5;font-size:.82rem;">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
async function selectWinner(bidId, supplierName, prNumber) {
  const result = await Swal.fire({
    title: 'Select this bid as winner?',
    html: `
      <p>You are about to select <strong>${supplierName}</strong> as the winning bid for <strong>${prNumber}</strong>.</p>
      <p style="font-size:.85rem;color:#9a9ab5;margin-top:10px;">This will:
        <br>• mark all other bids as rejected
        <br>• move this PR to Finance for review
        <br>• notify the requestor by email</p>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#22c55e',
    cancelButtonColor: '#444',
    confirmButtonText: 'Yes, select winner',
    cancelButtonText: 'Cancel',
  });
  if (!result.isConfirmed) return;

  const fd = new URLSearchParams({ pr_id: <?= (int)$prId ?>, winning_bid_id: bidId });
  try {
    const res = await fetch('../PHP/select_winning_bid.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: fd,
    });
    const data = await res.json();
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Winner selected',
        text: data.message,
        confirmButtonColor: '#22c55e',
      }).then(() => { window.location.href = 'Pending-approvals.php'; });
    } else {
      Swal.fire({ icon: 'error', title: 'Failed', text: data.message, confirmButtonColor: '#ef4444' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Network error', text: err.message, confirmButtonColor: '#ef4444' });
  }
}
</script>
</body>
</html>
