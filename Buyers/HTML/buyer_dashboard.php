<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'BUYER') {
    header("Location: ../../Admin/HTML/ZE-Electronics.php");
    exit();
}

require_once '../../Admin/PHP/db.php';

// Optional: if you have real buyer name in session, use it
// $buyer_name = $_SESSION['requestor_name'] ?? 'Current User';

// 1. Pending Orders (only for current buyer)
$pending_query = "
    SELECT COUNT(*) as count 
    FROM purchase_requests 
    WHERE finance_status = 'approved' 
      AND (buyer_status IS NULL OR buyer_status = 'pending_payment')
";

$pending_count = $conn->query($pending_query)->fetch_assoc()['count'] ?? 0;

// 2. Purchased Orders (only for current buyer)
$purchased_query = "
    SELECT COUNT(*) as count 
    FROM purchase_requests 
    WHERE buyer_status = 'purchased'
";

$purchased_count = $conn->query($purchased_query)->fetch_assoc()['count'] ?? 0;

// 3. Total Spent (calculated for current buyer)
$total_spent_query = "
    SELECT SUM(quantity * unit_price) as total 
    FROM purchase_requests 
    WHERE buyer_status = 'purchased'
";

$total_spent = $conn->query($total_spent_query)->fetch_assoc()['total'] ?? 0;

// 4. Recent Purchases — show ALL recent (no name filter for visibility)
$recent_query = "
    SELECT pr_number, requestor_name, quantity, unit_price, currency, 
           buyer_status, updated_at 
    FROM purchase_requests 
    WHERE buyer_status = 'purchased' 
    ORDER BY updated_at DESC 
    LIMIT 5
";

$recent_purchases = $conn->query($recent_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buyer Dashboard — Procurement System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../CSS/buyer_dashboard.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile">
                <img src="../Assets/Avatar.jpg" alt="Buyer" />
                <span class="role">BUYER</span>
            </div>

            <nav class="nav-menu">
                <ul>
                    <li><a href="buyer_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li><a href="buyer.php"><i class="fas fa-shopping-cart"></i> Purchase Requests</a></li>
                    <li><a href="buyer_history.php"><i class="fas fa-history"></i>History</a></li>
                    <li><a href="buyer_export.php"><i class="fas fa-file-export"></i> Export PO</a></li>
                </ul>
            </nav>

            <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header>
                <h1>Buyer Dashboard</h1>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="card">
                    <div class="text">
                        <h3>Pending Orders</h3>
                        <div class="number"><?= $pending_count ?></div>
                    </div>
                    <i class="fas fa-clock"></i>
                </div>

                <div class="card">
                    <div class="text">
                        <h3>Purchased Orders</h3>
                        <div class="number"><?= $purchased_count ?></div>
                    </div>
                    <i class="fas fa-check-circle"></i>
                </div>

                <div class="card">
                    <div class="text">
                        <h3>Total Spent</h3>
                        <div class="number">$<?= number_format($total_spent, 2) ?></div>
                    </div>
                    <i class="fas fa-dollar-sign"></i>
                </div>

                <div class="card">
                    <div class="text">
                        <h3>Recent Purchases</h3>
                        <div class="number"><?= count($recent_purchases) ?></div>
                    </div>
                    <i class="fas fa-history"></i>
                </div>
            </div>

            <!-- Spending Trend Chart (demo) -->
            <div class="chart-card">
                <h3><i class="fas fa-chart-line"></i> Spending Trend (Last 6 Months)</h3>
                <canvas id="spendingChart" height="180"></canvas>
            </div>

            <!-- Recent Purchased Table -->
            <div class="table-card">
                <div class="table-header">
                    <i class="fas fa-shopping-bag"></i> Recent Purchases
                </div>

                <?php if (empty($recent_purchases)): ?>
                    <p class="no-data">No recent purchases yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>PR#</th>
                                <th>Buyer</th>
                                <th>Total Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_purchases as $purchase): ?>
                                <tr>
                                    <td><?= htmlspecialchars($purchase['pr_number'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($purchase['requestor_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($purchase['currency'] ?? '$') ?><?= number_format($purchase['quantity'] * $purchase['unit_price'], 2) ?>
                                    </td>
                                    <td><?= date('M d, Y h:i A', strtotime($purchase['updated_at'] ?? $purchase['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Spending Trend Chart (demo – you can replace with real data later)
        const ctx = document.getElementById('spendingChart')?.getContext('2d');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan'],
                    datasets: [{
                        label: 'Total Spent (USD)',
                        data: [850, 1200, 2100, 1800, 3400, 4200],
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.12)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#22c55e',
                        pointHoverRadius: 8,
                        pointRadius: 5,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#333' }, ticks: { color: '#aaa' } },
                        x: { grid: { display: false }, ticks: { color: '#aaa' } }
                    }
                }
            });
        }
    </script>
</body>

</html>