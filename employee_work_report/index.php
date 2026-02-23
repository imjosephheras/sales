<?php
require_once __DIR__ . '/../app/bootstrap.php';
Middleware::module('work_report');

$page_title = 'Employee Work Report';
$page_icon  = 'fas fa-clipboard-list';
$page_slug  = 'work_report';

// Capture header.php CSS content (it outputs <head>...</head>)
ob_start();
include 'header.php';
$_headerRaw = ob_get_clean();
$page_head = preg_replace('/<\/?head>/i', '', $_headerRaw);

// LANGUAGE CONTROLLER
if (isset($_GET["lang"])) {
    $_SESSION["lang"] = $_GET["lang"];
}

$lang = $_SESSION["lang"] ?? "en";
$page_lang = $lang;
include 'lang.php';
$t = $translations[$lang];

ob_start();
?>

<div class="container">

<!-- HEADER -->
<div class="form-header"
     style="text-align:center; margin-bottom:25px; padding:25px 0;
            background:#a30000; border-radius:10px;">
    <h2 style="color:white; margin:0;">
        <?= $t["wr_title"] ?? "Employee Work Report" ?>
    </h2>
    <p style="color:white; margin-top:5px;">
        <?= $t["wr_subtitle"] ?? "Internal form for job documentation" ?>
    </p>
</div>

<!-- REPORTS HUB -->
<div class="reports-hub" id="reportsHub">
    <h3 class="reports-hub-title">
        <i class="fas fa-folder-open"></i>
        <?= $t['wr_reports_hub_title'] ?? 'Reports' ?>
    </h3>
    <div class="reports-hub-grid">
        <!-- Service Report -->
        <div class="report-tile" id="tileServiceReport" data-report="service">
            <div class="report-tile-icon" style="background: linear-gradient(135deg, #001f54, #003080);">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="report-tile-body">
                <h4><?= $t['wr_report_service'] ?? 'Service Report' ?></h4>
                <p><?= $t['wr_report_service_desc'] ?? 'Fill, print or download the service report' ?></p>
            </div>
            <div class="report-tile-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>
        <!-- Photo Report -->
        <div class="report-tile" id="tilePhotoReport" data-report="photo">
            <div class="report-tile-icon" style="background: linear-gradient(135deg, #a30000, #c70734);">
                <i class="fas fa-camera"></i>
            </div>
            <div class="report-tile-body">
                <h4><?= $t['wr_report_photo'] ?? 'Photo Report' ?></h4>
                <p><?= $t['wr_report_photo_desc'] ?? 'Photo documentation of completed services' ?></p>
            </div>
            <div class="report-tile-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>
        <!-- Product Report -->
        <div class="report-tile" id="tileProductReport" data-report="product">
            <div class="report-tile-icon" style="background: linear-gradient(135deg, #6f42c1, #8257d8);">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="report-tile-body">
                <h4><?= $t['wr_report_product'] ?? 'Product Report' ?></h4>
                <p><?= $t['wr_report_product_desc'] ?? 'Invoice for replacement parts' ?></p>
            </div>
            <div class="report-tile-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>
        <!-- System Prime (external link) -->
        <a class="report-tile" href="https://primefsgroup.com/addresses" target="_blank" rel="noopener noreferrer">
            <div class="report-tile-icon" style="background: linear-gradient(135deg, #e8a817, #f5c842);">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="report-tile-body">
                <h4><?= $t['wr_report_prime'] ?? 'System Prime' ?></h4>
                <p><?= $t['wr_report_prime_desc'] ?? 'Access external Prime system for products and accounts' ?></p>
            </div>
            <div class="report-tile-arrow"><i class="fas fa-external-link-alt"></i></div>
        </a>
        <!-- Manage Products -->
        <div class="report-tile" id="tileManageProducts" data-report="manage_products">
            <div class="report-tile-icon" style="background: linear-gradient(135deg, #0d7c5f, #10a37f);">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="report-tile-body">
                <h4><?= ($lang === 'es') ? 'Administrar Productos' : 'Manage Products' ?></h4>
                <p><?= ($lang === 'es') ? 'Crear y administrar catálogo de productos' : 'Create and manage product catalog' ?></p>
            </div>
            <div class="report-tile-arrow"><i class="fas fa-chevron-right"></i></div>
        </div>
    </div>
</div>

