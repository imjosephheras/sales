<!-- Right Sidebar: Clients Filter + Today + Work/Jobs -->
<aside class="sidebar-right">
    
    <!-- Clients Filter Section -->
    <div class="clients-section work-section">
        <div class="section-header">
            <h2>🏢 Clients</h2>
            <button class="btn-icon" onclick="clearClientFilter()" title="Show all">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="clients-filter">
            <input type="text" 
                   id="clientSearchInput" 
                   class="client-search" 
                   placeholder="Search client..."
                   onkeyup="filterClients(this.value)">
            
            <div class="clients-list" id="clientsList">
                <?php
                // Get unique clients from events
                $clients = [];
                foreach ($events as $evt) {
                    if (!empty($evt['client'])) {
                        $client = trim($evt['client']);
                        if (!isset($clients[$client])) {
                            $clients[$client] = 0;
                        }
                        $clients[$client]++;
                    }
                }
                ksort($clients);
                ?>
                
                <?php if (empty($clients)): ?>
                    <div class="empty-state-small">No clients yet</div>
                <?php else: ?>
                    <div class="client-item all-clients active" onclick="filterByClient('')">
                        <span class="client-name">All Clients</span>
                        <span class="client-count"><?= count($events) ?></span>
                    </div>
                    <?php foreach ($clients as $client => $count): ?>
                        <div class="client-item" onclick="filterByClient('<?= e($client) ?>')">
                            <span class="client-name"><?= e($client) ?></span>
                            <span class="client-count"><?= $count ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Today Section -->
    <div class="today-section work-section">
        <div class="section-header">
            <h2>Today</h2>
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
            <h2>Work / Jobs</h2>
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