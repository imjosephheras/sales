<!-- ========================================= -->
<!-- 👁️ PREVIEW PANEL - Vista en Tiempo Real -->
<!-- ========================================= -->

<div class="preview-wrapper">

    <!-- Estado inicial: sin solicitud seleccionada -->
    <div id="preview-no-selection" class="preview-empty-state">
        <i class="fas fa-eye"></i>
        <h3>Preview Area</h3>
        <p>Select a request to see the contract preview</p>
    </div>

    <!-- Preview activo -->
    <div id="preview-active" style="display: none;">
        <div class="preview-header">
            <h3><i class="fas fa-file-alt"></i> Live Preview</h3>
            <div class="preview-info">
                <span id="preview-doc-type" class="preview-doc-badge">JWO</span>
                <button id="btn-refresh-preview" class="btn-icon" title="Refresh preview">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <div class="preview-scroll-container">
            <div class="preview-paper" id="live-preview-content">
                <div class="preview-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading preview...</p>
                </div>
            </div>
        </div>
    </div>

</div>