<!-- BACK TO REPORTS BUTTON (hidden by default) -->
<div id="backToReportsBar" style="display:none; margin-bottom:20px;">
    <button type="button" id="btnBackToReports" class="btn-back-to-reports">
        <i class="fas fa-arrow-left"></i>
        <?= ($lang === 'es') ? 'Volver a Reportes' : 'Back to Reports' ?>
    </button>
</div>

<!-- EDITOR CONTAINER: iframe for Service Report -->
<div id="editorContainer" style="display:none;">
    <iframe id="editorFrame" src="about:blank" style="width:100%; border:none; border-radius:10px; background:#525659;"></iframe>
</div>

<!-- PRODUCT EDITOR CONTAINER: separate iframe to preserve selections -->
<div id="productEditorContainer" style="display:none;">
    <iframe id="productEditorFrame" src="about:blank" style="width:100%; border:none; border-radius:10px; background:#525659;"></iframe>
</div>

<!-- PHOTO REPORT CONTENT (inline form) -->
<div class="form-content" id="photoReportContent" style="display:none;">
<form id="main_form" action="enviar_correo.php" method="POST" enctype="multipart/form-data">

    <!-- REPORT TYPE SELECTION -->
    <div class="report-type-section">
        <h3 class="report-type-title"><?= $t["wr_report_type"] ?? "Select Report Type" ?></h3>
        <div class="report-type-buttons">
            <label class="report-type-btn selected">
                <input type="radio" name="report_type" value="before_after" checked>
                <div class="btn-content">
                    <span class="btn-icon">1</span>
                    <span class="btn-label"><?= $t["wr_btn_before_after"] ?? "Before & After" ?></span>
                    <span class="btn-desc"><?= $t["wr_btn_before_after_desc"] ?? "Side-by-side comparison photos" ?></span>
                </div>
            </label>
            <label class="report-type-btn">
                <input type="radio" name="report_type" value="all_photos">
                <div class="btn-content">
                    <span class="btn-icon">2</span>
                    <span class="btn-label"><?= $t["wr_btn_all_photos"] ?? "All Photos" ?></span>
                    <span class="btn-desc"><?= $t["wr_btn_all_photos_desc"] ?? "All photos together, up to 100" ?></span>
                </div>
            </label>
        </div>
    </div>

    <!-- SECTION 1 -->
    <div class="section-title collapsible">
        <?= $t["wr_sec1_title"] ?> <span class="toggle-icon">&#9660;</span>
    </div>
    <div class="section-content hidden">
        <?php include 'form_part1_questions.php'; ?>
    </div>

    <!-- SECTION 2 -->
    <div class="section-title collapsible" id="section2-title"
         data-title-before-after="<?= $t["wr_sec2_title"] ?>"
         data-title-all-photos="<?= $t["wr_sec2_title_all"] ?>">
        <span id="section2-title-text"><?= $t["wr_sec2_title"] ?></span> <span class="toggle-icon">&#9660;</span>
    </div>
    <div class="section-content hidden">
        <?php include 'form_part2_photos.php'; ?>
    </div>

    <!-- SECTION: NOTES -->
    <div class="section-title collapsible">
        <?= $t["wr_notes_title"] ?? "Notes" ?> <span class="toggle-icon">&#9660;</span>
    </div>
    <div class="section-content hidden">
        <?php include 'form_part_notes.php'; ?>
    </div>

    <!-- SECTION 3 (INTERNAL ONLY) -->
    <div class="section-internal-only">
        <?php include 'form_part3_template.php'; ?>
    </div>

    <!-- SUBMIT -->
    <input type="hidden" name="action" id="formAction" value="send">
    <div class="form-actions" style="text-align:center; margin-top:25px;">
        <button type="button" id="btnPreview"
                style="padding:10px 25px; font-size:16px; font-weight:600;
                       background:#a30000; color:white; border:none;
                       border-radius:6px; cursor:pointer;">
            <?= $t["send"] ?? "Send" ?>
        </button>
    </div>

    <!-- PREVIEW MODAL -->
    <div id="previewModal" style="
        display:none; position:fixed; inset:0;
        background:rgba(0,0,0,0.6);
        justify-content:center; align-items:center; z-index:9999;">

        <div style="
            background:white; padding:25px; border-radius:10px;
            max-width:800px; width:90%;
            box-shadow:0 4px 20px rgba(0,0,0,0.3);
            overflow-y:auto; max-height:90vh;">

            <h2 style="color:#a30000; margin-bottom:10px;">
                <?= $t["preview_form"] ?>
            </h2>

            <div id="previewContent"
                 style="text-align:left; font-size:15px; line-height:1.5;">
            </div>

            <div style="text-align:center; margin-top:20px;">
                <button id="btnPrint"
                        style="background:#a30000; color:white;
                               padding:10px 20px; border:none; border-radius:6px;
                               font-weight:600; margin:5px; cursor:pointer;">
                    <?= $t["print"] ?? "Print" ?>
                </button>
                <button id="confirmSend"
                        style="background:#007bff; color:white;
                               padding:10px 20px; border:none; border-radius:6px;
                               font-weight:600; margin:5px; cursor:pointer;">
                    <?= $t["confirm_send"] ?? "Send" ?>
                </button>
                <button id="cancelPreview"
                        style="background:#666; color:white;
                               padding:10px 20px; border:none; border-radius:6px;
                               font-weight:600; margin:5px; cursor:pointer;">
                    <?= $t["cancel"] ?? "Cancel" ?>
                </button>
            </div>

        </div>
    </div>

