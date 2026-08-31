<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>History — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../CSS/history.css" />
</head>

<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile">
        <img src="../Assets/Avatar.jpg" alt="Admin" />
        <span class="role">ADMIN</span>
      </div>

      <nav class="nav-menu">
        <ul>
          <li><a href="AdminZE.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="Admin-users.php"><i class="fas fa-users"></i> User Management</a></li>
          <li><a href="Pending-approvals.php"><i class="fas fa-clock"></i> Pending Approvals</a></li>
          <li><a href="suppliers.php"><i class="fas fa-truck-field"></i> Suppliers</a></li>
          <li><a href="admin_price_prediction.php"><i class="fas fa-chart-line"></i> Price Prediction</a></li>
          <li><a href="admin_returns.php"><i class="fas fa-rotate-left"></i> Item Returns</a></li>
      <li><a href="HistoryZE.php" class="active"><i class="fas fa-history"></i> History</a></li>
        </ul>
      </nav>

      <button onclick="window.location.href = '../PHP/logout.php'" class="logout-btn">
        LOGOUT
      </button>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="page-header">
        <h1>Activity History</h1>
        <div class="filter-controls">
          <select class="filter-select" id="activityTypeFilter">
            <option value="all">All Activities</option>
            <optgroup label="User Activities">
              <option value="user_added">User Added</option>
              <option value="user_edited">User Edited</option>
              <option value="user_deleted">User Deleted</option>
            </optgroup>
            <optgroup label="Request Activities">
              <option value="request_created">Request Created</option>
              <option value="request_approved">Request Approved</option>
              <option value="request_rejected">Request Rejected</option>
              <option value="request_pending">Request Pending</option>
              <option value="request_cancelled">Request Cancelled</option>
            </optgroup>
            <optgroup label="Finance Activities">
              <option value="request_finance_approved">Finance Approved</option>
              <option value="request_finance_rejected">Finance Rejected</option>
              <option value="budget_insufficient">Insufficient Budget</option>
            </optgroup>
            <optgroup label="System Activities">
              <option value="login">Login Events</option>
              <option value="logout">Logout Events</option>
              <option value="purchase">Purchase Events</option>
            </optgroup>
          </select>
          <input type="date" class="date-filter" id="dateFilter" />
          <button class="clear-filter-btn" id="clearFilters">
            <i class="fas fa-times"></i> Clear
          </button>
        </div>
      </div>

      <!-- Activity Stats -->
      <div class="stats-row">
        <div class="stat-card">
          <i class="fas fa-clipboard-list"></i>
          <div>
            <h3 id="totalActivities">0</h3>
            <p>Total Activities</p>
          </div>
        </div>
        <div class="stat-card">
          <i class="fas fa-check-circle"></i>
          <div>
            <h3 id="totalApproved">0</h3>
            <p>Approved</p>
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

      <!-- History Timeline Card -->
      <div class="history-card">
        <div class="timeline" id="timeline">
          <!-- Dynamic content will be loaded here -->
          <div class="loading-state" id="loadingState">
            <i class="fas fa-spinner fa-spin fa-3x"></i>
            <p>Loading activity history...</p>
          </div>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none">
          <i class="fas fa-inbox fa-3x"></i>
          <p>No activities found</p>
          <small>Try adjusting your filters</small>
        </div>
      </div>

      <div class="load-more" id="loadMoreContainer" style="display: none">
        <button class="load-more-btn" id="loadMoreBtn">
          Load More Activities
        </button>
      </div>
    </main>
  </div>

  <!-- Activity Detail Modal -->
  <div class="modal" id="activityModal" style="display: none">
    <div class="modal-content">
      <span class="modal-close" id="closeActivityModal">×</span>
      <h2 id="modalTitle">Activity Details</h2>
      <div id="modalBody"></div>
    </div>
  </div>

  <script src="../JS/history.js"></script>
</body>

</html>