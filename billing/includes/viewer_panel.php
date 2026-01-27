<div class="viewer-container">
    <!-- Empty State -->
    <div id="viewer-empty-state" class="viewer-empty-state">
        <i class="fas fa-file-invoice-dollar"></i>
        <h2>No Document Selected</h2>
        <p>Select a document from the pending list to view and process it</p>
    </div>

    <!-- Active Document State -->
    <div id="viewer-active-state" style="display: none;">
        <!-- Document Header -->
        <div class="document-header">
            <div class="doc-info">
                <h2 id="viewer-doc-title">Document</h2>
                <span class="doc-number-badge" id="viewer-order-number"></span>
            </div>
            <div class="doc-actions">
                <button class="btn btn-success" id="btn-complete" title="Mark as Completed">
                    <i class="fas fa-check-circle"></i> Completed
                </button>
                <button class="btn btn-primary" id="btn-download" title="Download PDF">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>
        </div>

        <!-- PDF Viewer -->
        <div class="pdf-viewer-container">
            <iframe id="pdf-viewer" src="" frameborder="0"></iframe>
        </div>
    </div>
</div>
