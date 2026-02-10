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
        seller,
        created_at,
        Work_Date,
        client_name,
        company_name,
        service_status
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
$sellers = [];
$topClients = [];
$statusCounts = [];

// Store raw data per request for client-side filtering
$rawData = [];

foreach ($requests as $row) {
    $total = getRequestTotal($row);
    $sellerName = $row['seller'] ?: 'Unknown';
    $clientName = $row['company_name'] ?: ($row['client_name'] ?: 'Unknown');
    $serviceStatus = $row['service_status'] ?: 'pending';
    // Use Work_Date if available, otherwise fallback to created_at
    $dateValue = !empty($row['Work_Date']) ? $row['Work_Date'] : $row['created_at'];
    $date = new DateTime($dateValue);

    // Store raw data for client-side filtering
    $rawData[] = [
        'total' => $total,
        'seller' => $sellerName,
        'request_type' => $row['request_type'] ?: 'Unknown',
        'requested_service' => $row['requested_service'] ?: 'Unknown',
        'client_name' => $clientName,
        'service_status' => $serviceStatus,
        'weekKey' => $date->format('Y-W'),
        'weekLabel' => 'Week ' . $date->format('W') . ', ' . $date->format('Y'),
        'monthKey' => $date->format('Y-m'),
        'monthLabel' => $date->format('M Y'),
        'yearKey' => $date->format('Y'),
        'yearLabel' => $date->format('Y')
    ];

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

    // Sellers
    if (!isset($sellers[$sellerName])) {
        $sellers[$sellerName] = ['count' => 0, 'total' => 0];
    }
    $sellers[$sellerName]['count']++;
    $sellers[$sellerName]['total'] += $total;

    // Top clients
    if (!isset($topClients[$clientName])) {
        $topClients[$clientName] = ['count' => 0, 'total' => 0];
    }
    $topClients[$clientName]['count']++;
    $topClients[$clientName]['total'] += $total;

    // Status counts
    if (!isset($statusCounts[$serviceStatus])) {
        $statusCounts[$serviceStatus] = 0;
    }
    $statusCounts[$serviceStatus]++;
}

// Sort data
ksort($salesByWeek);
ksort($salesByMonth);
ksort($salesByYear);
arsort($sellers);

// Sort top clients by count descending
uasort($topClients, function($a, $b) { return $b['count'] - $a['count']; });

// Keep only last 12 weeks/months
$salesByWeek = array_slice($salesByWeek, -12, 12, true);
$salesByMonth = array_slice($salesByMonth, -12, 12, true);

// Calculate grand totals
$grandTotalSales = array_sum(array_map('getRequestTotal', $requests));
$totalRequests = count($requests);

// Get top client info
$topClientName = !empty($topClients) ? array_key_first($topClients) : 'N/A';
$topClientCount = !empty($topClients) ? $topClients[$topClientName]['count'] : 0;

