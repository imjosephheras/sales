<?php
/**
 * ============================================================
 * DELETE EVENT ACTION
 * Delete single event or entire series
 * ============================================================
 */

require_once '../../config.php';
require_once '../../app/Controllers/EventController.php';

// Require authentication
requireAuth();

// Initialize controller and delete
$controller = new EventController();
$controller->delete();