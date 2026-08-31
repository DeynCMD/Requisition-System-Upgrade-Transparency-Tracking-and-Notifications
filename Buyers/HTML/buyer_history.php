<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'BUYER') {
  header("Location: ../../Admin/HTML/ZE-Electronics.php");
  exit();
}

require_once '../../Admin/PHP/db.php';

// Stats counts (only PR-related)
$pending_count = $conn->query("
    SELECT COUNT(*) as count 
    FROM purchase_requests 
    WHERE finance_status = 'approved' 
      AND (buyer_status IS NULL OR buyer_status = 'pending_payment')
")->fetch_assoc()['count'] ?? 0;

$purchased_count = $conn->query("
    SELECT COUNT(*) as count 
    FROM purchase_requests 
    WHERE buyer_status = 'purchased'
")->fetch_assoc()['count'] ?? 0;

$total_history = $pending_count + $purchased_count;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Activity History — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../CSS/history.css?v=<?= time() ?>">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .modal-content {
      max-height: 80vh;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #555 #2a2a3a;
    }

    .modal-content::-webkit-scrollbar {
      width: 6px;
    }

    .modal-content::-webkit-scrollbar-track {
      background: #2a2a3a;
      border-radius: 10px;
    }

    .modal-content::-webkit-scrollbar-thumb {
      background: #555;
      border-radius: 10px;
    }

    .modal-content::-webkit-scrollbar-thumb:hover {
      background: #777;
    }

    .detail-status-badge {
      padding: 10px 20px;
      border-radius: 30px;
      font-weight: bold;
      margin: 0 0 20px;
      text-align: center;
      font-size: 1.1rem;
    }

    .detail-info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }

    .detail-box {
      background: #2a2a3a;
      border: 1px solid #444;
      border-radius: 12px;
      padding: 16px;
      text-align: center;
    }

    .detail-label {
      color: #a0a0c0;
      font-size: 0.9rem;
      margin-bottom: 6px;
      display: block;
    }

    .detail-value {
      font-size: 1.1rem;
      font-weight: 600;
      color: #e0e0ff;
    }

    .detail-value.highlight {
      color: #4ade80;
      font-size: 1.3rem;
    }

    .detail-extra-section .detail-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #333;
    }

    .detail-extra-section .detail-row:last-child {
      border-bottom: none;
    }
  </style>
</head>

<body>
  <div class="container">
    <aside class="sidebar">
      <div class="profile">
        <img src="../Assets/Avatar.jpg" alt="Buyer" />
        <span class="role">BUYER</span>
      </div>

      <nav class="nav-menu">
        <ul>
          <li><a href="buyer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="buyer.php"><i class="fas fa-shopping-cart"></i> Purchase Requests</a></li>
          <li><a href="buyer_history.php" class="active"><i class="fas fa-history"></i>History</a></li>
          <li><a href="buyer_export.php"><i class="fas fa-file-export"></i> Export PO</a></li>
        </ul>
      </nav>

      <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1>Activity History</h1>
        <div class="filter-controls">
          <select class="filter-select" id="activityTypeFilter">
            <option value="all">All PR Activities</option>
            <option value="request_created">Request Created</option>
            <option value="request_approved,request_finance_approved">Approved</option>
            <option value="request_rejected,request_finance_rejected">Rejected</option>
            <option value="budget_insufficient">Budget Issues</option>
          </select>
          <input type="date" class="date-filter" id="dateFilter" />
          <button class="clear-filter-btn" id="clearFilters">
            <i class="fas fa-times"></i> Clear
          </button>
        </div>
      </div>

      <div class="stats-row">
        <div class="stat-card">
          <i class="fas fa-clipboard-list"></i>
          <div>
            <h3 id="totalActivities"><?= $total_history ?></h3>
            <p>Total PR Activities</p>
          </div>
        </div>
        <div class="stat-card">
          <i class="fas fa-check-circle"></i>
          <div>
            <h3 id="totalApproved"><?= $purchased_count ?></h3>
            <p>Approved / Purchased</p>
          </div>
        </div>
        <div class="stat-card">
          <i class="fas fa-times-circle"></i>
          <div>
            <h3 id="totalRejected">0</h3>
            <p>Rejected</p>
          </div>
        </div>
        <div class="stat-card">
          <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>
          <div>
            <h3 id="totalBudgetIssues">0</h3>
            <p>Budget Issues</p>
          </div>
        </div>
      </div>

      <div class="history-card">
        <div class="timeline" id="timeline">
          <div class="loading-state" id="loadingState">
            <i class="fas fa-spinner fa-spin fa-3x"></i>
            <p>Loading activity history...</p>
          </div>
        </div>

        <div class="empty-state" id="emptyState" style="display: none">
          <i class="fas fa-inbox fa-3x"></i>
          <p>No PR activities found</p>
          <small>Try adjusting your filters</small>
        </div>
      </div>

      <div class="load-more" id="loadMoreContainer" style="display: none">
        <button class="load-more-btn" id="loadMoreBtn">Load More</button>
      </div>
    </main>
  </div>

  <div class="modal" id="activityModal" style="display: none">
    <div class="modal-content">
      <span class="modal-close" id="closeActivityModal">×</span>
      <h2 id="modalTitle">Activity Details</h2>
      <div id="modalBody" class="detail-modal-container"></div>
    </div>
  </div>

  <script src="../JS/history.js?v=<?= time() ?>"></script>
</body>

</html>