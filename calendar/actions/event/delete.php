<?php
/**
 * Delete Event Action - BASIC
 * Delegates to EventController->delete()
 */

require_once '../../config.php';
require_once APP_PATH . '/Controllers/EventController.php';

$controller = new EventController();
$controller->delete();
