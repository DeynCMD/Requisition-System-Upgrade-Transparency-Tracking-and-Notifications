<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../ZE-Electronics.php");
    exit();
}

require_once '../PHP/urgency_helper.php';
require_once '../../Admin/PHP/db.php';

// === Get all unique MPNs for search autocomplete ===
$mpn_result = $conn->query("
    SELECT DISTINCT mpn 
    FROM purchase_requests 
    WHERE mpn IS NOT NULL AND mpn != '' 
    ORDER BY mpn ASC
");
$all_mpns = [];
while ($row = $mpn_result->fetch_assoc()) {
    $all_mpns[] = $row['mpn'];
}
$all_mpns_json = json_encode($all_mpns);

// === Stats ===
$total_activity = $conn->query("SELECT COUNT(*) as count FROM activity_logs")->fetch_assoc()['count'] ?? 0;
$total_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests")->fetch_assoc()['count'] ?? 0;
$total_approved = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE status = 'approved'")->fetch_assoc()['count'] ?? 0;
$total_user_changes = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE activity_type IN ('user_added', 'user_edited', 'user_deleted')")->fetch_assoc()['count'] ?? 0;

// === Chart 1: Requests Status Distribution (now VERTICAL bar chart) ===
$status_result = $conn->query("SELECT status, COUNT(*) as count FROM purchase_requests GROUP BY status");
$status_data = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];
while ($row = $status_result->fetch_assoc()) {
    $key = ucfirst(strtolower($row['status']));
    if (isset($status_data[$key]))
        $status_data[$key] = (int) $row['count'];
}
$status_json = json_encode(array_values($status_data));
$status_labels_json = json_encode(array_keys($status_data));

// === Chart 2: Requests Over Time (last 6 months) ===
$requests_over_time = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM purchase_requests 
    GROUP BY month 
    ORDER BY month DESC 
    LIMIT 6
");
$months = $counts = [];
while ($row = $requests_over_time->fetch_assoc()) {
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $counts[] = (int) $row['count'];
}
$months = array_reverse($months);
$counts = array_reverse($counts);
$months_json = json_encode($months);
$counts_json = json_encode($counts);

