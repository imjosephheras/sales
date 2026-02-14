<!-- Services Panel - Task Tracking View -->
<div class="panel-header">
    <h2><i class="fas fa-clipboard-list"></i> Services</h2>
    <span class="badge pending-count">0</span>
</div>

<div class="panel-filters">
    <input type="text" id="search-pending" placeholder="Search..." class="search-input">
    <select id="filter-seller" class="filter-select">
        <option value="">All Sellers</option>
    </select>
    <select id="filter-progress" class="filter-select">
        <option value="">All Progress</option>
        <option value="not_started">Not Started</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
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
        <!-- Progress indicator -->
        <div class="card-progress">
            <div class="progress-mini">
                <div class="progress-bar-mini">
                    <div class="progress-fill-mini" style="width: 0%"></div>
                </div>
                <span class="progress-label">0/9</span>
            </div>
        </div>
    </div>
</template>
