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
        status,
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
$requestTypes = [];
$requestServices = [];
$sellers = [];
$topClients = [];

// Store raw data per request for client-side filtering
$rawData = [];

foreach ($requests as $row) {
    $total = getRequestTotal($row);
    $sellerName = $row['seller'] ?: 'Unknown';
    // Use Work_Date if available, otherwise fallback to created_at
    $dateValue = !empty($row['Work_Date']) ? $row['Work_Date'] : $row['created_at'];
    $date = new DateTime($dateValue);

    // Determine effective status
    $effectiveStatus = 'pending';
    if (strtolower($row['status'] ?? '') === 'cancelled') {
        $effectiveStatus = 'cancelled';
    } elseif (!empty($row['service_status'])) {
        $effectiveStatus = $row['service_status'];
    }

    // Client name (prefer client_name)
    $clientName = $row['client_name'] ?: ($row['company_name'] ?: 'Unknown');

    // Store raw data for client-side filtering
    $rawData[] = [
        'total' => $total,
        'seller' => $sellerName,
        'request_type' => $row['request_type'] ?: 'Unknown',
        'requested_service' => $row['requested_service'] ?: 'Unknown',
        'weekKey' => $date->format('Y-W'),
        'weekLabel' => 'Week ' . $date->format('W') . ', ' . $date->format('Y'),
        'monthKey' => $date->format('Y-m'),
        'monthLabel' => $date->format('M Y'),
        'yearKey' => $date->format('Y'),
        'yearLabel' => $date->format('Y'),
        'client' => $clientName,
        'service_status' => $effectiveStatus
    ];

    // Top clients aggregation
    if (!isset($topClients[$clientName])) {
        $topClients[$clientName] = 0;
    }
    $topClients[$clientName]++;

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

    // Sellers
    if (!isset($sellers[$sellerName])) {
        $sellers[$sellerName] = ['count' => 0, 'total' => 0];
    }
    $sellers[$sellerName]['count']++;
    $sellers[$sellerName]['total'] += $total;

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
arsort($sellers);
arsort($topClients);
$topClients = array_slice($topClients, 0, 5, true);

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
            cursor: pointer;
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

        /* Status Filter Chips */
        .status-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .status-chip {
            padding: 4px 10px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 20px;
            font-size: 0.72rem;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .status-chip:hover {
            background: #f1f5f9;
        }

        .status-chip.active {
            color: white;
            border-color: transparent;
        }

        .status-chip.active[data-status=""] { background: #003080; }
        .status-chip.active[data-status="pending"] { background: #d97706; }
        .status-chip.active[data-status="scheduled"] { background: #2563eb; }
        .status-chip.active[data-status="confirmed"] { background: #7c3aed; }
        .status-chip.active[data-status="in_progress"] { background: #0891b2; }
        .status-chip.active[data-status="completed"] { background: #16a34a; }
        .status-chip.active[data-status="not_completed"] { background: #dc2626; }
        .status-chip.active[data-status="cancelled"] { background: #6b7280; }

        /* Top Clients Mini List */
        .top-clients-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .top-client-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
        }

        .top-client-name {
            color: #334155;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 180px;
        }

        .top-client-count {
            color: #9333ea;
            font-weight: 700;
            font-size: 0.85rem;
            min-width: 20px;
            text-align: right;
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
                <i class="fas fa-users"></i>
            </div>
            <div class="card-label" style="font-weight:600; margin-bottom:10px; font-size:0.95rem; color:#1e293b;">Top Clients</div>
            <div id="topClientsList" class="top-clients-list">
                <?php foreach ($topClients as $client => $count): ?>
                <div class="top-client-item">
                    <span class="top-client-name" title="<?= htmlspecialchars($client) ?>"><?= htmlspecialchars($client) ?></span>
                    <span class="top-client-count"><?= $count ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($topClients)): ?>
                <div class="top-client-item"><span class="top-client-name" style="color:#94a3b8; font-style:italic;">No data</span></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="summary-card">
            <div class="card-icon orange">
                <i class="fas fa-filter"></i>
            </div>
            <div class="card-label" style="font-weight:600; margin-bottom:10px; font-size:0.95rem; color:#1e293b;">Status</div>
            <div id="statusFilters" class="status-filters">
                <button class="status-chip active" data-status="" onclick="filterByStatus(null)">All</button>
                <button class="status-chip" data-status="pending" onclick="filterByStatus('pending')">Pending</button>
                <button class="status-chip" data-status="scheduled" onclick="filterByStatus('scheduled')">Scheduled</button>
                <button class="status-chip" data-status="confirmed" onclick="filterByStatus('confirmed')">Confirmed</button>
                <button class="status-chip" data-status="in_progress" onclick="filterByStatus('in_progress')">In Progress</button>
                <button class="status-chip" data-status="completed" onclick="filterByStatus('completed')">Completed</button>
                <button class="status-chip" data-status="not_completed" onclick="filterByStatus('not_completed')">Not Completed</button>
                <button class="status-chip" data-status="cancelled" onclick="filterByStatus('cancelled')">Cancelled</button>
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

        // ============ SELLER FILTER LOGIC ============
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
            // Sort by key and return values
            const sorted = Object.keys(agg).sort();
            return sorted.map(k => agg[k]);
        }

        function aggregateServices(data) {
            const agg = {};
            data.forEach(item => {
                const svc = item.requested_service;
                if (!agg[svc]) agg[svc] = 0;
                agg[svc]++;
            });
            // Sort descending by count
            const sorted = Object.entries(agg).sort((a, b) => b[1] - a[1]);
            return sorted;
        }

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

        function refreshAllCharts() {
            const filtered = getFilteredData();

            // Update summary cards
            const totalSales = filtered.reduce((sum, item) => sum + item.total, 0);
            const totalReqs = filtered.length;

            const cardValues = document.querySelectorAll('.summary-card .card-value');
            cardValues[0].textContent = '$' + totalSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            cardValues[1].textContent = totalReqs;

            // Rebuild top clients list with filtered data
            rebuildTopClients(filtered);

            // Rebuild sales chart
            showSalesChart(currentPeriod);

            // Rebuild services list
            rebuildServicesList(filtered);
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
                        backgroundColor: activeSeller ? colors.secondary : colors.primary,
                        borderColor: activeSeller ? colors.secondary : colors.primary,
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

        // ============ SERVICES LIST REBUILD ============
        function rebuildServicesList(filtered) {
            const services = aggregateServices(filtered);
            const container = document.querySelector('.services-list');
            if (services.length === 0) {
                container.innerHTML = '<div class="service-item"><span class="service-name" style="color:#94a3b8; font-style:italic;">No services data available</span></div>';
                return;
            }
            const maxCount = services[0][1];
            let html = '';
            services.forEach(([name, count]) => {
                const pct = maxCount > 0 ? (count / maxCount) * 100 : 0;
                html += `<div class="service-item">
                    <span class="service-name">${escapeHtml(name)}</span>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width:${pct}%"></div>
                    </div>
                    <span class="service-count">${count}</span>
                </div>`;
            });
            container.innerHTML = html;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ============ STATUS FILTER ============
        function filterByStatus(status) {
            activeStatus = status;
            document.querySelectorAll('.status-chip').forEach(chip => {
                chip.classList.remove('active');
                if (chip.getAttribute('data-status') === (status || '')) {
                    chip.classList.add('active');
                }
            });
            refreshAllCharts();
        }

        // ============ TOP CLIENTS REBUILD ============
        function rebuildTopClients(filtered) {
            const clients = {};
            filtered.forEach(item => {
                if (!clients[item.client]) clients[item.client] = 0;
                clients[item.client]++;
            });
            const sorted = Object.entries(clients).sort((a, b) => b[1] - a[1]).slice(0, 5);
            const container = document.getElementById('topClientsList');
            if (sorted.length === 0) {
                container.innerHTML = '<div class="top-client-item"><span class="top-client-name" style="color:#94a3b8; font-style:italic;">No data</span></div>';
                return;
            }
            let html = '';
            sorted.forEach(([name, count]) => {
                html += `<div class="top-client-item">
                    <span class="top-client-name" title="${escapeHtml(name)}">${escapeHtml(name)}</span>
                    <span class="top-client-count">${count}</span>
                </div>`;
            });
            container.innerHTML = html;
        }
    </script>
</body>
</html>
