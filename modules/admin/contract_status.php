<?php
/**
 * CONTRACT STATUS - Read-only informational view
 *
 * Shows contract status and accounting status.
 * Admin CANNOT modify, resend, regenerate PDF, or alter states.
 * This is purely informational.
 */
require_once __DIR__ . '/../../app/bootstrap.php';
Middleware::role(1);

$page_title = 'Contract Status';
$page_icon  = 'fas fa-eye';
$back_url   = url('/modules/admin/');
$back_label = 'Admin Panel';

// Database connection
$dbHost = 'localhost';
$dbName = 'form';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser, $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Get all completed contracts with their billing status
$sql = "
    SELECT
        f.form_id AS id,
        f.docnum,
        f.company_name AS Company_Name,
        f.client_name AS Client_Name,
        f.requested_service AS Requested_Service,
        f.request_type AS Request_Type,
        f.service_type AS Service_Type,
        f.status AS contract_status,
        f.completed_at AS contract_completed_at,
        f.final_pdf_path,
        f.grand_total AS PriceInput,
        b.id AS billing_id,
        b.status AS billing_status,
        b.completed_at AS billing_completed_at,
        b.completed_by_name AS billing_completed_by,
        b.created_at AS billing_created_at
    FROM forms f
    LEFT JOIN billing_documents b ON b.form_id = f.form_id
    WHERE f.status IN ('ready', 'completed')
    ORDER BY f.completed_at DESC, f.updated_at DESC
";

$stmt = $pdo->query($sql);
$contracts = $stmt->fetchAll();

// Count stats
$totalContracts = count($contracts);
$completedContracts = 0;
$pendingBilling = 0;
$completedBilling = 0;

