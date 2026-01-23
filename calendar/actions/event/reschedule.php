<?php
/**
 * ============================================================
 * RESCHEDULE EVENT ACTION
 * Reschedule event via drag & drop
 * ============================================================
 */

require_once '../../config.php';
require_once '../../app/Controllers/EventController.php';

// Require authentication
requireAuth();

// Initialize controller and reschedule
$controller = new EventController();
$controller->reschedule();