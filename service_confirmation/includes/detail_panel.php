<!-- Service Detail Panel -->
<div class="panel-header">
    <h2><i class="fas fa-file-alt"></i> Service Details</h2>
</div>

<div class="panel-content" id="detail-content">
    <div class="empty-state">
        <i class="fas fa-hand-pointer"></i>
        <p>Select a service from the list to view details</p>
    </div>
</div>

<template id="detail-template">
    <div class="detail-view">
        <!-- Status Header -->
        <div class="status-header">
            <span class="current-status"></span>
            <span class="nomenclature-badge"></span>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="btn btn-success btn-mark-complete" data-id="">
                <i class="fas fa-check-circle"></i> Mark as Completed
            </button>
            <button class="btn btn-danger btn-mark-not-complete" data-id="">
                <i class="fas fa-times-circle"></i> Mark as Not Done
            </button>
            <button class="btn btn-secondary btn-reset-pending" data-id="">
                <i class="fas fa-undo"></i> Reset to Pending
            </button>
        </div>

        <!-- Service Info -->
        <div class="detail-section">
            <h3>Service Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Service Type</label>
                    <span class="service-type"></span>
                </div>
                <div class="detail-item">
                    <label>Request Type</label>
                    <span class="request-type"></span>
                </div>
                <div class="detail-item">
                    <label>Seller</label>
                    <span class="seller"></span>
                </div>
                <div class="detail-item">
                    <label>Price</label>
                    <span class="price"></span>
                </div>
            </div>
        </div>

        <!-- Client Info -->
        <div class="detail-section">
            <h3>Client Information</h3>
            <div class="detail-grid">
                <div class="detail-item full-width">
                    <label>Company</label>
                    <span class="company-name"></span>
                </div>
                <div class="detail-item">
                    <label>Client Name</label>
                    <span class="client-name"></span>
                </div>
                <div class="detail-item">
                    <label>Email</label>
                    <span class="email"></span>
                </div>
                <div class="detail-item">
                    <label>Phone</label>
                    <span class="phone"></span>
                </div>
                <div class="detail-item full-width">
                    <label>Address</label>
                    <span class="address"></span>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="detail-section">
            <h3>Dates</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Document Date</label>
                    <span class="document-date"></span>
                </div>
                <div class="detail-item">
                    <label>Work Date</label>
                    <span class="work-date"></span>
                </div>
                <div class="detail-item">
                    <label>Created</label>
                    <span class="created-at"></span>
                </div>
                <div class="detail-item">
                    <label>Completed</label>
                    <span class="completed-at"></span>
                </div>
            </div>
        </div>

        <!-- Observations -->
        <div class="detail-section">
            <h3>Observations</h3>
            <div class="observations-content"></div>
        </div>
    </div>
</template>
