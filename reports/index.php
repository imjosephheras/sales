<?php
/**
 * Reports Module - Modern Excel-like Data View
 * Displays contract request data in a clean spreadsheet format
 */

// Include database configuration
require_once __DIR__ . '/../form_contract/db_config.php';

// Get database connection
$pdo = getDBConnection();

// Fetch all requests
$stmt = $pdo->query("
    SELECT
        Service_Type,
        Request_Type,
        Requested_Service,
        client_name,
        Client_Title,
        Email,
        Number_Phone,
        Company_Name,
        Company_Address,
        Invoice_Frequency,
        Contract_Duration,
        Seller,
        total18,
        taxes18,
        grand18,
        total19,
        taxes19,
        grand19,
        inflationAdjustment,
        totalArea,
        buildingsIncluded,
        startDateServices,
        Site_Observation,
        Additional_Comments,
        created_at
    FROM requests
    ORDER BY created_at DESC
");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Column definitions (question -> database field)
$columns = [
    'Service Type' => 'Service_Type',
    'Request Type' => 'Request_Type',
    'Requested Service' => 'Requested_Service',
    'Client Name' => 'client_name',
    'Client Title' => 'Client_Title',
    'Email' => 'Email',
    'Phone Number' => 'Number_Phone',
    'Company Name' => 'Company_Name',
    'Company Address' => 'Company_Address',
    'Invoice Frequency' => 'Invoice_Frequency',
    'Contract Duration' => 'Contract_Duration',
    'Seller' => 'Seller',
    'Total' => 'calculated_total',
    'Taxes (8.25%)' => 'calculated_taxes',
    'Grand Total' => 'calculated_grand',
    'Inflation Adjustment' => 'inflationAdjustment',
    'Total Area (sq ft)' => 'totalArea',
    'Buildings Included' => 'buildingsIncluded',
    'Start Date' => 'startDateServices',
    'Site Observation' => 'Site_Observation',
    'Additional Comments' => 'Additional_Comments'
];

// Helper function to calculate totals from sections 18 and 19
function calculateTotals($row) {
    $total18 = floatval(str_replace(['$', ','], '', $row['total18'] ?? '0'));
    $total19 = floatval(str_replace(['$', ','], '', $row['total19'] ?? '0'));
    $taxes18 = floatval(str_replace(['$', ','], '', $row['taxes18'] ?? '0'));
    $taxes19 = floatval(str_replace(['$', ','], '', $row['taxes19'] ?? '0'));
    $grand18 = floatval(str_replace(['$', ','], '', $row['grand18'] ?? '0'));
    $grand19 = floatval(str_replace(['$', ','], '', $row['grand19'] ?? '0'));

    return [
        'total' => $total18 + $total19,
        'taxes' => $taxes18 + $taxes19,
        'grand' => $grand18 + $grand19
    ];
}

// Format currency
function formatCurrency($value) {
    if ($value == 0) return '-';
    return '$' . number_format($value, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Prime Facility Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .header-stats {
            display: flex;
            gap: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-label {
            font-size: 0.75rem;
            opacity: 0.8;
            text-transform: uppercase;
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

        /* Toolbar */
        .toolbar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e9ef;
        }

        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #dde2e8;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #003080;
            box-shadow: 0 0 0 3px rgba(0,48,128,0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: #003080;
            color: white;
        }

        .btn-primary:hover {
            background: #001f54;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #dde2e8;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        /* Table Container */
        .table-container {
            margin: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
            max-height: calc(100vh - 220px);
        }

        /* Modern Spreadsheet Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .data-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table th {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
            position: relative;
        }

        .data-table th:hover {
            background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
        }

        .data-table th .sort-icon {
            margin-left: 6px;
            opacity: 0.4;
            font-size: 0.7rem;
        }

        .data-table th:hover .sort-icon {
            opacity: 1;
        }

        .data-table tbody tr {
            transition: background 0.15s;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        .data-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .data-table tbody tr:nth-child(even):hover {
            background: #f1f5f9;
        }

        .data-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .data-table td:hover {
            overflow: visible;
            white-space: normal;
            background: #fffde7;
            position: relative;
            z-index: 5;
        }

        /* Row number column */
        .row-num {
            background: #f8fafc !important;
            color: #94a3b8;
            font-weight: 500;
            text-align: center;
            width: 50px;
            min-width: 50px;
            border-right: 1px solid #e2e8f0;
        }

        .data-table thead th.row-num {
            background: linear-gradient(180deg, #e2e8f0 0%, #d1d5db 100%) !important;
        }

        /* Cell types */
        .cell-currency {
            font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
            text-align: right;
            color: #059669;
            font-weight: 500;
        }

        .cell-date {
            font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
            color: #6366f1;
        }

        .cell-email {
            color: #2563eb;
        }

        .cell-phone {
            font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;
        }

        .cell-empty {
            color: #cbd5e1;
            font-style: italic;
        }

        /* Resize handle */
        .resize-handle {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 5px;
            cursor: col-resize;
            background: transparent;
        }

        .resize-handle:hover {
            background: #003080;
        }

        /* Status bar */
        .status-bar {
            background: #f8fafc;
            padding: 10px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #64748b;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            margin-bottom: 10px;
            color: #475569;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
            }

            .toolbar {
                flex-direction: column;
                gap: 15px;
            }

            .search-box {
                width: 100%;
            }
        }

        /* Print styles */
        @media print {
            .header, .toolbar {
                display: none;
            }

            .table-container {
                margin: 0;
                box-shadow: none;
            }

            .data-table td {
                white-space: normal;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <a href="../" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            <h1><i class="fas fa-table"></i> Reports</h1>
        </div>
        <div class="header-stats">
            <div class="stat-item">
                <div class="stat-value"><?= count($requests) ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= count($columns) ?></div>
                <div class="stat-label">Columns</div>
            </div>
        </div>
    </header>

    <div class="toolbar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search records...">
        </div>
        <div class="toolbar-actions">
            <button class="btn btn-secondary" onclick="exportCSV()">
                <i class="fas fa-download"></i>
                Export CSV
            </button>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-chart-pie"></i>
                Dashboard
            </a>
            <button class="btn btn-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i>
                Refresh
            </button>
        </div>
    </div>

    <div class="table-container">
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Records Found</h3>
                <p>There are no contract requests in the database yet.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="data-table" id="dataTable">
                    <thead>
                        <tr>
                            <th class="row-num">#</th>
                            <?php foreach ($columns as $label => $field): ?>
                                <th>
                                    <?= htmlspecialchars($label) ?>
                                    <i class="fas fa-sort sort-icon"></i>
                                    <div class="resize-handle"></div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $index => $row):
                            $totals = calculateTotals($row);
                        ?>
                            <tr>
                                <td class="row-num"><?= $index + 1 ?></td>
                                <?php foreach ($columns as $label => $field): ?>
                                    <?php
                                    // Handle calculated fields
                                    if ($field === 'calculated_total') {
                                        $value = formatCurrency($totals['total']);
                                        $cellClass = 'cell-currency';
                                    } elseif ($field === 'calculated_taxes') {
                                        $value = formatCurrency($totals['taxes']);
                                        $cellClass = 'cell-currency';
                                    } elseif ($field === 'calculated_grand') {
                                        $value = formatCurrency($totals['grand']);
                                        $cellClass = 'cell-currency';
                                    } else {
                                        $value = $row[$field] ?? '';
                                        $cellClass = '';

                                        // Apply specific cell types
                                        if ($field === 'Email') {
                                            $cellClass = 'cell-email';
                                        } elseif ($field === 'Number_Phone') {
                                            $cellClass = 'cell-phone';
                                        } elseif ($field === 'startDateServices') {
                                            $cellClass = 'cell-date';
                                            if ($value) {
                                                $date = new DateTime($value);
                                                $value = $date->format('M d, Y');
                                            }
                                        }
                                    }

                                    if (empty($value) || $value === '-') {
                                        $cellClass .= ' cell-empty';
                                        $value = '-';
                                    }
                                    ?>
                                    <td class="<?= trim($cellClass) ?>" title="<?= htmlspecialchars($value) ?>">
                                        <?= htmlspecialchars($value) ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="status-bar">
                <span>Showing <?= count($requests) ?> of <?= count($requests) ?> records</span>
                <span>Last updated: <?= date('M d, Y H:i') ?></span>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#dataTable tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const isVisible = text.includes(searchTerm);
                row.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleCount++;
            });

            // Update status bar
            const statusBar = document.querySelector('.status-bar span:first-child');
            statusBar.textContent = `Showing ${visibleCount} of ${rows.length} records`;
        });

        // Export to CSV
        function exportCSV() {
            const table = document.getElementById('dataTable');
            const rows = table.querySelectorAll('tr');
            let csv = [];

            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                const rowData = [];
                cells.forEach((cell, index) => {
                    if (index === 0) return; // Skip row numbers
                    let text = cell.textContent.trim();
                    // Escape quotes and wrap in quotes if contains comma
                    if (text.includes(',') || text.includes('"')) {
                        text = '"' + text.replace(/"/g, '""') + '"';
                    }
                    rowData.push(text);
                });
                csv.push(rowData.join(','));
            });

            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);

            link.setAttribute('href', url);
            link.setAttribute('download', 'reports_' + new Date().toISOString().split('T')[0] + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Column sorting
        document.querySelectorAll('#dataTable th:not(.row-num)').forEach((th, index) => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', function() {
                sortTable(index + 1); // +1 because of row number column
            });
        });

        let sortDirection = {};

        function sortTable(columnIndex) {
            const table = document.getElementById('dataTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            sortDirection[columnIndex] = !sortDirection[columnIndex];
            const direction = sortDirection[columnIndex] ? 1 : -1;

            rows.sort((a, b) => {
                const aVal = a.cells[columnIndex].textContent.trim();
                const bVal = b.cells[columnIndex].textContent.trim();

                // Try numeric sort first
                const aNum = parseFloat(aVal.replace(/[$,]/g, ''));
                const bNum = parseFloat(bVal.replace(/[$,]/g, ''));

                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return (aNum - bNum) * direction;
                }

                return aVal.localeCompare(bVal) * direction;
            });

            // Re-append sorted rows and update row numbers
            rows.forEach((row, index) => {
                row.cells[0].textContent = index + 1;
                tbody.appendChild(row);
            });

            // Update sort icons
            document.querySelectorAll('#dataTable th .sort-icon').forEach(icon => {
                icon.className = 'fas fa-sort sort-icon';
            });
            const th = table.querySelectorAll('th')[columnIndex];
            const icon = th.querySelector('.sort-icon');
            icon.className = sortDirection[columnIndex] ?
                'fas fa-sort-up sort-icon' : 'fas fa-sort-down sort-icon';
        }

        // Column resize
        let isResizing = false;
        let currentTh = null;
        let startX = 0;
        let startWidth = 0;

        document.querySelectorAll('.resize-handle').forEach(handle => {
            handle.addEventListener('mousedown', function(e) {
                e.stopPropagation();
                isResizing = true;
                currentTh = this.parentElement;
                startX = e.pageX;
                startWidth = currentTh.offsetWidth;
                document.body.style.cursor = 'col-resize';
            });
        });

        document.addEventListener('mousemove', function(e) {
            if (!isResizing) return;
            const width = startWidth + (e.pageX - startX);
            if (width > 50) {
                currentTh.style.width = width + 'px';
                currentTh.style.minWidth = width + 'px';
            }
        });

        document.addEventListener('mouseup', function() {
            isResizing = false;
            currentTh = null;
            document.body.style.cursor = '';
        });
    </script>
</body>
</html>