</form>
</div>

<!-- MANAGE PRODUCTS CONTENT (inline view) -->
<div id="manageProductsContent" style="display:none;">
    <div class="manage-products-container">
        <h3 class="mp-title">
            <i class="fas fa-boxes"></i>
            <?= ($lang === 'es') ? 'Administrar Productos' : 'Manage Products' ?>
        </h3>

        <!-- Add Product Form -->
        <div class="mp-form-card">
            <h4 class="mp-form-title">
                <i class="fas fa-plus-circle"></i>
                <?= ($lang === 'es') ? 'Agregar Nuevo Producto' : 'Add New Product' ?>
            </h4>
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="mp-form-group">
                    <label class="mp-label" for="productName">
                        <?= ($lang === 'es') ? 'Nombre del Producto' : 'Product Name' ?> <span style="color:#a30000;">*</span>
                    </label>
                    <input type="text" id="productName" name="product_name" class="mp-input"
                           placeholder="<?= ($lang === 'es') ? 'Ingrese nombre del producto' : 'Enter product name' ?>" required>
                </div>
                <div class="mp-form-group">
                    <label class="mp-label">
                        <?= ($lang === 'es') ? 'Imagen del Producto' : 'Product Image' ?> <span style="color:#a30000;">*</span>
                    </label>
                    <!-- Paste Zone -->
                    <div class="mp-paste-zone" id="pasteZone" tabindex="0">
                        <div class="mp-paste-icon"><i class="fas fa-clipboard"></i></div>
                        <p class="mp-paste-text">
                            <?= ($lang === 'es') ? 'Haz clic aquí y pega una imagen (Ctrl+V)' : 'Click here and paste an image (Ctrl+V)' ?>
                        </p>
                        <p class="mp-paste-hint">
                            <?= ($lang === 'es') ? 'O usa el botón de abajo para subir un archivo' : 'Or use the button below to upload a file' ?>
                        </p>
                    </div>
                    <!-- Image Preview -->
                    <div class="mp-image-preview" id="imagePreview" style="display:none;">
                        <img id="previewImg" src="" alt="Preview">
                        <button type="button" class="mp-remove-image" id="removeImageBtn" title="Remove image">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <!-- File Upload -->
                    <div class="mp-upload-row">
                        <label class="mp-upload-btn" for="productImageFile">
                            <i class="fas fa-upload"></i>
                            <?= ($lang === 'es') ? 'Subir Archivo' : 'Upload File' ?>
                        </label>
                        <input type="file" id="productImageFile" name="product_image" accept="image/*" style="display:none;">
                        <span class="mp-file-name" id="fileName"></span>
                    </div>
                    <input type="hidden" id="productImageBase64" name="product_image_base64" value="">
                </div>
                <div class="mp-form-actions">
                    <button type="submit" class="mp-save-btn" id="saveProductBtn">
                        <i class="fas fa-save"></i>
                        <?= ($lang === 'es') ? 'Guardar Producto' : 'Save Product' ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Products List -->
        <div class="mp-list-card">
            <h4 class="mp-form-title">
                <i class="fas fa-th-large"></i>
                <?= ($lang === 'es') ? 'Catálogo de Productos' : 'Product Catalog' ?>
                <span class="mp-product-count" id="productCount"></span>
            </h4>
            <div id="productGrid" class="mp-product-grid">
                <div class="mp-loading"><i class="fas fa-spinner fa-spin"></i> <?= ($lang === 'es') ? 'Cargando...' : 'Loading...' ?></div>
            </div>
        </div>
    </div>
