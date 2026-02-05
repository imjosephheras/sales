<?php
/**
 * Reschedule Event Action - BASIC
 * Delegates to EventController->reschedule()
 */

require_once '../../config.php';
require_once APP_PATH . '/Controllers/EventController.php';

$controller = new EventController();
$controller->reschedule();
