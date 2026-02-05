<?php
/**
 * Save Event Action - BASIC
 * Delegates to EventController->save()
 */

require_once '../../config.php';
require_once APP_PATH . '/Controllers/EventController.php';

$controller = new EventController();
$controller->save();