</div>

</div>

<style>
.section-title {
    background-color: #001f54;
    color: white;
    padding: 12px 16px;
    font-size: 18px;
    font-weight: bold;
    border-radius: 8px;
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
}
.section-title:hover { background-color: #003080; }
.section-title.collapsed .toggle-icon { transform: rotate(-90deg); }
.section-content.hidden { display: none; }
.section-internal-only { display: none; }

.report-type-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border: 2px solid #e0e0e0;
}
.report-type-title {
    color: #001f54;
    margin: 0 0 15px 0;
    text-align: center;
    font-size: 16px;
}
.report-type-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}
.report-type-btn {
    flex: 1;
    max-width: 250px;
    cursor: pointer;
}
.report-type-btn input[type="radio"] { display: none; }
.report-type-btn .btn-content {
    background: white;
    border: 3px solid #ddd;
    border-radius: 12px;
    padding: 20px 15px;
    text-align: center;
    transition: all 0.3s ease;
}
.report-type-btn:hover .btn-content {
    border-color: #a30000;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.report-type-btn.selected .btn-content {
    border-color: #a30000;
    background: #fff5f5;
    box-shadow: 0 4px 12px rgba(163,0,0,0.2);
}
.report-type-btn .btn-icon {
    display: block;
    width: 40px; height: 40px;
    line-height: 40px;
    background: #a30000;
    color: white;
    border-radius: 50%;
    font-size: 18px;
    font-weight: bold;
    margin: 0 auto 10px;
}
.report-type-btn .btn-label {
    display: block;
    font-size: 16px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}
.report-type-btn .btn-desc {
    display: block;
    font-size: 12px;
    color: #666;
}
/* Reports Hub */
.reports-hub {
    margin-bottom: 25px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 2px solid #e0e4ea;
}
.reports-hub-title {
    color: #001f54;
    margin: 0 0 16px 0;
    font-size: 17px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}
.reports-hub-grid {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.report-tile {
    display: flex;
    align-items: center;
    gap: 15px;
    background: white;
    border: 2px solid #e0e4ea;
    border-radius: 10px;
    padding: 14px 18px;
    cursor: pointer;
    transition: all 0.25s ease;
    text-decoration: none;
    color: inherit;
    position: relative;
}
.report-tile:hover {
    border-color: #003080;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,31,84,0.12);
}
.report-tile.active {
    border-color: #a30000;
    background: #fff5f5;
    box-shadow: 0 2px 10px rgba(163,0,0,0.1);
}
.report-tile.disabled {
    opacity: 0.65;
    cursor: default;
}
.report-tile.disabled:hover {
    border-color: #e0e4ea;
    transform: none;
    box-shadow: none;
}
.report-tile-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    flex-shrink: 0;
}
.report-tile-body {
    flex: 1;
    min-width: 0;
}
.report-tile-body h4 {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 2px 0;
}
.report-tile-body p {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}
.report-tile-arrow {
    color: #94a3b8;
    font-size: 14px;
    flex-shrink: 0;
}
.report-tile:hover .report-tile-arrow {
    color: #003080;
}
.report-tile.active .report-tile-arrow {
    color: #a30000;
}
.report-tile-badge {
    font-size: 11px;
    font-weight: 600;
    color: #6f42c1;
    background: #f3e8ff;
    padding: 3px 10px;
    border-radius: 12px;
    white-space: nowrap;
    flex-shrink: 0;
}
/* Back to Reports Button */
.btn-back-to-reports {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #001f54;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-back-to-reports:hover {
    background: #003080;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,31,84,0.2);
}
/* Editor iframe container */
#editorFrame,
#productEditorFrame {
    min-height: 85vh;
}
@media (max-width: 480px) {
    .report-tile {
        padding: 12px 14px;
    }
    .report-tile-icon {
        width: 38px;
        height: 38px;
        font-size: 16px;
    }
    .report-tile-body h4 {
        font-size: 14px;
    }
}

/* =============================================
   MANAGE PRODUCTS STYLES
   ============================================= */
