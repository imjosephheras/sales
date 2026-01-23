<?php
/**
 * MARK READY CONTROLLER
 * Marca la solicitud como lista y genera el DOCNUM
 * Formato: <DOCNUM><FECHA>-<BIZ>-<SERV>-<FREC>-<ANOS>
 * Ejemplo: 100001152026-ABC-HV-01-03
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_config.php';

try {
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['request_id'] ?? null;

    if (!$id) {
        throw new Exception('Request ID is required');
    }

    // Obtener datos de la solicitud
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$request) {
        throw new Exception('Request not found');
    }

    // Verificar que tenga los datos mínimos
    if (empty($request['Business_Name'])) {
        throw new Exception('Business Name is required to mark as ready');
    }

    // ========================================
    // GENERAR DOCNUM
    // ========================================

    // 1. Obtener número consecutivo
    $pdo->beginTransaction();
    
    $stmt = $pdo->query("SELECT last_number FROM docnum_counter WHERE id = 1 FOR UPDATE");
    $counter = $stmt->fetch(PDO::FETCH_ASSOC);
    $next_number = $counter['last_number'] + 1;
    
    // Actualizar contador
    $pdo->exec("UPDATE docnum_counter SET last_number = $next_number WHERE id = 1");

    // 2. Generar componentes del DOCNUM
    
    // DOCNUM: número consecutivo (6 dígitos)
    $docnum_part = str_pad($next_number, 6, '0', STR_PAD_LEFT);
    
    // FECHA: MMDDYYYY
    $date_part = date('mdY');
    
    // BIZ: Iniciales del negocio (primeras 3 letras, mayúsculas)
    $business_name = strtoupper($request['Business_Name']);
    $biz_part = substr(preg_replace('/[^A-Z]/', '', $business_name), 0, 3);
    if (strlen($biz_part) < 3) {
        $biz_part = str_pad($biz_part, 3, 'X', STR_PAD_RIGHT);
    }
    
    // SERV: Tipo de servicio (mapeo)
    $service_map = [
        'Janitorial' => 'JAN',
        'Hospitality' => 'HOS',
        'Kitchen Cleaning' => 'KIT',
        'Hood Vent' => 'HV',
        'Kitchen Cleaning & Hood Vent' => 'KHV'
    ];
    $serv_part = $service_map[$request['Requested_Service']] ?? 'OTH';
    
    // FREC: Frecuencia de facturación (2 dígitos)
    $freq_map = [
        '15' => '15',
        '30' => '30',
        '50_deposit' => '50',
        'completion' => '99'
    ];
    $frec_part = $freq_map[$request['Invoice_Frequency']] ?? '00';
    
    // ANOS: Duración del contrato (2 dígitos)
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
    $anos_part = $duration_map[$request['Contract_Duration']] ?? '00';
    
    // DOCNUM completo
    $docnum = "{$docnum_part}{$date_part}-{$biz_part}-{$serv_part}-{$frec_part}-{$anos_part}";

    // ========================================
    // ACTUALIZAR REQUEST
    // ========================================

    $stmt = $pdo->prepare("
        UPDATE requests 
        SET 
            docnum = :docnum,
            status = 'ready',
            completed_at = NOW(),
            updated_at = NOW()
        WHERE id = :id
    ");
    
    $stmt->execute([
        ':docnum' => $docnum,
        ':id' => $id
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Request marked as ready',
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