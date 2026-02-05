<?php
/**
 * Event Dot Component - BASIC
 * Shows: title, client, category color.
 * No code extraction, no series badges, no date inference.
 */

$eventId = $event['event_id'];
$title   = e($event['title'] ?? '');
$client  = e($event['client'] ?? '');
$color   = e($event['color_hex'] ?? '#2563eb');
$status  = $event['status'] ?? '';
?>
<div class="event-dot <?= $status === 'completed' ? 'event-completed' : '' ?>"
     data-event-id="<?= $eventId ?>"
     data-client="<?= $client ?>"
     style="--event-color: <?= $color ?>"
     draggable="true"
     ondragstart="handleDragStart(event, <?= $eventId ?>)"
     onclick="event.stopPropagation(); openEventModal(<?= $eventId ?>)">
    <span class="event-label"><?= $title ?></span>
    <?php if ($client): ?>
        <span class="event-client-name"><?= mb_strlen($client) > 20 ? mb_substr($client, 0, 20) . '...' : $client ?></span>
    <?php endif; ?>
</div>
