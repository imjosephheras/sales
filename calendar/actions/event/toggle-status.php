<?php
/**
 * Toggle Status Action - BASIC
 * Delegates to EventController->toggleStatus()
 */

require_once '../../config.php';
require_once APP_PATH . '/Controllers/EventController.php';

$controller = new EventController();
$controller->toggleStatus();