// Prepare JSON data for charts
$chartDataWeek = json_encode(array_values($salesByWeek));
$chartDataMonth = json_encode(array_values($salesByMonth));
$chartDataYear = json_encode(array_values($salesByYear));
$chartDataSellers = json_encode($sellers);
$chartRawData = json_encode($rawData);
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
            align-items: start;
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

        /* Top Client card value - smaller font for names */
        .summary-card .card-value.client-name {
            font-size: 1.15rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Status Filter Card */
        .status-filter-card .card-label-title {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 12px;
        }

        .status-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .status-pill {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.73rem;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            transition: all 0.2s;
            white-space: nowrap;
            user-select: none;
        }

        .status-pill:hover {
            background: #e2e8f0;
        }

        .status-pill.active {
            background: #003080;
            color: white;
            border-color: #003080;
        }

        .status-pill .pill-count {
            font-weight: 600;
            margin-left: 2px;
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
            cursor: pointer;
        }

        /* Top Clients List */
        .clients-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .client-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .client-item:last-child {
            border-bottom: none;
        }

        .client-rank {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .client-rank.top-3 {
            background: #003080;
            color: white;
        }

        .client-info {
            flex: 1;
            margin: 0 12px;
            min-width: 0;
        }

        .client-name {
            font-size: 0.9rem;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .client-total {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .client-count {
            background: #e0f2fe;
            color: #0284c7;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            flex-shrink: 0;
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
            <div class="card-value" id="cardTotalSales">$<?= number_format($grandTotalSales, 2) ?></div>
            <div class="card-label">Total Sales</div>
        </div>
        <div class="summary-card">
            <div class="card-icon green">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="card-value" id="cardTotalRequests"><?= $totalRequests ?></div>
            <div class="card-label">Total Requests</div>
        </div>
        <!-- Top Client Card (replaces Request Types) -->
        <div class="summary-card">
            <div class="card-icon purple">
                <i class="fas fa-crown"></i>
            </div>
            <div class="card-value client-name" id="cardTopClientName"><?= htmlspecialchars($topClientName) ?></div>
            <div class="card-label"><span id="cardTopClientOrders"><?= $topClientCount ?></span> orders &mdash; Top Client</div>
        </div>
        <!-- Status Filter Card (replaces Service Types) -->
        <div class="summary-card status-filter-card">
            <div class="card-icon orange">
                <i class="fas fa-filter"></i>
            </div>
            <div class="card-label-title">Status</div>
            <div class="status-pills" id="statusPills">
                <span class="status-pill active" data-status="all" onclick="filterByStatus('all')">All <span class="pill-count"><?= $totalRequests ?></span></span>
                <span class="status-pill" data-status="pending" onclick="filterByStatus('pending')">Pending <span class="pill-count"><?= $statusCounts['pending'] ?? 0 ?></span></span>
                <span class="status-pill" data-status="scheduled" onclick="filterByStatus('scheduled')">Scheduled <span class="pill-count"><?= $statusCounts['scheduled'] ?? 0 ?></span></span>
                <span class="status-pill" data-status="confirmed" onclick="filterByStatus('confirmed')">Confirmed <span class="pill-count"><?= $statusCounts['confirmed'] ?? 0 ?></span></span>
                <span class="status-pill" data-status="in_progress" onclick="filterByStatus('in_progress')">In Progress <span class="pill-count"><?= $statusCounts['in_progress'] ?? 0 ?></span></span>
                <span class="status-pill" data-status="completed" onclick="filterByStatus('completed')">Completed <span class="pill-count"><?= $statusCounts['completed'] ?? 0 ?></span></span>
                <span class="status-pill" data-status="not_completed" onclick="filterByStatus('not_completed')">Not Completed <span class="pill-count"><?= $statusCounts['not_completed'] ?? 0 ?></span></span>
                <span class="status-pill" data-status="cancelled" onclick="filterByStatus('cancelled')">Cancelled <span class="pill-count"><?= $statusCounts['cancelled'] ?? 0 ?></span></span>
            </div>
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

        <!-- Sellers Pie Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title"><i class="fas fa-chart-pie"></i> Sellers</div>
                <div id="sellerFilterIndicator" style="display:none;">
                    <span id="sellerFilterName" style="font-size:0.85rem; color:#003080; font-weight:600;"></span>
                    <button onclick="clearSellerFilter()" style="background:#ef4444; color:white; border:none; padding:4px 10px; border-radius:4px; font-size:0.75rem; cursor:pointer; margin-left:8px;">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
            <div class="chart-container pie">
                <canvas id="sellersChart"></canvas>
            </div>
            <p style="text-align:center; font-size:0.75rem; color:#94a3b8; margin-top:8px;">Click a seller to filter all charts</p>
        </div>

        <!-- Top Clients (replaces Requested Services) -->
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title"><i class="fas fa-trophy"></i> Top Clients</div>
            </div>
            <div class="clients-list" id="topClientsList">
                <?php
                $topClientArr = array_slice($topClients, 0, 10, true);
                $rank = 0;
                $maxCount = !empty($topClientArr) ? reset($topClientArr)['count'] : 0;
                foreach ($topClientArr as $client => $data):
                    $rank++;
                ?>
                <div class="client-item">
                    <span class="client-rank <?= $rank <= 3 ? 'top-3' : '' ?>"><?= $rank ?></span>
                    <div class="client-info">
                        <div class="client-name"><?= htmlspecialchars($client) ?></div>
                        <div class="client-total">$<?= number_format($data['total'], 2) ?></div>
                    </div>
                    <span class="client-count"><?= $data['count'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($topClients)): ?>
                <div class="client-item">
                    <span class="client-name" style="color: #94a3b8; font-style: italic;">No client data available</span>
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
        const sellersData = <?= $chartDataSellers ?>;
        const rawData = <?= $chartRawData ?>;

        // Current state
        let currentPeriod = 'month';
        let activeSeller = null;
        let activeStatus = null;

        // Colors for charts
        const colors = {
            primary: '#003080',
            secondary: '#0066cc',
            pieColors: ['#003080', '#0066cc', '#00a3cc', '#00cc99', '#66cc00', '#cc9900', '#cc6600', '#cc3300', '#9933cc', '#ff6699']
        };

        // ============ FILTER LOGIC ============
        function getFilteredData() {
            let data = rawData;
            if (activeSeller) {
                data = data.filter(item => item.seller === activeSeller);
            }
            if (activeStatus) {
                data = data.filter(item => item.service_status === activeStatus);
            }
            return data;
        }

        function aggregateSalesData(data, keyField, labelField) {
            const agg = {};
            data.forEach(item => {
                const key = item[keyField];
                if (!agg[key]) {
                    agg[key] = { label: item[labelField], total: 0, count: 0 };
                }
                agg[key].total += item.total;
                agg[key].count++;
            });
            const sorted = Object.keys(agg).sort();
            return sorted.map(k => agg[k]);
        }

        function aggregateClients(data) {
            const agg = {};
            data.forEach(item => {
                const client = item.client_name;
                if (!agg[client]) agg[client] = { count: 0, total: 0 };
                agg[client].count++;
                agg[client].total += item.total;
            });
            return Object.entries(agg).sort((a, b) => b[1].count - a[1].count);
        }

        // ============ SELLER FILTER ============
        function filterBySeller(sellerName) {
            activeSeller = sellerName;
            document.getElementById('sellerFilterIndicator').style.display = 'flex';
            document.getElementById('sellerFilterIndicator').style.alignItems = 'center';
            document.getElementById('sellerFilterIndicator').style.gap = '8px';
            document.getElementById('sellerFilterName').textContent = 'Filtered: ' + sellerName;
            refreshAllCharts();
        }

        function clearSellerFilter() {
            activeSeller = null;
            document.getElementById('sellerFilterIndicator').style.display = 'none';
            refreshAllCharts();
        }

        // ============ STATUS FILTER ============
        function filterByStatus(status) {
            if (status === 'all' || activeStatus === status) {
                activeStatus = null;
            } else {
                activeStatus = status;
            }
            updateStatusPillsUI();
            refreshAllCharts();
        }

        function updateStatusPillsUI() {
            document.querySelectorAll('.status-pill').forEach(pill => {
                pill.classList.remove('active');
                if ((!activeStatus && pill.dataset.status === 'all') ||
                    pill.dataset.status === activeStatus) {
                    pill.classList.add('active');
                }
            });
        }

        function updateStatusPillCounts() {
            // Counts based on data filtered by seller only (not by status)
            let sellerFiltered = rawData;
            if (activeSeller) {
                sellerFiltered = sellerFiltered.filter(item => item.seller === activeSeller);
            }
            const counts = {};
            sellerFiltered.forEach(item => {
                const s = item.service_status;
                if (!counts[s]) counts[s] = 0;
                counts[s]++;
            });
            document.querySelectorAll('.status-pill').forEach(pill => {
                const status = pill.dataset.status;
                const countEl = pill.querySelector('.pill-count');
                if (countEl) {
                    if (status === 'all') {
                        countEl.textContent = sellerFiltered.length;
                    } else {
                        countEl.textContent = counts[status] || 0;
                    }
                }
            });
        }

        // ============ REFRESH ALL ============
        function refreshAllCharts() {
            const filtered = getFilteredData();

            // Update summary cards
            const totalSales = filtered.reduce((sum, item) => sum + item.total, 0);
            const totalReqs = filtered.length;

            document.getElementById('cardTotalSales').textContent = '$' + totalSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('cardTotalRequests').textContent = totalReqs;

            // Update top client card
            const clients = aggregateClients(filtered);
            const topClient = clients.length > 0 ? clients[0] : null;
            document.getElementById('cardTopClientName').textContent = topClient ? topClient[0] : 'N/A';
            document.getElementById('cardTopClientOrders').textContent = topClient ? topClient[1].count : 0;

            // Update status pill counts
            updateStatusPillCounts();

            // Rebuild sales chart
            showSalesChart(currentPeriod);

            // Rebuild top clients list
            rebuildTopClientsList(filtered);
        }

        // ============ SALES CHART ============
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
                        backgroundColor: (activeSeller || activeStatus) ? colors.secondary : colors.primary,
                        borderColor: (activeSeller || activeStatus) ? colors.secondary : colors.primary,
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
                                    const lines = [
                                        `Total: $${total.toLocaleString('en-US', {minimumFractionDigits: 2})}`,
                                        `Requests: ${count}`
                                    ];
                                    if (activeSeller) lines.push(`Seller: ${activeSeller}`);
                                    if (activeStatus) lines.push(`Status: ${activeStatus.replace('_', ' ')}`);
                                    return lines;
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
            currentPeriod = period;

            // Update active button
            document.querySelectorAll('.chart-btn').forEach(btn => btn.classList.remove('active'));
            const btns = document.querySelectorAll('.chart-btn');
            if (period === 'week') btns[0].classList.add('active');
            else if (period === 'month') btns[1].classList.add('active');
            else if (period === 'year') btns[2].classList.add('active');

            const filtered = getFilteredData();
            let keyField, labelField;
            switch(period) {
                case 'week': keyField = 'weekKey'; labelField = 'weekLabel'; break;
                case 'month': keyField = 'monthKey'; labelField = 'monthLabel'; break;
                case 'year': keyField = 'yearKey'; labelField = 'yearLabel'; break;
            }
            let data = aggregateSalesData(filtered, keyField, labelField);
            // Keep last 12 for week/month
            if (period !== 'year' && data.length > 12) {
                data = data.slice(-12);
            }
            createSalesChart(data);
        }

        // Initialize with month view
        showSalesChart('month');

        // ============ SELLERS PIE CHART ============
        const sellersCtx = document.getElementById('sellersChart').getContext('2d');
        const sellerLabels = Object.keys(sellersData);
        const sellerCounts = sellerLabels.map(s => sellersData[s].count);

        const sellersChart = new Chart(sellersCtx, {
            type: 'pie',
            data: {
                labels: sellerLabels,
                datasets: [{
                    data: sellerCounts,
                    backgroundColor: colors.pieColors.slice(0, sellerLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: function(event, elements) {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const clickedSeller = sellerLabels[index];
                        if (activeSeller === clickedSeller) {
                            clearSellerFilter();
                        } else {
                            filterBySeller(clickedSeller);
                        }
                    }
                },
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
                                const sellerName = context.label;
                                const sellerTotal = sellersData[sellerName]?.total || 0;
                                return [
                                    `${sellerName}: ${value} requests (${percentage}%)`,
                                    `Sales: $${sellerTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}`
                                ];
                            }
                        }
                    }
                }
            }
        });

        // ============ TOP CLIENTS LIST REBUILD ============
        function rebuildTopClientsList(filtered) {
            const clients = aggregateClients(filtered);
            const container = document.getElementById('topClientsList');
            if (clients.length === 0) {
                container.innerHTML = '<div class="client-item"><span class="client-name" style="color:#94a3b8; font-style:italic;">No client data available</span></div>';
                return;
            }
            const top10 = clients.slice(0, 10);
            let html = '';
            top10.forEach(([name, data], index) => {
                const rank = index + 1;
                html += `<div class="client-item">
                    <span class="client-rank ${rank <= 3 ? 'top-3' : ''}">${rank}</span>
                    <div class="client-info">
                        <div class="client-name">${escapeHtml(name)}</div>
                        <div class="client-total">$${data.total.toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                    </div>
                    <span class="client-count">${data.count}</span>
                </div>`;
            });
            container.innerHTML = html;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
