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

            <!-- Card 2: Action Buttons -->
            <div class="viewer-action-card" id="viewer-actions">
                <button class="btn btn-attach-doc" id="btn-attach-document" title="Attach Document">
                    <i class="fas fa-paperclip"></i> + Document
                </button>
                <button class="btn btn-action-completed" id="btn-mark-completed" title="Mark as Completed">
                    <i class="fas fa-check-circle"></i> Mark as Completed
                </button>
                <button class="btn btn-action-pending" id="btn-mark-pending" title="Mark as Pending" style="display: none;">
                    <i class="fas fa-clock"></i> Mark as Pending
                </button>
            </div>
        </div>

        <!-- Attached Documents Section -->
        <div class="attachments-section" id="attachments-section" style="display: none;">
            <div class="attachments-header">
                <div class="attachments-title">
                    <i class="fas fa-paperclip"></i>
                    <span>Attached Documents</span>
                    <span class="attachments-count" id="attachments-count">0</span>
                </div>
                <button class="btn-toggle-attachments" id="btn-toggle-attachments" title="Toggle attachments">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="attachments-list" id="attachments-list"></div>
        </div>

        <!-- PDF Viewer: Full width below cards -->
        <div class="pdf-viewer-container">
            <iframe id="pdf-viewer" src="" frameborder="0"></iframe>
        </div>
    </div>
</div>

<!-- Upload Attachment Modal -->
<div class="modal-overlay" id="attachment-modal">
    <div class="modal-box attachment-modal-box">
        <div class="attachment-modal-header">
            <h3><i class="fas fa-paperclip"></i> Attach Document</h3>
            <button class="btn-modal-close" id="btn-close-attachment-modal">&times;</button>
        </div>
        <form id="attachment-upload-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="attachment-file-type">Document Type</label>
                <select id="attachment-file-type" name="file_type" required>
                    <option value="">-- Select type --</option>
                    <option value="timesheet">Timesheet</option>
                    <option value="invoice">Invoice</option>
                    <option value="po">PO (Purchase Order)</option>
                    <option value="jwo_pdf">JWO PDF</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="attachment-file">Select File</label>
                <div class="file-upload-area" id="file-upload-area">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to select, drag & drop, or paste (Ctrl+V)</p>
                    <small>PDF, Images, Excel, Word, CSV (max 20MB)</small>
                    <input type="file" id="attachment-file" name="file" required
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv,.txt">
                </div>
                <div class="file-selected" id="file-selected" style="display: none;">
                    <i class="fas fa-file"></i>
                    <span id="file-selected-name"></span>
                    <button type="button" class="btn-remove-file" id="btn-remove-file">&times;</button>
                </div>
            </div>
            <div class="attachment-modal-actions">
                <button type="button" class="btn btn-cancel" id="btn-cancel-upload">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-submit-upload">
                    <i class="fas fa-upload"></i> Upload
                </button>
            </div>
        </form>
        <div class="upload-progress" id="upload-progress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <p class="progress-text" id="progress-text">Uploading...</p>
        </div>
    </div>
</div>
