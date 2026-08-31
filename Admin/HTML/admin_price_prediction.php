<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../../Admin/HTML/ZE-Electronics.php");
    exit();
}

require_once '../PHP/db.php';

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Price Prediction — Procurement System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../CSS/price_prediction.css?v=<?= time() ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="container">
        <!-- Sidebar (same as dashboard) -->
        <aside class="sidebar">
            <div class="profile">
                <img src="../Assets/Avatar.jpg" alt="Admin" />
                <span class="role">ADMIN</span>
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="AdminZE.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="Admin-users.php"><i class="fas fa-users"></i> User Management</a></li>
                    <li><a href="Pending-approvals.php"><i class="fas fa-clock"></i> Pending Requests</a></li>
                    <li><a href="suppliers.php"><i class="fas fa-truck-field"></i> Suppliers</a></li>
                    <li><a href="admin_price_prediction.php" class="active"><i class="fas fa-chart-line"></i> Price Prediction</a></li>
                    <li><a href="admin_returns.php"><i class="fas fa-rotate-left"></i> Item Returns</a></li>
      <li><a href="HistoryZE.php"><i class="fas fa-history"></i> History</a></li>
                </ul>
            </nav>
            <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header>
                <h1 class="page-title">Part Price Prediction</h1>
            </header>

            <section class="price-prediction-section">
                <h2 style="color: #e0e0ff; margin-bottom: 25px;">
                    <i class="fas fa-microchip"></i> Electronic Component Price Forecast
                </h2>

                <div class="search-container">
                    <input type="text" id="partSearch" class="search-box"
                        placeholder="Enter MPN (Manufacturer Part Number)..." autocomplete="off" />
                    <i class="fas fa-search search-icon"></i>
                    <div id="autocompleteDropdown" class="autocomplete-dropdown"></div>
                </div>

                <div id="predictionResult" class="prediction-result">
                    <h3 style="color: #e0e0ff; margin-bottom: 20px;">
                        <i class="fas fa-microchip"></i> <span id="selectedPart"></span>
                    </h3>

                    <div class="prediction-grid">
                        <div class="prediction-item">
                            <h4>Current Avg Price</h4>
                            <div class="prediction-value" id="currentPrice">-</div>
                        </div>
                        <div class="prediction-item">
                            <h4>Predicted Next Month</h4>
                            <div class="prediction-value" id="predictedPrice">-</div>
                            <span id="trendIndicator"></span>
                        </div>
                        <div class="prediction-item">
                            <h4>Price Change</h4>
                            <div class="prediction-value" id="priceChange">-</div>
                        </div>
                        <div class="prediction-item">
                            <h4>Total Orders</h4>
                            <div class="prediction-value" id="totalOrders">-</div>
                        </div>
                        <!-- Added Historical MAPE card -->
                        <div class="prediction-item">
                            <h4>Historical MAPE</h4>
                            <div class="prediction-value" id="mapeValue">-</div>
                            <small id="mapeNote" style="color: #94a3b8; font-size: 0.85rem;"></small>
                        </div>
                    </div>

                    <div class="confidence-indicator">
                        <span style="color: #a0aec0; font-size: 14px;">Prediction Confidence:</span>
                        <div class="confidence-bar">
                            <div class="confidence-fill" id="confidenceFill"></div>
                        </div>
                        <span id="confidencePercent" style="color: #e0e0ff; font-weight: 600;">-</span>
                    </div>

                    <div class="forecast-chart-container">
                        <canvas id="forecastChart" style="max-height: 380px;"></canvas>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const allMPNs = <?= $all_mpns_json ?>;
        let forecastChartInstance = null;

        // ────────────────────────────────────────────────
        //  Autocomplete + Search Logic
        // ────────────────────────────────────────────────
        const searchBox = document.getElementById('partSearch');
        const dropdown = document.getElementById('autocompleteDropdown');

        searchBox.addEventListener('input', function () {
            const value = this.value.toLowerCase();
            dropdown.innerHTML = '';

            if (value.length < 1) {
                dropdown.style.display = 'none';
                return;
            }

            const filtered = allMPNs.filter(mpn => mpn.toLowerCase().includes(value)).slice(0, 10);

            if (filtered.length > 0) {
                filtered.forEach(mpn => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item';
                    item.textContent = mpn;
                    item.addEventListener('click', () => {
                        searchBox.value = mpn;
                        dropdown.style.display = 'none';
                        fetchPricePrediction(mpn);
                    });
                    dropdown.appendChild(item);
                });
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        });

        searchBox.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && this.value.trim()) {
                fetchPricePrediction(this.value.trim());
                dropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', function (e) {
            if (!searchBox.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        function fetchPricePrediction(mpn) {
            const resultDiv = document.getElementById('predictionResult');
            resultDiv.classList.remove('show');

            setTimeout(() => {
                resultDiv.classList.add('show');
                document.getElementById('selectedPart').textContent = mpn;
            }, 100);

            fetch('../PHP/get_price_prediction.php?mpn=' + encodeURIComponent(mpn))
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        Swal.fire({ icon:'warning', title:'No Data', text: data.error, confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff' });
                        resultDiv.classList.remove('show');
                        return;
                    }
                    displayPrediction(data, mpn);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({ icon:'error', title:'Error', text:'Failed to fetch prediction. Please try again.', confirmButtonColor:'#ef4444', background:'#2a2a3a', color:'#e0e0ff' });
                    resultDiv.classList.remove('show');
                });
        }

        function displayPrediction(data, mpn) {
            document.getElementById('currentPrice').textContent = '$' + parseFloat(data.current_avg_price).toFixed(4);
            document.getElementById('predictedPrice').textContent = '$' + parseFloat(data.predicted_price).toFixed(4);
            document.getElementById('totalOrders').textContent = data.total_orders;

            const change = ((data.predicted_price - data.current_avg_price) / data.current_avg_price * 100).toFixed(2);
            const changeElem = document.getElementById('priceChange');
            changeElem.textContent = (change > 0 ? '+' : '') + change + '%';
            changeElem.style.color = change > 0 ? '#f87171' : '#4ade80';

            const trendElem = document.getElementById('trendIndicator');
            if (Math.abs(change) < 2) {
                trendElem.innerHTML = '<span class="trend-indicator trend-stable"><i class="fas fa-minus"></i> Stable</span>';
            } else if (change > 0) {
                trendElem.innerHTML = '<span class="trend-indicator trend-up"><i class="fas fa-arrow-up"></i> Rising</span>';
            } else {
                trendElem.innerHTML = '<span class="trend-indicator trend-down"><i class="fas fa-arrow-down"></i> Falling</span>';
            }

            const confidence = data.confidence || 50;
            document.getElementById('confidenceFill').style.width = confidence + '%';
            document.getElementById('confidencePercent').textContent = confidence + '%';

            renderForecastChart(data, mpn);

            // ────────────────────────────────────────────────
            // Show Historical MAPE
            // ────────────────────────────────────────────────
            if (data.mape !== undefined && data.mape !== null) {
                const mapeValue = document.getElementById('mapeValue');
                const mapeNote = document.getElementById('mapeNote');

                mapeValue.textContent = data.mape + '%';

                mapeNote.textContent = data.mape_note || `based on ${data.mape_comparisons || '?'} months`;

                // Color coding
                if (data.mape < 10) {
                    mapeValue.style.color = '#4ade80';      // excellent
                } else if (data.mape < 20) {
                    mapeValue.style.color = '#fbbf24';      // acceptable
                } else {
                    mapeValue.style.color = '#f87171';      // needs improvement
                }
            } else {
                document.getElementById('mapeValue').textContent = 'N/A';
                document.getElementById('mapeNote').textContent = 'Not enough data';
            }
        }

        function renderForecastChart(data, mpn) {
            const ctx = document.getElementById('forecastChart').getContext('2d');
            if (forecastChartInstance) forecastChartInstance.destroy();

            const labels = [...data.historical_months, 'Next Month'];
            const historicalPrices = data.historical_prices;
            const allPrices = [...historicalPrices, data.predicted_price];

            forecastChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Historical Prices',
                        data: historicalPrices,
                        borderColor: '#4ade80',
                        backgroundColor: 'rgba(74, 222, 128, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }, {
                        label: 'Predicted Price',
                        data: [...Array(historicalPrices.length).fill(null), data.predicted_price],
                        borderColor: '#fbbf24',
                        backgroundColor: 'rgba(251, 191, 36, 0.1)',
                        borderDash: [5, 5],
                        pointRadius: 7,
                        pointHoverRadius: 9,
                        pointStyle: 'star'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#e0e0ff'
                            }
                        },
                        tooltip: {
                            backgroundColor: '#2a2a3a',
                            titleColor: '#e0e0ff',
                            bodyColor: '#e0e0ff',
                            borderColor: '#444',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#94a3b8' },
                            grid: { color: '#444' }
                        },
                        y: {
                            ticks: { color: '#94a3b8' },
                            grid: { color: '#444' }
                        }
                    }
                }
            });
        }
    </script>
</body>

</html>