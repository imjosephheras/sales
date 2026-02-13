<div class="viewer-container">
    <!-- Empty State -->
    <div id="viewer-empty-state" class="viewer-empty-state">
        <i class="fas fa-file-invoice-dollar"></i>
        <h2>No Contract Selected</h2>
        <p>Select a contract from the pending list to review the final PDF</p>
    </div>

    <!-- Active Document State -->
    <div id="viewer-active-state" style="display: none;">
        <!-- Top Row: Contract Info Card + Action Card -->
        <div class="viewer-top-row">
            <!-- Card 1: Contract Information -->
            <div class="viewer-info-card">
                <div class="info-card-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="info-card-details">
                    <h3 id="viewer-doc-title">Contract</h3>
                    <span class="doc-number-badge" id="viewer-order-number"></span>
                    <p class="info-card-client" id="viewer-client-name"></p>
                    <p class="info-card-service" id="viewer-service-name"></p>
                </div>
            </div>

            <!-- Card 2: Action Button -->
            <div class="viewer-action-card" id="viewer-actions">
                <button class="btn btn-action-completed" id="btn-mark-completed" title="Mark as Completed">
                    <i class="fas fa-check-circle"></i> Mark as Completed
                </button>
                <button class="btn btn-action-pending" id="btn-mark-pending" title="Mark as Pending" style="display: none;">
                    <i class="fas fa-clock"></i> Mark as Pending
                </button>
            </div>
        </div>

        <!-- PDF Viewer: Full width below cards -->
        <div class="pdf-viewer-container">
            <iframe id="pdf-viewer" src="" frameborder="0"></iframe>
        </div>
    </div>
</div>
