<!-- Pending Services Panel -->
<div class="panel-header">
    <h2><i class="fas fa-clock"></i> Pending Services</h2>
    <span class="badge pending-count">0</span>
</div>

<div class="panel-filters">
    <input type="text" id="search-pending" placeholder="Search..." class="search-input">
    <select id="filter-seller" class="filter-select">
        <option value="">All Sellers</option>
    </select>
</div>

<div class="panel-content" id="pending-list">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i> Loading...
    </div>
</div>

<template id="service-card-template">
    <div class="service-card" data-id="">
        <div class="card-header">
            <span class="nomenclature"></span>
            <span class="work-date"></span>
        </div>
        <div class="card-body">
            <div class="company-name"></div>
            <div class="client-info">
                <span class="client-name"></span>
            </div>
            <div class="service-info">
                <span class="service-type badge-info"></span>
                <span class="seller"></span>
            </div>
            <div class="price"></div>
        </div>
        <div class="card-actions">
            <button class="btn-action btn-complete" title="Mark as Completed">
                <i class="fas fa-check"></i> Completed
            </button>
            <button class="btn-action btn-not-complete" title="Mark as Not Completed">
                <i class="fas fa-times"></i> Not Done
            </button>
        </div>
    </div>
</template>