.manage-products-container {
    max-width: 100%;
}
.mp-title {
    color: #0d7c5f;
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.mp-form-card,
.mp-list-card {
    background: #f8f9fa;
    border: 2px solid #e0e4ea;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}
.mp-form-title {
    color: #1e293b;
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.mp-form-title i {
    color: #0d7c5f;
}
.mp-product-count {
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    margin-left: auto;
}
.mp-form-group {
    margin-bottom: 16px;
}
.mp-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}
.mp-input {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #dde1e7;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: white;
}
.mp-input:focus {
    outline: none;
    border-color: #0d7c5f;
    box-shadow: 0 0 0 3px rgba(13,124,95,0.12);
}
/* Paste Zone */
.mp-paste-zone {
    border: 2px dashed #c0c7d0;
    border-radius: 10px;
    padding: 25px;
    text-align: center;
    background: #fafbfc;
    cursor: pointer;
    transition: all 0.2s ease;
    outline: none;
}
.mp-paste-zone:hover,
.mp-paste-zone:focus {
    border-color: #0d7c5f;
    background: #f0faf6;
}
.mp-paste-zone.drag-over {
    border-color: #0d7c5f;
    background: #e6f7f0;
}
.mp-paste-icon {
    font-size: 32px;
    color: #94a3b8;
    margin-bottom: 8px;
}
.mp-paste-zone:hover .mp-paste-icon,
.mp-paste-zone:focus .mp-paste-icon {
    color: #0d7c5f;
}
.mp-paste-text {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    margin: 0 0 4px 0;
}
.mp-paste-hint {
    font-size: 12px;
    color: #94a3b8;
    margin: 0;
}
/* Image Preview */
.mp-image-preview {
    position: relative;
    display: inline-block;
    margin-top: 10px;
    border: 2px solid #e0e4ea;
    border-radius: 10px;
    overflow: hidden;
    background: white;
}
.mp-image-preview img {
    display: block;
    max-width: 250px;
    max-height: 200px;
    object-fit: contain;
}
.mp-remove-image {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 50%;
    background: rgba(220,38,38,0.9);
    color: white;
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.mp-remove-image:hover {
    background: #dc2626;
}
/* Upload Row */
.mp-upload-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
}
.mp-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #334155;
    color: white;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.mp-upload-btn:hover {
    background: #1e293b;
    transform: translateY(-1px);
}
.mp-file-name {
    font-size: 13px;
    color: #64748b;
}
/* Form Actions */
.mp-form-actions {
    text-align: right;
    margin-top: 16px;
}
.mp-save-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    background: #0d7c5f;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.mp-save-btn:hover {
    background: #0a6b51;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13,124,95,0.3);
}
.mp-save-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
/* Products Grid */
.mp-product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px;
}
.mp-product-card {
    background: white;
    border: 2px solid #e0e4ea;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.2s;
    position: relative;
}
.mp-product-card:hover {
    border-color: #0d7c5f;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.mp-product-card-img {
    width: 100%;
    height: 120px;
    object-fit: contain;
    background: #f8f9fa;
    padding: 8px;
}
.mp-product-card-body {
    padding: 10px 12px;
}
.mp-product-card-name {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.3;
    margin: 0 0 6px 0;
    word-break: break-word;
}
.mp-product-card-date {
    font-size: 11px;
    color: #94a3b8;
}
.mp-product-delete {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 26px;
    height: 26px;
    border: none;
    border-radius: 50%;
    background: rgba(220,38,38,0.85);
    color: white;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}
