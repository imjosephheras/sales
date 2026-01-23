<!-- Left Sidebar: Clients Filter (Excel-style) -->
<aside class="sidebar-left">
    
    <!-- Clients Filter Section (Excel-style with checkboxes) -->
    <div class="clients-section work-section">
        <div class="section-header">
            <h2>🏢 Clients</h2>
            <button class="btn-icon" onclick="clearClientFilter()" title="Clear filters">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 3L2 3 10 12.46 10 19 14 21 14 12.46 22 3z"></path>
                </svg>
            </button>
        </div>
        
        <div class="clients-filter-excel">
            <!-- Search box -->
            <input type="text" 
                   id="clientSearchInput" 
                   class="client-search" 
                   placeholder="🔍 Search client..."
                   onkeyup="filterClientsList(this.value)">
            
            <!-- Select/Deselect All -->
            <div class="filter-controls">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           id="selectAllClients" 
                           checked 
                           onchange="toggleAllClients(this.checked)">
                    <span class="checkbox-text">Select All</span>
                </label>
                <button class="btn-text-small" onclick="applyClientFilter()">Apply</button>
            </div>
            
            <!-- Clients list with checkboxes -->
            <div class="clients-list-excel" id="clientsListExcel">
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
                    <?php foreach ($clients as $client => $count): ?>
                        <label class="client-checkbox-item" data-client="<?= e($client) ?>">
                            <input type="checkbox" 
                                   class="client-checkbox" 
                                   value="<?= e($client) ?>" 
                                   checked
                                   onchange="updateClientFilter()">
                            <span class="client-checkbox-label">
                                <span class="client-name"><?= e($client) ?></span>
                                <span class="client-count">(<?= $count ?>)</span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Active filters display -->
            <div class="active-filters" id="activeFilters" style="display: none;">
                <div class="active-filters-header">
                    <span class="active-filters-label">🏷️ Active:</span>
                    <button class="btn-text-tiny" onclick="clearClientFilter()">Clear all</button>
                </div>
                <div class="active-filters-tags" id="activeFiltersTags"></div>
            </div>
        </div>
    </div>

</aside>