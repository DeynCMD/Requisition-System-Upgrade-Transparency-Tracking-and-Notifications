<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'REQUESTOR') {
  header("Location: ../ZE-Electronics.php");
  exit();
}

require_once '../../Admin/PHP/db.php';
require_once '../../Admin/PHP/urgency_helper.php';

$user_id = $_SESSION['user_id'] ?? 0; // Make sure user_id is set in session during login!

// === Total Counts (all time for this user) ===
$total_pending = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE user_id = $user_id AND status = 'PENDING'")->fetch_assoc()['count'] ?? 0;
$total_approved = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE user_id = $user_id AND status = 'approved'")->fetch_assoc()['count'] ?? 0;
$total_for_payment = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE user_id = $user_id AND status = 'FOR_PAYMENT'")->fetch_assoc()['count'] ?? 0;
$total_rejected = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE user_id = $user_id AND status = 'rejected'")->fetch_assoc()['count'] ?? 0;

// === Real-Time +X Today (only today's changes) ===
$pending_today = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE user_id = $user_id AND status = 'PENDING' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0;
$approved_today = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE user_id = $user_id AND status = 'approved' AND DATE(approved_at) = CURDATE()")->fetch_assoc()['count'] ?? 0;
$for_payment_today = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE user_id = $user_id AND status = 'FOR_PAYMENT' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0;
$rejected_today = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE user_id = $user_id AND status = 'rejected' AND DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0;

// === My Recent Requests (Latest 5) ===
$my_requests = $conn->query("
    SELECT 
        pr_number, 
        mpn, 
        quantity, 
        unit_price, 
        currency, 
        DATE_FORMAT(created_at, '%m/%d/%Y') as date, 
        category, 
        status
    FROM purchase_requests 
    WHERE user_id = $user_id 
    ORDER BY created_at DESC 
    LIMIT 5
");
$requests = $my_requests ? $my_requests->fetch_all(MYSQLI_ASSOC) : [];

// === Recent Activity (related to this user) ===
$activity_result = $conn->query("
    SELECT activity_type, description, created_at
    FROM activity_logs
    WHERE description LIKE CONCAT('%', (SELECT username FROM users WHERE id = $user_id), '%') 
       OR description LIKE '%PR%'
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_activity = $activity_result ? $activity_result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Requestor Dashboard — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../CSS/style.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="../../Admin/CSS/admin_style.css?v=<?= time() ?>"/>
</head>

<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile">
        <img src="../Assets/Avatar.jpg" alt="Requestor" />
        <span class="role">REQUESTOR</span>
      </div>
      <nav class="nav-menu">
        <ul>
          <li><a href="requestor-dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="create-request.html"><i class="fas fa-plus-circle"></i> Create Request</a></li>
          <li>
            <a href="history.html"> <i class="fas fa-clock"></i> History </a>
          </li>
          <li>
            <a href="my-requests.php">
              <i class="fas fa-list-check"></i> My Requests
            </a>
          </li>
        </ul>
      </nav>
      <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header>
        <h1>Requestor Dashboard</h1>
      </header>

      <div class="content-wrapper">
        <!-- Stats Cards with Real-Time +X Today -->
        <div class="stats-grid">
          <div class="stat-card pending">
            <h3>PENDING</h3>
            <div class="number"><?= number_format($total_pending) ?></div>
            <div class="change pending">+<?= $pending_today ?> Today</div>
            <i class="fas fa-clock icon"></i>
          </div>

          <div class="stat-card approved">
            <h3>APPROVED</h3>
            <div class="number"><?= number_format($total_approved) ?></div>
            <div class="change approved">+<?= $approved_today ?> Today</div>
            <i class="fas fa-check-circle icon"></i>
          </div>

          <div class="stat-card for-payment">
            <h3>FOR PAYMENT</h3>
            <div class="number"><?= number_format($total_for_payment) ?></div>
            <div class="change for-payment">+<?= $for_payment_today ?> Today</div>
            <i class="fas fa-dollar-sign icon"></i>
          </div>

          <div class="stat-card rejected">
            <h3>REJECTED</h3>
            <div class="number"><?= number_format($total_rejected) ?></div>
            <div class="change rejected">+<?= $rejected_today ?> Today</div>
            <i class="fas fa-times-circle icon"></i>
          </div>
        </div>

        <!-- Side-by-Side: Recent Activity (Left) + My Recent Requests (Right) -->
        <div class="bottom-section">
          <!-- Recent Activity -->
          <div class="recent-activity">
            <h3><i class="fas fa-bell"></i> Recent Activity</h3>
            <?php if (!empty($recent_activity)): ?>
              <?php foreach ($recent_activity as $act): ?>
                <div class="activity-item">
                  <strong><?= htmlspecialchars(str_replace('_', ' ', ucfirst($act['activity_type']))) ?></strong>
                  <small><?= date('M d, h:i A', strtotime($act['created_at'])) ?></small>
                  <p><?= htmlspecialchars($act['description']) ?></p>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="activity-item empty">No recent notifications</div>
            <?php endif; ?>
          </div>

          <!-- My Recent Requests -->
          <div class="my-requests">
            <h3><i class="fas fa-list-alt"></i> My Recent Requests</h3>
            <?php if (!empty($requests)): ?>
              <table class="request-table">
                <thead>
                  <tr>
                    <th>Item/MPN</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Urgency</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($requests as $req): ?>
                    <tr class="<?= urgency_row_class($req['urgency'] ?? '') ?>">
                      <td><?= htmlspecialchars($req['mpn']) ?></td>
                      <td><?= number_format($req['quantity']) ?></td>
                      <td><?= number_format($req['unit_price'], 2) ?> <?= $req['currency'] ?></td>
                      <td><?= $req['date'] ?></td>
                      <td><?= htmlspecialchars($req['category'] ?: '-') ?></td>
                      <td><?= urgency_badge($req['urgency'] ?? '') ?></td>
                      <td>
                        <span class="status <?= strtolower($req['status']) ?>">
                          <?= ucfirst(strtolower($req['status'])) ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="no-data">No recent requests.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>

</html>