.mp-product-card:hover .mp-product-delete {
    opacity: 1;
}
.mp-product-delete:hover {
    background: #dc2626;
}
.mp-loading {
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
    padding: 30px;
    grid-column: 1 / -1;
}
.mp-empty {
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
    padding: 30px;
    grid-column: 1 / -1;
}
.mp-toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 8px;
    color: white;
    font-size: 14px;
    font-weight: 600;
    z-index: 10000;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.mp-toast.show {
    opacity: 1;
    transform: translateY(0);
}
.mp-toast.success {
    background: #0d7c5f;
}
.mp-toast.error {
    background: #dc2626;
}
@media (max-width: 480px) {
    .mp-product-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // =============================================
    // SPA NAVIGATION STATE
    // =============================================
    var reportsHub = document.getElementById("reportsHub");
    var backBar = document.getElementById("backToReportsBar");
    var editorContainer = document.getElementById("editorContainer");
    var editorFrame = document.getElementById("editorFrame");
    var productEditorContainer = document.getElementById("productEditorContainer");
    var productEditorFrame = document.getElementById("productEditorFrame");
    var photoContent = document.getElementById("photoReportContent");
    var manageProductsContent = document.getElementById("manageProductsContent");
    var activeReport = null;
    var productEditorLoaded = false;

    var editorUrls = {
        service: "../contract_generator/vent_hood_editor.php?embedded=1",
        product: "../contract_generator/product_report_editor.php?embedded=1"
    };

    /**
     * Open a report editor: hide hub, show back button and content
     */
    function openReport(type) {
        activeReport = type;

        // Hide the reports hub
        reportsHub.style.display = "none";

        // Show back button
        backBar.style.display = "block";

        if (type === "photo") {
            // Show inline photo report form
            photoContent.style.display = "block";
            editorContainer.style.display = "none";
            productEditorContainer.style.display = "none";
            manageProductsContent.style.display = "none";
            photoContent.scrollIntoView({ behavior: "smooth", block: "start" });
        } else if (type === "manage_products") {
            // Show manage products view
            manageProductsContent.style.display = "block";
            photoContent.style.display = "none";
            editorContainer.style.display = "none";
            productEditorContainer.style.display = "none";
            loadProducts();
            manageProductsContent.scrollIntoView({ behavior: "smooth", block: "start" });
        } else if (type === "product") {
            // Show product editor in its own persistent iframe
            photoContent.style.display = "none";
            manageProductsContent.style.display = "none";
            editorContainer.style.display = "none";
            productEditorContainer.style.display = "block";
            // Only load once - preserve state on subsequent opens
            if (!productEditorLoaded) {
                productEditorFrame.src = editorUrls.product;
                productEditorLoaded = true;
            }
            productEditorContainer.scrollIntoView({ behavior: "smooth", block: "start" });
        } else {
            // Load service editor in iframe
            photoContent.style.display = "none";
            manageProductsContent.style.display = "none";
            productEditorContainer.style.display = "none";
            editorContainer.style.display = "block";
            editorFrame.src = editorUrls[type];
            editorContainer.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }

    /**
     * Return to the reports hub: hide editors, show hub
     */
    function backToReports() {
        activeReport = null;

        // Show the reports hub
        reportsHub.style.display = "";

        // Hide back button
        backBar.style.display = "none";

        // Hide service editor and reset it
        editorContainer.style.display = "none";
        editorFrame.src = "about:blank";

        // Hide product editor but do NOT reset its src - preserve selections
        productEditorContainer.style.display = "none";

        photoContent.style.display = "none";
        manageProductsContent.style.display = "none";

        // Scroll to top of hub
        reportsHub.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    // =============================================
    // TILE CLICK HANDLERS
    // =============================================
    document.getElementById("tileServiceReport").addEventListener("click", function() {
        openReport("service");
    });

    document.getElementById("tilePhotoReport").addEventListener("click", function() {
        openReport("photo");
    });

    document.getElementById("tileProductReport").addEventListener("click", function() {
        openReport("product");
    });

    document.getElementById("tileManageProducts").addEventListener("click", function() {
        openReport("manage_products");
    });

    // Back button
    document.getElementById("btnBackToReports").addEventListener("click", backToReports);

    // =============================================
    // PHOTO REPORT: Report Type Buttons
    // =============================================
    var section2Title = document.getElementById("section2-title");
    var section2TitleText = document.getElementById("section2-title-text");

    document.querySelectorAll(".report-type-btn").forEach(function(btn) {
        btn.addEventListener("click", function() {
            document.querySelectorAll(".report-type-btn").forEach(function(b) { b.classList.remove("selected"); });
            btn.classList.add("selected");

            var radioInput = btn.querySelector('input[type="radio"]');
            if (radioInput) {
                var mode = radioInput.value;
                if (section2Title && section2TitleText) {
                    if (mode === "all_photos") {
                        section2TitleText.textContent = section2Title.dataset.titleAllPhotos;
                    } else {
                        section2TitleText.textContent = section2Title.dataset.titleBeforeAfter;
                    }
                }
                if (typeof window.switchPhotoMode === 'function') {
                    window.switchPhotoMode(mode);
                }
            }
        });
    });

    // =============================================
    // COLLAPSIBLE SECTIONS
    // =============================================
    document.querySelectorAll(".section-title").forEach(function(section) {
        var content = section.nextElementSibling;
        content.classList.add("hidden");
        section.classList.add("collapsed");

        section.addEventListener("click", function() {
            section.classList.toggle("collapsed");
            content.classList.toggle("hidden");
        });
    });

    // =============================================
    // PREVIEW / SEND MODAL
    // =============================================
    document.getElementById("btnPreview").addEventListener("click", function() {
        var form = document.getElementById("main_form");
        var data = new FormData(form);

        var html = "<table style='width:100%; border-collapse:collapse;'>";
        data.forEach(function(value, key) {
            if (typeof value === "string" && value.trim() !== "") {
                html += "<tr><td style='font-weight:bold; padding:6px; border-bottom:1px solid #ddd;'>" + key + "</td><td style='padding:6px; border-bottom:1px solid #ddd;'>" + value + "</td></tr>";
            }
        });
        html += "</table>";

        document.getElementById("previewContent").innerHTML = html;
        document.getElementById("previewModal").style.display = "flex";
    });

    document.getElementById("cancelPreview").onclick = function() {
        document.getElementById("previewModal").style.display = "none";
    };

    document.getElementById("btnPrint").onclick = function() {
        document.getElementById("formAction").value = "print_only";
        document.getElementById("main_form").target = "_blank";
        document.getElementById("main_form").submit();
        setTimeout(function() {
            document.getElementById("main_form").target = "_self";
            document.getElementById("previewModal").style.display = "none";
        }, 500);
    };

    document.getElementById("confirmSend").onclick = function() {
        document.getElementById("formAction").value = "send";
        document.getElementById("main_form").target = "_self";
        document.getElementById("main_form").submit();
    };

    // =============================================
    // MANAGE PRODUCTS FUNCTIONALITY
    // =============================================
    var mpImageBase64 = "";
    var mpImageFile = null;
    var pasteZone = document.getElementById("pasteZone");
    var imagePreview = document.getElementById("imagePreview");
    var previewImg = document.getElementById("previewImg");
    var productImageFile = document.getElementById("productImageFile");
    var productImageBase64 = document.getElementById("productImageBase64");
    var fileNameEl = document.getElementById("fileName");
    var removeImageBtn = document.getElementById("removeImageBtn");

    function showImagePreview(src) {
        previewImg.src = src;
        imagePreview.style.display = "inline-block";
        pasteZone.style.display = "none";
    }

    function clearImageSelection() {
        mpImageBase64 = "";
        mpImageFile = null;
        productImageBase64.value = "";
        productImageFile.value = "";
        fileNameEl.textContent = "";
        previewImg.src = "";
        imagePreview.style.display = "none";
        pasteZone.style.display = "";
    }

    // Clipboard Paste
    pasteZone.addEventListener("click", function() {
        pasteZone.focus();
    });

    document.addEventListener("paste", function(e) {
        if (manageProductsContent.style.display === "none") return;
        var items = e.clipboardData && e.clipboardData.items;
        if (!items) return;
        for (var i = 0; i < items.length; i++) {
            if (items[i].type.indexOf("image") !== -1) {
                e.preventDefault();
                var blob = items[i].getAsFile();
                var reader = new FileReader();
                reader.onload = function(evt) {
                    mpImageBase64 = evt.target.result;
                    mpImageFile = null;
                    productImageBase64.value = mpImageBase64;
                    productImageFile.value = "";
                    fileNameEl.textContent = "";
                    showImagePreview(mpImageBase64);
                };
                reader.readAsDataURL(blob);
                break;
            }
        }
    });

    // Drag and Drop
    pasteZone.addEventListener("dragover", function(e) {
        e.preventDefault();
        pasteZone.classList.add("drag-over");
    });
    pasteZone.addEventListener("dragleave", function() {
        pasteZone.classList.remove("drag-over");
    });
    pasteZone.addEventListener("drop", function(e) {
        e.preventDefault();
        pasteZone.classList.remove("drag-over");
        var file = e.dataTransfer.files[0];
        if (file && file.type.indexOf("image") !== -1) {
            var reader = new FileReader();
            reader.onload = function(evt) {
                mpImageBase64 = evt.target.result;
                mpImageFile = null;
                productImageBase64.value = mpImageBase64;
                productImageFile.value = "";
                fileNameEl.textContent = "";
                showImagePreview(mpImageBase64);
            };
            reader.readAsDataURL(file);
        }
    });

    // File Upload
    productImageFile.addEventListener("change", function() {
        if (this.files && this.files[0]) {
            var file = this.files[0];
            mpImageFile = file;
            mpImageBase64 = "";
            productImageBase64.value = "";
            fileNameEl.textContent = file.name;
            var reader = new FileReader();
            reader.onload = function(evt) {
                showImagePreview(evt.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove Image
    removeImageBtn.addEventListener("click", function() {
        clearImageSelection();
    });

    // Toast notifications
    function showToast(message, type) {
        var existing = document.querySelector(".mp-toast");
        if (existing) existing.remove();
        var toast = document.createElement("div");
        toast.className = "mp-toast " + type;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(function() { toast.classList.add("show"); }, 10);
        setTimeout(function() {
            toast.classList.remove("show");
            setTimeout(function() { toast.remove(); }, 300);
        }, 3000);
    }

    // Save Product
    document.getElementById("addProductForm").addEventListener("submit", function(e) {
        e.preventDefault();
        var name = document.getElementById("productName").value.trim();
        if (!name) {
            showToast("Product name is required", "error");
            return;
        }
        if (!mpImageBase64 && !mpImageFile) {
            showToast("Product image is required", "error");
            return;
        }

        var formData = new FormData();
        formData.append("product_name", name);

        if (mpImageBase64) {
            formData.append("product_image_base64", mpImageBase64);
        } else if (mpImageFile) {
            formData.append("product_image", mpImageFile);
        }

        var saveBtn = document.getElementById("saveProductBtn");
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "save_product.php", true);
        xhr.onload = function() {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> <?= ($lang === "es") ? "Guardar Producto" : "Save Product" ?>';
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    showToast(resp.message, "success");
                    document.getElementById("productName").value = "";
                    clearImageSelection();
                    loadProducts();
                } else {
                    showToast(resp.message || "Error saving product", "error");
                }
            } catch(err) {
                showToast("Server error", "error");
            }
        };
        xhr.onerror = function() {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> <?= ($lang === "es") ? "Guardar Producto" : "Save Product" ?>';
            showToast("Network error", "error");
        };
        xhr.send(formData);
    });

    // Load Products
    function loadProducts() {
        var grid = document.getElementById("productGrid");
        var countEl = document.getElementById("productCount");
        grid.innerHTML = '<div class="mp-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_products.php", true);
        xhr.onload = function() {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success && resp.products) {
                    countEl.textContent = "(" + resp.products.length + ")";
                    if (resp.products.length === 0) {
                        grid.innerHTML = '<div class="mp-empty"><i class="fas fa-box-open"></i><br><?= ($lang === "es") ? "No hay productos aún" : "No products yet" ?></div>';
                        return;
                    }
                    var html = "";
                    resp.products.forEach(function(p) {
                        html += '<div class="mp-product-card" data-id="' + p.id + '">' +
                            '<button class="mp-product-delete" onclick="deleteProduct(' + p.id + ', this)" title="Delete">' +
                                '<i class="fas fa-trash"></i>' +
                            '</button>' +
                            '<img class="mp-product-card-img" src="../' + escapeAttr(p.image_path) + '" alt="' + escapeAttr(p.name) + '">' +
                            '<div class="mp-product-card-body">' +
                                '<p class="mp-product-card-name">' + escapeHtml(p.name) + '</p>' +
                                '<span class="mp-product-card-date">' + (p.created_at || '') + '</span>' +
                            '</div>' +
                        '</div>';
                    });
                    grid.innerHTML = html;
                } else {
                    grid.innerHTML = '<div class="mp-empty">Error loading products</div>';
                }
            } catch(err) {
                grid.innerHTML = '<div class="mp-empty">Error loading products</div>';
            }
        };
        xhr.onerror = function() {
            grid.innerHTML = '<div class="mp-empty">Network error</div>';
        };
        xhr.send();
    }

    // Delete Product
    window.deleteProduct = function(id, btn) {
        if (!confirm("<?= ($lang === 'es') ? '¿Eliminar este producto?' : 'Delete this product?' ?>")) return;
        var formData = new FormData();
        formData.append("id", id);

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_product.php", true);
        xhr.onload = function() {
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                    showToast(resp.message, "success");
                    loadProducts();
                } else {
                    showToast(resp.message || "Error deleting product", "error");
                }
            } catch(err) {
                showToast("Server error", "error");
            }
        };
        xhr.send(formData);
    };

    // Utility functions for manage products
    function escapeHtml(str) {
        var div = document.createElement("div");
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
    function escapeAttr(str) {
        return String(str).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }
});
</script>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../app/Views/layouts/dashboard.php';
?>
