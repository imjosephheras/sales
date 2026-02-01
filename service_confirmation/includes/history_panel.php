<!-- History Panel -->
<div class="panel-header">
    <h2><i class="fas fa-history"></i> Service History</h2>
    <span class="badge history-count">0</span>
</div>

<div class="panel-filters">
    <select id="filter-history-status" class="filter-select">
        <option value="all">All Status</option>
        <option value="completed">Completed</option>
        <option value="not_completed">Not Completed</option>
        <option value="pending">Pending</option>
    </select>
    <input type="text" id="search-history" placeholder="Search..." class="search-input">
</div>

<!-- Statistics -->
<div class="stats-bar">
    <div class="stat-item stat-completed">
        <i class="fas fa-check-circle"></i>
        <span class="stat-value" id="stat-completed">0</span>
        <span class="stat-label">Completed</span>
    </div>
    <div class="stat-item stat-not-completed">
        <i class="fas fa-times-circle"></i>
        <span class="stat-value" id="stat-not-completed">0</span>
        <span class="stat-label">Not Done</span>
    </div>
    <div class="stat-item stat-pending">
        <i class="fas fa-clock"></i>
        <span class="stat-value" id="stat-pending">0</span>
        <span class="stat-label">Pending</span>
    </div>
    <div class="stat-item stat-invoice">
        <i class="fas fa-file-invoice-dollar"></i>
        <span class="stat-value" id="stat-invoice">0</span>
        <span class="stat-label">Ready to Invoice</span>
    </div>
</div>

<div class="panel-content" id="history-list">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i> Loading history...
    </div>
</div>

<!-- Pagination -->
<div class="pagination-controls" id="history-pagination">
    <button class="btn-page" id="prev-page" disabled>
        <i class="fas fa-chevron-left"></i>
    </button>
    <span class="page-info">Page <span id="current-page">1</span> of <span id="total-pages">1</span></span>
    <button class="btn-page" id="next-page" disabled>
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

<template id="history-item-template">
    <div class="history-item" data-id="">
        <div class="history-status">
            <i class="status-icon"></i>
        </div>
        <div class="history-info">
            <div class="history-header">
                <span class="nomenclature"></span>
                <span class="status-badge"></span>
            </div>
            <div class="history-company"></div>
            <div class="history-meta">
                <span class="date"><i class="fas fa-calendar"></i> <span class="date-value"></span></span>
                <span class="seller"><i class="fas fa-user"></i> <span class="seller-value"></span></span>
            </div>
        </div>
        <div class="history-actions">
            <button class="btn-icon btn-view" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <a class="btn-icon btn-pdf" title="Download PDF" target="_blank">
                <i class="fas fa-file-pdf"></i>
            </a>
        </div>
    </div>
</template>
