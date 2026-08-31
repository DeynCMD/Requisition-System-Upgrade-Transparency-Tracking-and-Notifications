<?php
session_start();

// Check if user is logged in and is a REQUESTOR
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'REQUESTOR') {
  header("Location: ../../HTML/ZE-Electronics.php?error=access_denied");
  exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ze_electronic");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$fullname = $_SESSION['fullname'];

$stmt = $conn->prepare("
    SELECT id, pr_number, request_date, category, mpn, quantity, unit_price, currency,
           status, buyer_status, withdrawal_status, subcategory
    FROM purchase_requests 
    WHERE requestor_name = ? 
    ORDER BY request_date DESC
");
$stmt->bind_param("s", $fullname);
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <title>My Requests — Procurement System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="../CSS/my-requests.css"/>
<style>
.wd-btn{margin-top:10px;display:inline-flex;align-items:center;gap:8px;padding:9px 16px;background:#b45309;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.87rem;font-weight:600;transition:.2s;}
.wd-btn:hover{background:#92400e;}
.wd-badge{display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:6px 13px;border-radius:20px;font-size:.82rem;font-weight:600;}
.wd-badge.pending{background:#fef3c7;color:#92400e;}
.wd-badge.approved{background:#dcfce7;color:#15803d;}
.wd-badge.rejected{background:#fee2e2;color:#991b1b;}
</style>
</head>

<body>
<div class="container">

<aside class="sidebar">
  <div class="profile">
    <img src="../Assets/Avatar.jpg">
    <span class="role">REQUESTOR</span>
  </div>

  <nav class="nav-menu">
    <ul>
      <li><a href="requestor-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
      <li><a href="create-request.html"><i class="fas fa-plus-circle"></i> Create Request</a></li>
      <li><a href="history.html"><i class="fas fa-clock"></i> History</a></li>
      <li><a href="my-requests.php" class="active"><i class="fas fa-list-check"></i> My Requests</a></li>
    </ul>
  </nav>

  <button onclick="window.location.href='../../Admin/PHP/logout.php'" class="logout-btn">LOGOUT</button>
</aside>

<main class="main-content">

<div class="page-header">
  <h1>My Requests</h1>
  <div class="filter-controls">
    <select id="statusFilter" class="filter-select">
      <option value="all">All Statuses</option>
      <option value="pending">Pending</option>
      <option value="approved">Approved</option>
      <option value="purchasing">Purchasing</option>
      <option value="completed">Completed</option>
      <option value="rejected">Rejected</option>
    </select>
    <input type="date" class="date-filter" id="dateFilter">
    <button class="clear-filter-btn" id="clearFilters"><i class="fas fa-times"></i> Clear</button>
  </div>
</div>

<div class="history-card">
<div class="timeline">

<?php foreach($requests as $req): ?>
<?php
$status = strtolower($req['status']);

switch ($status) {
  case 'pending':    $bar='pending';    $label='Pending';    $icon='clock';         $color='#3b82f6'; break;
  case 'approved':   $bar='approved';   $label='Approved';   $icon='check';         $color='#fbbf24'; break;
  case 'purchasing': $bar='purchasing'; $label='Purchasing'; $icon='shopping-cart'; $color='#f97316'; break;
  case 'completed':  $bar='completed';  $label='Completed';  $icon='check-double';  $color='#4ade80'; break;
  default:           $bar='rejected';   $label='Rejected';   $icon='times';         $color='#f87171';
}
?>

<div class="timeline-item" data-id="<?= $req['id'] ?>" data-status="<?= $status ?>" data-date="<?= $req['request_date'] ?>">
  <div class="timeline-icon" style="background:<?= $color ?>">
    <i class="fas fa-<?= $icon ?>"></i>
  </div>

  <div class="timeline-content clickable">
    <div class="timeline-header">
      <h3><?= $req['pr_number'] ?></h3>
      <span class="time"><?= date("M d, Y", strtotime($req['request_date'])) ?></span>
    </div>

    <p><strong>Item:</strong> <?= $req['category'] ?> - <?= $req['mpn'] ?></p>
    <p><strong>Quantity:</strong> <?= $req['quantity'] ?> × <?= $req['currency'] ?> <?= number_format($req['unit_price'],2) ?></p>

    <div class="progress-container">
      <div class="progress-bar <?= $bar ?>">
        <span class="progress-label"><?= $label ?></span>
      </div>
    </div>

    <span class="view-details"><i class="fas fa-eye"></i> View Details</span>

    <?php
    $canWithdraw = $req['withdrawal_status'] === 'none' && (
        strtolower($req['status']) === 'approved' ||
        $req['buyer_status'] === 'purchased'
    );
    if($canWithdraw): ?>
    <button class="wd-btn" onclick="reportUnavailable(<?= $req['id'] ?>,'<?= htmlspecialchars($req['pr_number']) ?>')">
      <i class="fas fa-triangle-exclamation"></i> Item Unavailable / Cancel Request
    </button>
    <?php elseif($req['withdrawal_status']==='requested'): ?>
    <span class="wd-badge pending"><i class="fas fa-clock"></i> Withdrawal Pending Finance Review</span>
    <?php elseif($req['withdrawal_status']==='approved'): ?>
    <span class="wd-badge approved"><i class="fas fa-check"></i> Withdrawal Approved — Budget Released</span>
    <?php elseif($req['withdrawal_status']==='rejected'): ?>
    <span class="wd-badge rejected"><i class="fas fa-times"></i> Withdrawal Rejected</span>
    <?php endif; ?>

  </div>
</div>

<?php endforeach; ?>

</div>
</div>

</main>
</div>

<div class="modal" id="requestModal">
<div class="modal-content">
<span class="modal-close" id="closeModal">×</span>
<h2>Request Details</h2>
<div class="modal-body" id="modalBody"></div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../JS/my-requests.js"></script>
<script>
async function reportUnavailable(prId, prNumber){
  const {value: reason, isConfirmed} = await Swal.fire({
    title: `Report Item Unavailable`,
    html: `<p style="color:#fbbf24;margin-bottom:12px;font-size:.93rem">
             <i class='fas fa-triangle-exclamation'></i>
             Use this when the item for <strong>${prNumber}</strong> cannot be sourced or is no longer needed.
             Finance will review and release the reserved budget.
           </p>`,
    input: 'textarea',
    inputLabel: 'Reason *',
    inputPlaceholder: 'Describe why the item is unavailable or the request needs to be cancelled (min 5 characters)...',
    showCancelButton: true,
    confirmButtonColor: '#b45309',
    confirmButtonText: 'Submit Withdrawal',
    preConfirm: v => {
      if(!v || v.trim().length < 5){ Swal.showValidationMessage('Reason must be at least 5 characters'); }
      return v?.trim();
    }
  });
  if(!isConfirmed || !reason) return;
  const res = await fetch('../PHP/request_withdrawal.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({pr_id: prId, reason})
  });
  const d = await res.json();
  if(d.success){
    Swal.fire({icon:'success', title:'Submitted', text: d.message, confirmButtonColor:'#22c55e'})
      .then(() => location.reload());
  } else {
    Swal.fire({icon:'error', title:'Error', text: d.message});
  }
}
</script>

</body>
</html>
