<?php
/**
 * delete_photo.php - Delete a form photo from server storage + database
 *
 * Expects JSON POST: { "photo_id": int, "form_id": int }
 * Deletes the file via FileStorageService and removes the DB record.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../app/bootstrap.php';
Middleware::auth();

require_once 'db_config.php';
require_once 'order_access.php';

try {
    $currentUser = requireOrderAccess();

    $input = json_decode(file_get_contents('php://input'), true);
    $photoId = isset($input['photo_id']) ? (int)$input['photo_id'] : 0;
    $formId  = isset($input['form_id'])  ? (int)$input['form_id']  : 0;

    if (!$photoId || !$formId) {
        echo json_encode(['success' => false, 'message' => 'photo_id and form_id are required']);
        exit;
    }

    $pdo = getDBConnection();

    // RBAC: Verify access to this form
    if (!canAccessOrder($pdo, $formId, $currentUser)) {
        denyOrderAccess();
    }

    // Get photo record
    $stmt = $pdo->prepare("SELECT * FROM form_photos WHERE id = ? AND form_id = ?");
    $stmt->execute([$photoId, $formId]);
    $photo = $stmt->fetch();

    if (!$photo) {
        echo json_encode(['success' => false, 'message' => 'Photo not found']);
        exit;
    }

    // Delete file from storage (local or FTP)
    if (!empty($photo['photo_path'])) {
        $storage = new FileStorageService();
        $storage->deleteFile($photo['photo_path']);
    }

    // Delete DB record
    $stmt = $pdo->prepare("DELETE FROM form_photos WHERE id = ? AND form_id = ?");
    $stmt->execute([$photoId, $formId]);

    echo json_encode(['success' => true, 'message' => 'Photo deleted successfully']);

} catch (Exception $e) {
    error_log("delete_photo error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
