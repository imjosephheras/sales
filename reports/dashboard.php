<?php
/**
 * Dashboard - Sales Analytics
 * Displays charts and analytics for sales data
 */

// Include database configuration
require_once __DIR__ . '/../form_contract/db_config.php';

// Get database connection
$pdo = getDBConnection();

// Fetch all requests for analytics
$stmt = $pdo->query("
    SELECT
        form_id,
        request_type,
        requested_service,
        total_cost,
        created_at,
        Work_Date
    FROM forms
    ORDER BY Work_Date DESC, created_at DESC
");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to parse currency values
function parseCurrency($value) {
    return floatval(str_replace(['$', ','], '', $value ?? '0'));
}

// Calculate totals for each request
function getRequestTotal($row) {
    return parseCurrency($row['total_cost']);
}

// Prepare data for analytics
$salesByWeek = [];
$salesByMonth = [];
$salesByYear = [];
$requestTypes = [];
$requestServices = [];

foreach ($requests as $row) {
    $total = getRequestTotal($row);
    // Use Work_Date if available, otherwise fallback to created_at
    $dateValue = !empty($row['Work_Date']) ? $row['Work_Date'] : $row['created_at'];
    $date = new DateTime($dateValue);

    // Sales by week (ISO week)
    $weekKey = $date->format('Y-W');
    $weekLabel = 'Week ' . $date->format('W') . ', ' . $date->format('Y');
    if (!isset($salesByWeek[$weekKey])) {
        $salesByWeek[$weekKey] = ['label' => $weekLabel, 'total' => 0, 'count' => 0];
    }
    $salesByWeek[$weekKey]['total'] += $total;
    $salesByWeek[$weekKey]['count']++;

    // Sales by month
    $monthKey = $date->format('Y-m');
    $monthLabel = $date->format('M Y');
    if (!isset($salesByMonth[$monthKey])) {
        $salesByMonth[$monthKey] = ['label' => $monthLabel, 'total' => 0, 'count' => 0];
    }
    $salesByMonth[$monthKey]['total'] += $total;
    $salesByMonth[$monthKey]['count']++;

    // Sales by year
    $yearKey = $date->format('Y');
    if (!isset($salesByYear[$yearKey])) {
        $salesByYear[$yearKey] = ['label' => $yearKey, 'total' => 0, 'count' => 0];
    }
    $salesByYear[$yearKey]['total'] += $total;
    $salesByYear[$yearKey]['count']++;

    // Request types
    $type = $row['request_type'] ?: 'Unknown';
    if (!isset($requestTypes[$type])) {
        $requestTypes[$type] = 0;
    }
    $requestTypes[$type]++;

    // Requested services
    $service = $row['requested_service'] ?: 'Unknown';
    if (!isset($requestServices[$service])) {
        $requestServices[$service] = 0;
    }
    $requestServices[$service]++;
}

// Sort data
ksort($salesByWeek);
ksort($salesByMonth);
ksort($salesByYear);
arsort($requestServices);

// Keep only last 12 weeks/months
$salesByWeek = array_slice($salesByWeek, -12, 12, true);
$salesByMonth = array_slice($salesByMonth, -12, 12, true);

// Calculate grand totals
$grandTotalSales = array_sum(array_map('getRequestTotal', $requests));
$totalRequests = count($requests);

// Prepare JSON data for charts
$chartDataWeek = json_encode(array_values($salesByWeek));
$chartDataMonth = json_encode(array_values($salesByMonth));
$chartDataYear = json_encode(array_values($salesByYear));
$chartDataTypes = json_encode($requestTypes);
$chartDataServices = json_encode($requestServices);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prime Facility Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #001f54 0%, #003080 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .btn-back {
            background: rgba(255,255,255,0.15);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.25);
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 30px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .summary-card .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .summary-card .card-icon.blue {
            background: #e0f2fe;
            color: #0284c7;
        }

        .summary-card .card-icon.green {
            background: #dcfce7;
            color: #16a34a;
        }

        .summary-card .card-icon.purple {
            background: #f3e8ff;
            color: #9333ea;
        }

        .summary-card .card-icon.orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .summary-card .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .summary-card .card-label {
            font-size: 0.9rem;
            color: #64748b;
        }

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 0 30px 30px;
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .chart-card.full-width {
            grid-column: 1 / -1;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .chart-controls {
            display: flex;
            gap: 8px;
        }

        .chart-btn {
            padding: 6px 14px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }

        .chart-btn:hover {
            background: #f8fafc;
        }

        .chart-btn.active {
            background: #003080;
            color: white;
            border-color: #003080;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .chart-container.pie {
            height: 350px;
        }

        /* Services List */
        .services-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .service-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .service-item:last-child {
            border-bottom: none;
        }

        .service-name {
            font-size: 0.9rem;
            color: #334155;
        }

        .service-count {
            background: #e0f2fe;
            color: #0284c7;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .progress-bar-container {
            flex: 1;
            margin: 0 15px;
            height: 8px;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #003080, #0066cc);
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Reports
            </a>
            <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
        </div>
    </header>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="card-icon blue">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="card-value">$<?= number_format($grandTotalSales, 2) ?></div>
            <div class="card-label">Total Sales</div>
        </div>
        <div class="summary-card">
            <div class="card-icon green">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="card-value"><?= $totalRequests ?></div>
            <div class="card-label">Total Requests</div>
        </div>
        <div class="summary-card">
            <div class="card-icon purple">
                <i class="fas fa-tags"></i>
            </div>
            <div class="card-value"><?= count($requestTypes) ?></div>
            <div class="card-label">Request Types</div>
        </div>
        <div class="summary-card">
            <div class="card-icon orange">
                <i class="fas fa-concierge-bell"></i>
            </div>
            <div class="card-value"><?= count($requestServices) ?></div>
            <div class="card-label">Service Types</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <!-- Sales Chart -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <div class="chart-title"><i class="fas fa-chart-bar"></i> Total Sales</div>
                <div class="chart-controls">
                    <button class="chart-btn" onclick="showSalesChart('week')">Week</button>
                    <button class="chart-btn active" onclick="showSalesChart('month')">Month</button>
                    <button class="chart-btn" onclick="showSalesChart('year')">Year</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Request Types Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title"><i class="fas fa-chart-pie"></i> Request Types</div>
            </div>
            <div class="chart-container pie">
                <canvas id="typesChart"></canvas>
            </div>
        </div>

        <!-- Requested Services -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title"><i class="fas fa-list"></i> Requested Services</div>
            </div>
            <div class="services-list">
                <?php
                $maxCount = max($requestServices);
                foreach ($requestServices as $service => $count):
                    $percentage = ($maxCount > 0) ? ($count / $maxCount) * 100 : 0;
                ?>
                <div class="service-item">
                    <span class="service-name"><?= htmlspecialchars($service) ?></span>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                    </div>
                    <span class="service-count"><?= $count ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($requestServices)): ?>
                <div class="service-item">
                    <span class="service-name" style="color: #94a3b8; font-style: italic;">No services data available</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Chart data from PHP
        const salesDataWeek = <?= $chartDataWeek ?>;
        const salesDataMonth = <?= $chartDataMonth ?>;
        const salesDataYear = <?= $chartDataYear ?>;
        const requestTypesData = <?= $chartDataTypes ?>;

        // Colors for charts
        const colors = {
            primary: '#003080',
            secondary: '#0066cc',
            pieColors: ['#003080', '#0066cc', '#00a3cc', '#00cc99', '#66cc00', '#cc9900', '#cc6600', '#cc3300']
        };

        // Sales Chart
        let salesChart;
        const salesCtx = document.getElementById('salesChart').getContext('2d');

        function createSalesChart(data) {
            if (salesChart) {
                salesChart.destroy();
            }

            const labels = data.map(item => item.label);
            const values = data.map(item => item.total);
            const counts = data.map(item => item.count);

            salesChart = new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Sales ($)',
                        data: values,
                        backgroundColor: colors.primary,
                        borderColor: colors.primary,
                        borderWidth: 1,
                        borderRadius: 6,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const total = context.parsed.y;
                                    const count = counts[index];
                                    return [
                                        `Total: $${total.toLocaleString('en-US', {minimumFractionDigits: 2})}`,
                                        `Requests: ${count}`
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function showSalesChart(period) {
            // Update active button
            document.querySelectorAll('.chart-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Show appropriate data
            switch(period) {
                case 'week':
                    createSalesChart(salesDataWeek);
                    break;
                case 'month':
                    createSalesChart(salesDataMonth);
                    break;
                case 'year':
                    createSalesChart(salesDataYear);
                    break;
            }
        }

        // Initialize with month view
        createSalesChart(salesDataMonth);

        // Request Types Pie Chart
        const typesCtx = document.getElementById('typesChart').getContext('2d');
        const typeLabels = Object.keys(requestTypesData);
        const typeValues = Object.values(requestTypesData);

        new Chart(typesCtx, {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: colors.pieColors.slice(0, typeLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
