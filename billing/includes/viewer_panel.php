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
                <button class="btn btn-doc-type btn-doc-timesheet" data-file-type="timesheet" title="Attach Timesheet">
                    <i class="fas fa-clock"></i> Timesheet
                </button>
                <button class="btn btn-doc-type btn-doc-invoice" data-file-type="invoice" title="Attach Invoice">
                    <i class="fas fa-file-invoice-dollar"></i> Invoice
                </button>
                <button class="btn btn-doc-type btn-doc-po" data-file-type="po" title="Attach PO">
                    <i class="fas fa-shopping-cart"></i> PO
                </button>
                <button class="btn btn-doc-type btn-doc-other" data-file-type="other" title="Attach Other Document">
                    <i class="fas fa-file-alt"></i> Other
                </button>
                <button class="btn btn-doc-scan" id="btn-scan-document" title="Escanear Documento">
                    <i class="fas fa-camera"></i> Escanear
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

<!-- Document Scanner Modal -->
<div class="scanner-modal" id="scanner-modal">

    <!-- Step 1: Camera Capture -->
    <div class="scanner-step" id="scanner-step-camera">
        <div class="scanner-header">
            <h3><i class="fas fa-camera"></i> Escanear Documento</h3>
            <button class="scanner-close-btn" id="scanner-close-camera">&times;</button>
        </div>
        <div class="scanner-camera-container">
            <video id="scanner-video" autoplay playsinline muted></video>
            <div class="scanner-guide-overlay">
                <div class="scanner-guide-frame">
                    <span class="guide-corner guide-tl"></span>
                    <span class="guide-corner guide-tr"></span>
                    <span class="guide-corner guide-bl"></span>
                    <span class="guide-corner guide-br"></span>
                </div>
                <p class="scanner-guide-text">Alinee el documento dentro del marco</p>
            </div>
            <div class="scanner-camera-error" id="scanner-camera-error" style="display:none;">
                <i class="fas fa-video-slash"></i>
                <p>No se puede acceder a la cámara</p>
            </div>
        </div>
        <div class="scanner-controls">
            <button class="scanner-btn-capture" id="scanner-btn-capture" title="Capturar">
                <span class="capture-ring"></span>
                <span class="capture-inner"></span>
            </button>
        </div>
    </div>

    <!-- Step 2: Crop / Corner Adjustment -->
    <div class="scanner-step" id="scanner-step-crop" style="display:none;">
        <div class="scanner-header">
            <h3><i class="fas fa-crop-alt"></i> Ajustar Documento</h3>
            <button class="scanner-close-btn" id="scanner-close-crop">&times;</button>
        </div>
        <div class="scanner-hint">
            <i class="fas fa-hand-pointer"></i> Arrastre las esquinas para ajustar el área del documento
        </div>
        <div class="scanner-crop-container">
            <canvas id="scanner-crop-canvas"></canvas>
            <div class="scanner-loading" id="scanner-crop-loading" style="display:none;">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Procesando...</p>
            </div>
        </div>
        <div class="scanner-controls scanner-controls-row">
            <button class="scanner-btn-secondary" id="scanner-btn-retake-crop">
                <i class="fas fa-redo"></i> Repetir
            </button>
            <button class="scanner-btn-primary" id="scanner-btn-apply-crop">
                <i class="fas fa-magic"></i> Aplicar
            </button>
        </div>
    </div>

    <!-- Step 3: Preview & Save -->
    <div class="scanner-step" id="scanner-step-preview" style="display:none;">
        <div class="scanner-header">
            <h3><i class="fas fa-eye"></i> Vista Previa</h3>
            <button class="scanner-close-btn" id="scanner-close-preview">&times;</button>
        </div>
        <div class="scanner-filter-bar">
            <button class="scanner-filter-btn active" data-filter="bw">
                <i class="fas fa-file-alt"></i> B&N
            </button>
            <button class="scanner-filter-btn" data-filter="gray">
                <i class="fas fa-adjust"></i> Gris
            </button>
            <button class="scanner-filter-btn" data-filter="color">
                <i class="fas fa-palette"></i> Color
            </button>
        </div>
        <div class="scanner-preview-container">
            <canvas id="scanner-preview-canvas"></canvas>
            <div class="scanner-loading" id="scanner-preview-loading" style="display:none;">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Aplicando filtro...</p>
            </div>
            <div class="scanner-loading" id="scanner-save-loading" style="display:none;">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Guardando...</p>
            </div>
        </div>
        <div class="scanner-controls scanner-controls-row">
            <button class="scanner-btn-secondary" id="scanner-btn-retake-preview">
                <i class="fas fa-redo"></i> Repetir
            </button>
            <button class="scanner-btn-success" id="scanner-btn-save">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>

</div>

<!-- Upload Attachment Modal -->
<div class="modal-overlay" id="attachment-modal">
    <div class="modal-box attachment-modal-box">
        <div class="attachment-modal-header">
            <h3><i class="fas fa-paperclip"></i> <span id="attachment-modal-title">Attach Document</span></h3>
            <button class="btn-modal-close" id="btn-close-attachment-modal">&times;</button>
        </div>
        <form id="attachment-upload-form" enctype="multipart/form-data">
            <input type="hidden" id="attachment-file-type" name="file_type" value="">
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
