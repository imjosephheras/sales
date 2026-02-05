<?php
/**
 * Event Card Component
 * Displays an event as a card on the calendar with code and location
 * 
 * Required: $event (array with event data)
 * Optional: $date (for data attribute)
 */

$eventId = $event['event_id'];
$title = e($event['title']);
$color = e($event['color_hex'] ?? '#2563eb');
$isReschedulable = $event['is_reschedulable'] ?? 1;
$eventDate = $date ?? $event['effective_date'] ?? $event['start_date'] ?? $event['execution_date'] ?? $event['document_date'];
$location = e($event['location'] ?? '');
$client = e($event['client'] ?? '');

// Extract CODE from title (format: PREFIX-CODE(MMDDYYYY)-SUFFIX)
preg_match('/([A-Z]+-)?([A-Z0-9]+)\((\d{8})\)/', $title, $matches);
$code = $matches[2] ?? '';
$dateCode = $matches[3] ?? '';

// Format date code as MM/DD/YYYY
if ($dateCode && strlen($dateCode) === 8) {
    $formattedDate = substr($dateCode, 0, 2) . '/' . substr($dateCode, 2, 2) . '/' . substr($dateCode, 4, 4);
} else {
    $formattedDate = '';
}
?>

<div class="event-card" 
     data-event-id="<?= $eventId ?>"
     data-event-date="<?= $eventDate ?>"
     data-reschedulable="<?= $isReschedulable ?>"
     data-client="<?= e($event['client'] ?? '') ?>"
     style="--event-color: <?= $color ?>"
     draggable="true"
     onclick="event.stopPropagation(); openEventDetail(<?= $eventId ?>)">
    
    <div class="event-card-header" style="background-color: <?= $color ?>">
        <span class="event-code"><?= $code ?></span>
    </div>
    
    <div class="event-card-body">
        <?php if ($formattedDate): ?>
            <div class="event-date-code"><?= $formattedDate ?></div>
        <?php endif; ?>
        
        <?php if ($client): ?>
            <div class="event-client" title="<?= $client ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <?= strlen($client) > 18 ? substr($client, 0, 18) . '...' : $client ?>
            </div>
        <?php endif; ?>
        
        <?php if ($location): ?>
            <div class="event-location" title="<?= $location ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <?= strlen($location) > 20 ? substr($location, 0, 20) . '...' : $location ?>
            </div>
        <?php endif; ?>
    </div>
</div>