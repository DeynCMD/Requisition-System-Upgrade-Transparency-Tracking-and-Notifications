<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Redirect if not logged in as Finance
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'FINANCE') {
  header('Location: ../../Admin/login.php');
  exit;
}

$conn = new mysqli("localhost", "root", "", "ze_electronic");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// 1. Fetch main budget
$budget_query = $conn->query("SELECT * FROM finance_budget WHERE id = 1");
$budget = $budget_query->fetch_assoc() ?: [
  'total_budget' => 0,
  'allocated_budget' => 0,
  'spent_budget' => 0,
  'remaining_budget' => 0,
  'updated_at' => date('Y-m-d H:i:s')
];

$total_budget = number_format($budget['total_budget'], 2);
$allocated_budget = number_format($budget['allocated_budget'], 2);
$spent_budget = number_format($budget['spent_budget'], 2);
$remaining_budget = number_format($budget['remaining_budget'], 2);

// Utilization %
$utilization = $budget['total_budget'] > 0 ? round(($budget['spent_budget'] / $budget['total_budget']) * 100, 1) : 0;

// 2. Recent budget transactions
$transactions_query = $conn->query("
    SELECT 
        transaction_type,
        amount,
        description,
        department,
        performed_by,
        created_at
    FROM budget_transactions
    ORDER BY created_at DESC
    LIMIT 5
");
$transactions = $transactions_query->fetch_all(MYSQLI_ASSOC);

// 3. Recent finance approvals
$approvals_query = $conn->query("
    SELECT 
        pr_number,
        requestor_name,
        department,
        total_amount,
        status,
        finance_approved_at AS approved_at,
        rejection_reason
    FROM finance_approvals
    ORDER BY created_at DESC
    LIMIT 5
");
$approvals = $approvals_query->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Finance Dashboard — Procurement System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../CSS/theme.css" />
  <link rel="stylesheet" href="../CSS/finance-dashboard.css" />
</head>

<body>

  <div class="container">
    <!-- Sidebar (unchanged) -->
    <aside class="sidebar">
      <div class="profile">
        <img src="../Assets/Avatar.jpg" alt="User" />
        <span class="role">FINANCE</span>
      </div>

      <nav class="nav-menu">
        <ul>
          <li>
            <a href="finance-dashboard.php" class="active"><i class="fas fa-dollar-sign"></i> Finance</a>
          </li>
          <li>
            <a href="budget-approvals.html"><i class="fas fa-check-double"></i> Budget Approvals</a>
          </li>
         <li>
          <a href="finance-budget.php"><i class="fas fa-wallet"></i> Budget</a>
        </li>
          <li>
            <a href="withdrawals.php"><i class="fas fa-rotate-left"></i> Withdrawals</a>
          </li>
          <li>
            <a href="history.html"><i class="fas fa-history"></i> History</a>
          </li>
        </ul>
      </nav>

      <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <h1 class="page-title">Finance Dashboard</h1>

      <!-- Updated 4 Cards - styled like Requester Dashboard -->
      <div class="metrics-grid">
        <!-- Total Budget -->
        <div class="metric-card">
          <div class="metric-icon total"><i class="fas fa-wallet"></i></div>
          <div class="metric-label">Total Budget</div>
          <div class="metric-number">₱<?= $total_budget ?></div>
          <div class="metric-subtitle"><?= $utilization ?>% Utilized</div>
        </div>

        <!-- Allocated Budget -->
        <div class="metric-card">
          <div class="metric-icon allocated"><i class="fas fa-hand-holding-usd"></i></div>
          <div class="metric-label">Allocated Budget</div>
          <div class="metric-number">₱<?= $allocated_budget ?></div>
          <div class="metric-subtitle">Department allocations</div>
        </div>

        <!-- Spent Budget -->
        <div class="metric-card">
          <div class="metric-icon spent"><i class="fas fa-chart-pie"></i></div>
          <div class="metric-label">Spent Budget</div>
          <div class="metric-number">₱<?= $spent_budget ?></div>
          <div class="metric-subtitle">Used this period</div>
        </div>

        <!-- Remaining Budget -->
        <div class="metric-card">
          <div class="metric-icon remaining"><i class="fas fa-piggy-bank"></i></div>
          <div class="metric-label">Remaining Budget</div>
          <div class="metric-number">₱<?= $remaining_budget ?></div>
          <div class="metric-subtitle">Available funds</div>
        </div>
      </div>

      <!-- Recent Budget Transactions (unchanged) -->
      <div class="transactions-section">
        <div class="section-header">
          <h2>Recent Budget Transactions</h2>
        </div>

        <div class="transactions-list">
          <?php if (empty($transactions)): ?>
            <p>No recent budget transactions</p>
          <?php else: ?>
            <?php foreach ($transactions as $tx): ?>
              <div class="transaction-item">
                <div class="transaction-icon <?= $tx['transaction_type'] ?>">
                  <i
                    class="fas <?= $tx['transaction_type'] === 'add' ? 'fa-plus' : ($tx['transaction_type'] === 'spend' ? 'fa-minus' : 'fa-exchange-alt') ?>"></i>
                </div>
                <div class="transaction-info">
                  <h4><?= ucfirst($tx['transaction_type']) ?> - <?= htmlspecialchars($tx['description']) ?></h4>
                  <p>Department: <?= htmlspecialchars($tx['department'] ?: 'Company') ?></p>
                </div>
                <div
                  class="transaction-amount <?= $tx['transaction_type'] === 'add' || $tx['transaction_type'] === 'allocate' ? 'positive' : 'negative' ?>">
                  ₱<?= number_format($tx['amount'], 2) ?>
                </div>
                <div class="transaction-date">
                  <?= date("M d, Y H:i", strtotime($tx['created_at'])) ?>
                </div>
                <span class="status-badge <?= $tx['transaction_type'] ?>"><?= ucfirst($tx['transaction_type']) ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recent Approvals (unchanged) -->
      <div class="transactions-section">
        <div class="section-header">
          <h2>Recent Approvals</h2>
          <a href="budget-approvals.html" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="transactions-list">
          <?php if (empty($approvals)): ?>
            <p>No recent approvals</p>
          <?php else: ?>
            <?php foreach ($approvals as $app): ?>
              <div class="transaction-item">
                <div class="transaction-icon <?= strtolower($app['status']) ?>">
                  <i class="fas fa-check"></i>
                </div>
                <div class="transaction-info">
                  <h4><?= htmlspecialchars($app['pr_number']) ?></h4>
                  <p>Department: <?= htmlspecialchars($app['department'] ?: 'N/A') ?> | Requestor:
                    <?= htmlspecialchars($app['requestor_name']) ?>
                  </p>
                </div>
                <div class="transaction-amount positive">
                  PHP <?= number_format($app['total_amount'], 2) ?>
                </div>
                <div class="transaction-date">
                  <?= date("M d, Y H:i", strtotime($app['approved_at'])) ?>
                </div>
                <span class="status-badge <?= strtolower($app['status']) ?>"><?= htmlspecialchars($app['status']) ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>

</body>

</html>