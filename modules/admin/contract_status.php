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
$page_slug  = 'admin_panel';

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
        f.total_cost AS PriceInput,
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

$page_head = '
<style>
    .cs-stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }
    .cs-stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        border: 1px solid #e5e7eb;
    }
    .cs-stat-card .stat-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 5px;
    }
    .cs-stat-card .stat-label {
        font-size: 0.8rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .cs-stat-card.total .stat-number { color: #001f54; }
    .cs-stat-card.completed .stat-number { color: #1b5e20; }
    .cs-stat-card.billing-pending .stat-number { color: #e65100; }
    .cs-stat-card.billing-done .stat-number { color: #059669; }

    .cs-readonly-notice {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 10px 20px;
        color: #1e40af;
        font-size: 0.85rem;
        text-align: center;
        margin-bottom: 20px;
    }
    .cs-readonly-notice i { margin-right: 6px; }

    .cs-table-header {
        padding: 15px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cs-table-header h3 {
        font-size: 1.05rem;
        color: #1f2937;
    }
    .cs-search-box { position: relative; }
    .cs-search-box input {
        padding: 6px 12px 6px 32px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.85rem;
        width: 250px;
    }
    .cs-search-box input:focus {
        outline: none;
        border-color: #4a9eff;
        box-shadow: 0 0 0 2px rgba(74,158,255,0.12);
    }
    .cs-search-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.8rem;
    }

    .cs-docnum-cell {
        font-family: monospace;
        font-size: 0.78rem;
        color: #001f54;
        font-weight: 600;
    }
    .cs-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .cs-status-badge.ready { background: #dbeafe; color: #1d4ed8; }
    .cs-status-badge.completed { background: #d1fae5; color: #065f46; }
    .cs-status-badge.pending { background: #ffedd5; color: #c2410c; }
    .cs-status-badge.not-sent { background: #f3f4f6; color: #9ca3af; }

    .cs-date-cell { font-size: 0.78rem; color: #6b7280; }
    .cs-audit-cell { font-size: 0.78rem; color: #6b7280; }
    .cs-audit-cell i { color: #059669; margin-right: 3px; }
    .cs-no-data { text-align: center; padding: 40px; color: #9ca3af; }
    .cs-no-data i { font-size: 2rem; display: block; margin-bottom: 10px; }

    @media (max-width: 768px) {
        .cs-stats-bar { grid-template-columns: repeat(2, 1fr); }
    }
</style>';

ob_start();
?>

<p style="color:#6b7280;font-size:0.9rem;margin-bottom:20px;">Informational view only. No actions can be performed from this panel.</p>

<div class="cs-readonly-notice">
    <i class="fas fa-info-circle"></i>
    This panel is read-only. Contract completion is triggered from the Contract Generator.
    Accounting review is performed in the Billing / Accounting module.
</div>

<!-- Stats -->
<div class="cs-stats-bar">
    <div class="cs-stat-card total">
        <div class="stat-number"><?= $totalContracts ?></div>
        <div class="stat-label">Total Contracts</div>
    </div>
    <div class="cs-stat-card completed">
        <div class="stat-number"><?= $completedContracts ?></div>
        <div class="stat-label">Completed</div>
    </div>
    <div class="cs-stat-card billing-pending">
        <div class="stat-number"><?= $pendingBilling ?></div>
        <div class="stat-label">Billing Pending</div>
    </div>
    <div class="cs-stat-card billing-done">
        <div class="stat-number"><?= $completedBilling ?></div>
        <div class="stat-label">Billing Done</div>
    </div>
</div>

<!-- Contracts Table -->
<div class="db-table-wrapper">
    <div class="cs-table-header">
        <h3><i class="fas fa-file-contract"></i> All Contracts</h3>
        <div class="cs-search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="search-input" placeholder="Search contracts..." oninput="filterTable(this.value)">
        </div>
    </div>

    <?php if (empty($contracts)): ?>
        <div class="cs-no-data">
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
                    <td class="cs-docnum-cell"><?= htmlspecialchars($c['docnum'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <strong><?= htmlspecialchars($c['Company_Name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if (!empty($c['Client_Name'])): ?>
                            <br><small style="color:#9ca3af"><?= htmlspecialchars($c['Client_Name'], ENT_QUOTES, 'UTF-8') ?></small>
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
                        <span class="cs-status-badge <?= $statusClass ?>">
                            <i class="fas <?= $statusIcon ?>"></i> <?= $statusLabel ?>
                        </span>
                    </td>
                    <td class="cs-date-cell">
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
                            <span class="cs-status-badge <?= $bClass ?>">
                                <i class="fas <?= $bIcon ?>"></i> <?= $bLabel ?>
                            </span>
                        <?php else: ?>
                            <span class="cs-status-badge not-sent">
                                <i class="fas fa-minus-circle"></i> Not sent
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="cs-date-cell">
                        <?= $c['billing_completed_at'] ? date('M d, Y', strtotime($c['billing_completed_at'])) : '-' ?>
                    </td>
                    <td class="cs-audit-cell">
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

<script>
function filterTable(query) {
    var rows = document.querySelectorAll('.contract-row');
    var q = query.toLowerCase().trim();
    rows.forEach(function(row) {
        var searchData = row.getAttribute('data-search') || '';
        row.style.display = (!q || searchData.includes(q)) ? '' : 'none';
    });
}
</script>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../../app/Views/layouts/dashboard.php';
?>
