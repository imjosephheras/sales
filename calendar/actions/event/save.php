<?php
/**
 * ============================================================
 * SAVE EVENT ACTION
 * Create or update event
 * ============================================================
 */

require_once '../../config.php';
require_once '../../app/Controllers/EventController.php';

// Require authentication
requireAuth();

// Initialize controller and save
$controller = new EventController();
$controller->save();