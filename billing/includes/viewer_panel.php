<div class="viewer-container">
    <!-- Empty State -->
    <div id="viewer-empty-state" class="viewer-empty-state">
        <i class="fas fa-file-invoice-dollar"></i>
        <h2>No Contract Selected</h2>
        <p>Select a contract from the pending list to review the final PDF</p>
    </div>

    <!-- Active Document State -->
    <div id="viewer-active-state" style="display: none;">
        <!-- Document Header -->
        <div class="document-header">
            <div class="doc-info">
                <h2 id="viewer-doc-title">Contract</h2>
                <span class="doc-number-badge" id="viewer-order-number"></span>
            </div>
        </div>

        <!-- PDF Viewer -->
        <div class="pdf-viewer-container">
            <iframe id="pdf-viewer" src="" frameborder="0"></iframe>
        </div>

        <!-- Action Buttons (below PDF viewer) -->
        <div class="viewer-actions" id="viewer-actions">
            <button class="btn btn-action-completed" id="btn-mark-completed" title="Mark as Completed">
                <i class="fas fa-check-circle"></i> Mark as Completed
            </button>
            <button class="btn btn-action-pending" id="btn-mark-pending" title="Mark as Pending" style="display: none;">
                <i class="fas fa-clock"></i> Mark as Pending
            </button>
        </div>
    </div>
</div>
