<?php
/**
 * MARK READY CONTROLLER
 * Marks the form as ready and generates the DOCNUM.
 * Reads/writes from forms table (single source of truth).
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/init.php';
$pdo = Database::getConnection();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['request_id'] ?? null;

    if (!$id) {
        throw new Exception('Form ID is required');
    }

    // Get form data
    $stmt = $pdo->prepare("SELECT * FROM forms WHERE form_id = :id");
    $stmt->execute([':id' => $id]);
    $form = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$form) {
        throw new Exception('Form not found');
    }

    // Verify minimum data
    if (empty($form['company_name'])) {
        throw new Exception('Company Name is required to mark as ready');
    }

    // ========================================
    // GENERATE DOCNUM
    // ========================================

    $pdo->beginTransaction();

    $stmt = $pdo->query("SELECT last_number FROM docnum_counter WHERE id = 1 FOR UPDATE");
    $counter = $stmt->fetch(PDO::FETCH_ASSOC);
    $next_number = $counter['last_number'] + 1;

    $pdo->exec("UPDATE docnum_counter SET last_number = $next_number WHERE id = 1");

    // DOCNUM parts
    $docnum_part = str_pad($next_number, 6, '0', STR_PAD_LEFT);
    $date_part = date('mdY');

    // BIZ: Company initials
    $company_name = strtoupper($form['company_name']);
    $biz_part = substr(preg_replace('/[^A-Z]/', '', $company_name), 0, 3);
    if (strlen($biz_part) < 3) {
        $biz_part = str_pad($biz_part, 3, 'X', STR_PAD_RIGHT);
    }

    // SERV: Service type mapping
    $service_map = [
        'Janitorial' => 'JAN',
        'Hospitality' => 'HOS',
        'Kitchen Cleaning' => 'KIT',
        'Hood Vent' => 'HV',
        'Kitchen Cleaning & Hood Vent' => 'KHV'
    ];
    $serv_part = $service_map[$form['requested_service']] ?? 'OTH';

    // FREC: Invoice frequency
    $freq_map = [
        '15' => '15',
        '30' => '30',
        '50_deposit' => '50',
        'completion' => '99'
    ];
    $frec_part = $freq_map[$form['invoice_frequency']] ?? '00';

    // ANOS: Contract duration
    $duration_map = [
        '6_months' => '06',
        '1_year' => '12',
        '1_5_years' => '18',
        '2_years' => '24',
        '3_years' => '36',
        '4_years' => '48',
        '5_years' => '60',
        'not_applicable' => '00'
    ];
    $anos_part = $duration_map[$form['contract_duration']] ?? '00';

    // Full DOCNUM
    $docnum = "{$docnum_part}{$date_part}-{$biz_part}-{$serv_part}-{$frec_part}-{$anos_part}";

    // ========================================
    // UPDATE FORM
    // ========================================

    $stmt = $pdo->prepare("
        UPDATE forms
        SET
            docnum = :docnum,
            status = 'ready',
            completed_at = NOW(),
            updated_at = NOW()
        WHERE form_id = :id
    ");

    $stmt->execute([
        ':docnum' => $docnum,
        ':id' => $id
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Form marked as ready',
        'docnum' => $docnum,
        'request_id' => $id
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
