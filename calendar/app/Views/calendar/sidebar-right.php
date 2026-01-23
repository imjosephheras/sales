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

    <!-- Work/Jobs Section -->
    <div class="work-section">
        <div class="section-header">
            <h2>📋 Work / Jobs</h2>
            <button class="btn-add" onclick="openWorkModal()">+</button>
        </div>
        
        <!-- JWO Section -->
        <div class="work-category">
            <div class="work-category-header">
                <span class="work-badge jwo">JWO</span>
                <span class="work-count"><?= count($jwos) ?></span>
            </div>
            <div class="work-items">
                <?php if (empty($jwos)): ?>
                    <div class="empty-state-micro">No JWOs</div>
                <?php else: ?>
                    <?php foreach (array_slice($jwos, 0, 3) as $jwo): ?>
                        <?php component('work-item', ['item' => $jwo]); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contracts Section -->
        <div class="work-category">
            <div class="work-category-header">
                <span class="work-badge contract">Contracts</span>
                <span class="work-count"><?= count($contracts) ?></span>
            </div>
            <div class="work-items">
                <?php if (empty($contracts)): ?>
                    <div class="empty-state-micro">No contracts</div>
                <?php else: ?>
                    <?php foreach (array_slice($contracts, 0, 3) as $contract): ?>
                        <?php component('work-item', ['item' => $contract]); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Proposals Section -->
        <div class="work-category">
            <div class="work-category-header">
                <span class="work-badge proposal">Proposals</span>
                <span class="work-count"><?= count($proposals) ?></span>
            </div>
            <div class="work-items">
                <?php if (empty($proposals)): ?>
                    <div class="empty-state-micro">No proposals</div>
                <?php else: ?>
                    <?php foreach (array_slice($proposals, 0, 3) as $proposal): ?>
                        <?php component('work-item', ['item' => $proposal]); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</aside>