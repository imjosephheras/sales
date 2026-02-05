<!-- Right Sidebar: Today + Work/Jobs -->
<aside class="sidebar-right">
    
    <!-- Today Section -->
    <div class="today-section work-section">
        <div class="section-header">
            <h2>📅 Today</h2>
            <span class="date-badge"><?= date('d') ?></span>
        </div>
        
        <?php if (empty($todayEvents)): ?>
            <div class="empty-state-small">
                No events today
            </div>
        <?php else: ?>
            <div class="today-events">
                <?php foreach ($todayEvents as $evt): ?>
                    <div class="today-event work-item" style="--event-color: <?= e($evt['color_hex'] ?? '#2563eb') ?>">
                        <div class="event-details">
                            <div class="event-title"><?= e($evt['title']) ?></div>
                            <div class="event-meta">
                                <span class="event-time">
                                    <?= $evt['start_time'] ? formatTime12h($evt['start_time']) : 'All day' ?>
                                </span>
                                <?php if ($evt['category_name']): ?>
                                    <span class="event-category"><?= e($evt['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($evt['client'])): ?>
                                <div class="event-client-badge">
                                    👤 <?= e($evt['client']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Status Switch -->
                        <div class="work-status">
                            <label class="switch-label" title="Mark as completed">
                                <div class="switch <?= $evt['status'] === 'completed' ? 'on' : '' ?>" 
                                     onclick="toggleWorkStatus(<?= $evt['event_id'] ?>, this)">
                                    <div class="switch-track"></div>
                                    <div class="switch-thumb"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Next 7 Days Section -->
    <div class="work-section">
        <div class="section-header">
            <h2>📆 Next 7 Days</h2>
            <span class="work-count"><?= count($next7DaysEvents ?? []) ?></span>
        </div>

        <div class="next-7-days-list">
            <?php if (empty($next7DaysEvents)): ?>
                <div class="empty-state-small">
                    No events scheduled for the next 7 days
                </div>
            <?php else: ?>
                <?php
                // Group events by date
                $eventsByDate = [];
                foreach ($next7DaysEvents as $evt) {
                    $date = $evt['effective_date'] ?? $evt['start_date'] ?? $evt['execution_date'] ?? $evt['document_date'];
                    if (!$date) continue;
                    if (!isset($eventsByDate[$date])) {
                        $eventsByDate[$date] = [];
                    }
                    $eventsByDate[$date][] = $evt;
                }
                ?>
                <?php foreach ($eventsByDate as $date => $dayEvents): ?>
                    <div class="day-group">
                        <div class="day-header">
                            <span class="day-name"><?= date('l', strtotime($date)) ?></span>
                            <span class="day-date"><?= date('M d', strtotime($date)) ?></span>
                        </div>
                        <div class="day-events">
                            <?php foreach ($dayEvents as $evt): ?>
                                <div class="upcoming-event" onclick="openEventDetail(<?= $evt['event_id'] ?>)" style="--event-color: <?= e($evt['color_hex'] ?? '#3b82f6') ?>">
                                    <div class="upcoming-event-info">
                                        <?php if (!empty($evt['client'])): ?>
                                            <div class="upcoming-client">👤 <?= e($evt['client']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($evt['company'])): ?>
                                            <div class="upcoming-company">🏢 <?= e($evt['company']) ?></div>
                                        <?php elseif (!empty($evt['location'])): ?>
                                            <div class="upcoming-company">📍 <?= e(substr($evt['location'], 0, 30)) ?><?= strlen($evt['location']) > 30 ? '...' : '' ?></div>
                                        <?php endif; ?>
                                        <div class="upcoming-title"><?= e($evt['title']) ?></div>
                                    </div>
                                    <div class="upcoming-event-status">
                                        <div class="work-switch switch <?= ($evt['status'] ?? '') === 'completed' ? 'on' : '' ?>"
                                             onclick="event.stopPropagation(); toggleWorkStatus(<?= $evt['event_id'] ?>, this)">
                                            <div class="knob"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</aside>