foreach ($contracts as $c) {
    if ($c['contract_status'] === 'completed') $completedContracts++;
    if ($c['billing_status'] === 'pending') $pendingBilling++;
    if ($c['billing_status'] === 'completed') $completedBilling++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Status - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #001f54 0%, #a30000 100%);
            min-height: 100vh;
            color: #333;
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-title {
            color: white;
            font-size: 1.4rem;
            margin-bottom: 8px;
            text-align: center;
        }

        .page-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 25px;
        }

        /* Stats bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card.total .stat-number { color: #001f54; }
        .stat-card.completed .stat-number { color: #1b5e20; }
        .stat-card.billing-pending .stat-number { color: #e65100; }
        .stat-card.billing-done .stat-number { color: #28a745; }

        /* Read-only notice */
        .readonly-notice {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 10px 20px;
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 20px;
        }

        .readonly-notice i {
            margin-right: 6px;
        }

        /* Table container */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .table-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 2px solid #e1e8ed;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 1.1rem;
            color: #001f54;
        }

        .table-header .search-box {
            position: relative;
        }

        .table-header .search-box input {
            padding: 6px 12px 6px 32px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.85rem;
            width: 250px;
        }

        .table-header .search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 0.8rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #f0f2f5;
            padding: 10px 14px;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.5px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }

        tbody td {
            padding: 12px 14px;
            font-size: 0.85rem;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .docnum-cell {
            font-family: 'Courier New', monospace;
            font-size: 0.78rem;
            color: #001f54;
            font-weight: 600;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.ready {
            background: #e3f2fd;
            color: #1565c0;
        }

        .status-badge.completed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-badge.pending {
            background: #fff3e0;
            color: #e65100;
        }

        .status-badge.not-sent {
            background: #f5f5f5;
            color: #999;
        }

        .date-cell {
            font-size: 0.78rem;
            color: #666;
        }

        .audit-cell {
            font-size: 0.78rem;
            color: #666;
        }

        .audit-cell i {
            color: #28a745;
            margin-right: 3px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .no-data i {
            font-size: 2rem;
            display: block;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="main-content">
        <h2 class="page-title">Contract & Accounting Status</h2>
        <p class="page-subtitle">Informational view only. No actions can be performed from this panel.</p>

        <div class="readonly-notice">
            <i class="fas fa-info-circle"></i>
            This panel is read-only. Contract completion is triggered from the Contract Generator.
            Accounting review is performed in the Billing / Accounting module.
        </div>

        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-card total">
                <div class="stat-number"><?= $totalContracts ?></div>
                <div class="stat-label">Total Contracts</div>
            </div>
            <div class="stat-card completed">
                <div class="stat-number"><?= $completedContracts ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card billing-pending">
                <div class="stat-number"><?= $pendingBilling ?></div>
                <div class="stat-label">Billing Pending</div>
            </div>
            <div class="stat-card billing-done">
                <div class="stat-number"><?= $completedBilling ?></div>
                <div class="stat-label">Billing Done</div>
            </div>
        </div>

        <!-- Contracts Table -->
        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-file-contract"></i> All Contracts</h3>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Search contracts..." oninput="filterTable(this.value)">
                </div>
            </div>

            <?php if (empty($contracts)): ?>
                <div class="no-data">
                    <i class="fas fa-folder-open"></i>
                    <p>No contracts found</p>
                </div>
            <?php else: ?>
                <table id="contracts-table">
                    <thead>
                        <tr>
                            <th>DOCNUM</th>
                            <th>Company</th>
                            <th>Service</th>
                            <th>Type</th>
                            <th>Contract Status</th>
                            <th>Contract Date</th>
                            <th>Billing Status</th>
                            <th>Billing Date</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contracts as $c): ?>
                        <tr class="contract-row" data-search="<?= htmlspecialchars(strtolower(($c['docnum'] ?? '') . ' ' . ($c['Company_Name'] ?? '') . ' ' . ($c['Client_Name'] ?? '') . ' ' . ($c['Requested_Service'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                            <td class="docnum-cell"><?= htmlspecialchars($c['docnum'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <strong><?= htmlspecialchars($c['Company_Name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if (!empty($c['Client_Name'])): ?>
                                    <br><small style="color:#999"><?= htmlspecialchars($c['Client_Name'], ENT_QUOTES, 'UTF-8') ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($c['Requested_Service'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($c['Request_Type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php
                                $contractStatus = $c['contract_status'] ?? 'ready';
                                $statusClass = $contractStatus === 'completed' ? 'completed' : 'ready';
                                $statusLabel = ucfirst($contractStatus);
                                $statusIcon = $contractStatus === 'completed' ? 'fa-check-circle' : 'fa-clock';
                                ?>
                                <span class="status-badge <?= $statusClass ?>">
                                    <i class="fas <?= $statusIcon ?>"></i> <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="date-cell">
                                <?= $c['contract_completed_at'] ? date('M d, Y', strtotime($c['contract_completed_at'])) : '-' ?>
                            </td>
                            <td>
                                <?php if ($c['billing_id']): ?>
                                    <?php
                                    $billingStatus = $c['billing_status'];
                                    $bClass = $billingStatus === 'completed' ? 'completed' : 'pending';
                                    $bLabel = ucfirst($billingStatus);
                                    $bIcon = $billingStatus === 'completed' ? 'fa-check-circle' : 'fa-clock';
                                    ?>
                                    <span class="status-badge <?= $bClass ?>">
                                        <i class="fas <?= $bIcon ?>"></i> <?= $bLabel ?>
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge not-sent">
                                        <i class="fas fa-minus-circle"></i> Not sent
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="date-cell">
                                <?= $c['billing_completed_at'] ? date('M d, Y', strtotime($c['billing_completed_at'])) : '-' ?>
                            </td>
                            <td class="audit-cell">
                                <?php if ($c['billing_completed_by']): ?>
                                    <i class="fas fa-user-check"></i> <?= htmlspecialchars($c['billing_completed_by'], ENT_QUOTES, 'UTF-8') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function filterTable(query) {
        const rows = document.querySelectorAll('.contract-row');
        const q = query.toLowerCase().trim();

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            row.style.display = (!q || searchData.includes(q)) ? '' : 'none';
        });
    }
    </script>
</body>
</html>