// === Pending Requests (Latest 5) ===
$pending_result = $conn->query("
    SELECT 
        pr.id, pr.status, pr.requestor_name AS created_by, pr.category, pr.mpn AS item_name,
        pr.quantity, pr.unit_price AS predicted_price, pr.currency, pr.created_at, pr.reason,
        DATEDIFF(CURDATE(), pr.created_at) AS days_old
    FROM purchase_requests pr
    WHERE pr.status = 'PENDING'
    ORDER BY pr.created_at DESC
    LIMIT 5
");
$pending_delays = $pending_result ? $pending_result->fetch_all(MYSQLI_ASSOC) : [];

// === Recent Activity (Latest 5) ===
$activity_result = $conn->query("
    SELECT activity_type AS action, description AS details, performed_by AS username, created_at
    FROM activity_logs
    ORDER BY created_at DESC
    LIMIT 5
");
$recent_activities = $activity_result ? $activity_result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard — Procurement System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../CSS/admin_style.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li>
                        <a href="AdminZE.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="Admin-users.php"><i class="fas fa-users"></i> User Management</a>
                    </li>
                    <li>
                        <a href="Pending-approvals.php"><i class="fas fa-clock"></i> Pending Approvals</a>
                    </li>
                    <li><a href="suppliers.php"><i class="fas fa-truck-field"></i> Suppliers</a></li>
                    <li><a href="admin_price_prediction.php"><i class="fas fa-chart-line"></i> Price Prediction</a></li>
                    <li><a href="admin_returns.php"><i class="fas fa-rotate-left"></i> Item Returns</a></li>
      <li><a href="HistoryZE.php"><i class="fas fa-history"></i> History</a></li>
                </ul>
            </nav>
            <a href="../PHP/logout.php" class="logout-btn">LOGOUT</a>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header>
                <h1>Admin Dashboard</h1>
            </header>

            <div class="content-wrapper">
                <!-- Stats Cards -->
                <section class="stats-grid">
                    <div class="card total-activity">
                        <div class="text">
                            <h3>Total Activity</h3>
                            <div class="number"><?= number_format($total_activity) ?></div>
                        </div>
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="card requests">
                        <div class="text">
                            <h3>Total Requests</h3>
                            <div class="number"><?= number_format($total_requests) ?></div>
                        </div>
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="card approved">
                        <div class="text">
                            <h3>Approved</h3>
                            <div class="number"><?= number_format($total_approved) ?></div>
                        </div>
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card user-changes">
                        <div class="text">
                            <h3>User Changes</h3>
                            <div class="number"><?= number_format($total_user_changes) ?></div>
                        </div>
                        <i class="fas fa-user-edit"></i>
                    </div>
                </section>

                <!-- Charts Section -->
                <section class="charts-grid">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-bar"></i> Request Status Distribution</h3>
                        <canvas id="statusChart" style="max-height: 300px;"></canvas>
                    </div>

                    <div class="chart-card">
                        <h3><i class="fas fa-chart-line"></i> Requests Over Time (Last 6 Months)</h3>
                        <canvas id="requestsOverTime" style="max-height: 300px;"></canvas>
                    </div>
                </section>

                <!-- Bottom Section: Pending + Activity -->
                <div class="bottom-section">
                    <!-- Pending Requests -->
                    <section class="table-card">
                        <div class="table-header">
                            <h3><i class="fas fa-clock"></i> Pending Requests (Latest 5)</h3>
                        </div>
                        <?php if (!empty($pending_delays)): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Requestor</th>
                                        <th>Item/MPN</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Currency</th>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th>Urgency</th>
                                        <th>Reason</th>
                                        <th>Days Old</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_delays as $row): ?>
                                        <tr class="<?= $row['days_old'] > 7 ? 'delayed-row' : '' ?> <?= urgency_row_class($row['urgency'] ?? '') ?>">
                                            <td><span class="status-badge pending"><?= htmlspecialchars($row['status']) ?></span></td>
                                            <td><?= htmlspecialchars($row['created_by'] ?? 'Unknown') ?></td>
                                            <td><?= htmlspecialchars($row['item_name'] ?? '-') ?></td>
                                            <td><?= number_format($row['quantity']) ?></td>
                                            <td><?= number_format($row['predicted_price'], 4) ?></td>
                                            <td><?= htmlspecialchars($row['currency']) ?></td>
                                            <td><?= date('m/d/Y', strtotime($row['created_at'])) ?></td>
                                            <td><?= htmlspecialchars($row['category'] ?? '-') ?></td>
                                            <td><?= urgency_badge($row['urgency'] ?? '') ?></td>
                                            <td><?= htmlspecialchars(substr($row['reason'] ?? '-', 0, 60)) . (strlen($row['reason'] ?? '') > 60 ? '...' : '') ?></td>
                                            <td class="<?= $row['days_old'] > 7 ? 'text-danger' : '' ?>"><?= $row['days_old'] ?> days</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-data">No pending requests at the moment.</div>
                        <?php endif; ?>
                    </section>

                    <!-- Recent Activity -->
                    <aside class="recent-activity">
                        <h3><i class="fas fa-bell"></i> Recent Activity (Latest 5)</h3>
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <strong><?= htmlspecialchars(str_replace('_', ' ', ucfirst($activity['action']))) ?></strong>
                                    <small>by <?= htmlspecialchars($activity['username'] ?? 'System') ?></small>
                                    <p><?= htmlspecialchars($activity['details'] ?? 'No details') ?></p>
                                    <span class="time"><?= date('M d, Y h:i A', strtotime($activity['created_at'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-data">No recent activity yet.</div>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        </main>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        // Status Bar Chart - Vertical/Upright bars
        new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: {
                labels: <?= $status_labels_json ?>,
                datasets: [{
                    label: 'Number of Requests',
                    data: <?= $status_json ?>,
                    backgroundColor: ['#fbbf24', '#4ade80', '#f87171'],
                    borderColor: ['#d97706', '#16a34a', '#dc2626'],
                    borderWidth: 1,
                    borderRadius: 6,
                    barThickness: 50,
                    categoryPercentage: 0.7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#e0e0ff',
                        bodyColor: '#e0e0ff',
                        borderColor: '#444',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#334155' },
                        ticks: { color: '#94a3b8', stepSize: 1 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#e0e0ff' }
                    }
                }
            }
        });

        // Requests Over Time Line Chart (unchanged)
        new Chart(document.getElementById('requestsOverTime'), {
            type: 'line',
            data: {
                labels: <?= $months_json ?>,
                datasets: [{
                    label: 'Requests Created',
                    data: <?= $counts_json ?>,
                    borderColor: '#4ade80',
                    backgroundColor: 'rgba(74, 222, 128, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { color: '#e0e0ff' } },
                    x: { ticks: { color: '#e0e0ff' } }
                },
                plugins: { legend: { labels: { color: '#e0e0ff' } } }
            }
        });
    </script>

</body>

</html>