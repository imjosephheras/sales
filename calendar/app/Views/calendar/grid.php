<!-- Calendar Grid - BASIC -->
<!-- Events are placed by start_date. Nothing else. -->
<div class="calendar-grid">

    <!-- Day Headers -->
    <?php
    $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    foreach ($dayNames as $dn):
    ?>
        <div class="grid-day-header"><?= $dn ?></div>
    <?php endforeach; ?>

    <!-- Empty cells before first day -->
    <?php for ($i = 0; $i < $firstDay; $i++): ?>
        <div class="calendar-day empty"></div>
    <?php endfor; ?>

    <!-- Days of the month -->
    <?php
    $today = date('Y-m-d');
    for ($day = 1; $day <= $daysInMonth; $day++):
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $isToday = ($date === $today);

        // Filter events for this day using start_date only
        $dayEvents = array_filter($events, function($e) use ($date) {
            return $e['start_date'] === $date;
        });
    ?>
        <div class="calendar-day <?= $isToday ? 'today' : '' ?>"
             data-date="<?= $date ?>"
             ondragover="event.preventDefault(); this.classList.add('drag-over')"
             ondragleave="this.classList.remove('drag-over')"
             ondrop="handleDrop(event, this)">

            <div class="day-number"><?= $day ?></div>

            <div class="day-events">
                <?php foreach ($dayEvents as $evt): ?>
                    <?php component('event-dot', ['event' => $evt]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endfor; ?>
</div>
