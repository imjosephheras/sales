<?php
/**
 * ============================================================
 * TOGGLE EVENT STATUS ACTION
 * Toggle event completion status
 * ============================================================
 */

require_once '../../config.php';
require_once '../../app/Controllers/EventController.php';

// Require authentication
requireAuth();

// Initialize controller and toggle status
$controller = new EventController();
$controller->toggleStatus();