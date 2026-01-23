<!-- Calendar Grid -->
<div class="calendar-grid">
    
    <!-- Day Headers -->
    <?php
    $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    foreach ($dayNames as $dayName):
    ?>
        <div class="day-header"><?= $dayName ?></div>
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
        $dayEvents = array_filter($events, fn($e) => $e['start_date'] === $date);
    ?>
        <div class="calendar-day <?= $isToday ? 'today' : '' ?>" 
             data-date="<?= $date ?>"
             onclick="console.log('Day clicked: <?= $date ?>')">
            
            <div class="day-number"><?= $day ?></div>
            
            <!-- Screen display events -->
            <div class="day-events">
                <?php foreach ($dayEvents as $evt): ?>
                    <?php 
                    component('event-dot', [
                        'event' => $evt,
                        'date' => $date
                    ]); 
                    ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Print-only events (populated by JavaScript) -->
            <div class="print-events"></div>
        </div>
    <?php endfor; ?>
</div>