<?php
/**
 * Work Item Component
 * Displays a work item in the sidebar
 * 
 * Required: $item (array with work item data)
 */

$eventId = $item['event_id'];
$title = e($item['title']);
$startDate = $item['start_date'];
$status = $item['status'] ?? 'pending';
$isCompleted = $status === 'completed';
?>

<div class="work-item" onclick="openEventDetail(<?= $eventId ?>)">
    <div class="work-code"><?= $title ?></div>
    <div class="work-meta">
        <span class="work-date"><?= date('M d', strtotime($startDate)) ?></span>
        
        <!-- Status Switch -->
        <div class="work-switch switch <?= $isCompleted ? 'on' : '' ?>" 
             onclick="event.stopPropagation(); toggleWorkStatus(<?= $eventId ?>, this)">
            <div class="knob"></div>
        </div>
    </div>
</div>