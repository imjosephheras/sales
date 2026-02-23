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
<div class="reports-hub">
    <h3 class="reports-hub-title">
        <i class="fas fa-folder-open"></i>
        <?= $t['wr_reports_hub_title'] ?? 'Reports' ?>
    </h3>
    <div class="reports-hub-grid">
        <!-- Reporte de Servicio -->
        <a href="../contract_generator/vent_hood_editor.php" target="_blank" class="report-tile" id="tileServiceReport">
            <div class="report-tile-icon" style="background: linear-gradient(135deg, #001f54, #003080);">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="report-tile-body">
                <h4><?= $t['wr_report_service'] ?? 'Service Report' ?></h4>
                <p><?= $t['wr_report_service_desc'] ?? 'Fill, print or download the service report' ?></p>
            </div>
            <div class="report-tile-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>
        <!-- Reporte Fotografico -->
        <div class="report-tile active" id="tilePhotoReport">
            <div class="report-tile-icon" style="background: linear-gradient(135deg, #a30000, #c70734);">
                <i class="fas fa-camera"></i>
            </div>
            <div class="report-tile-body">
                <h4><?= $t['wr_report_photo'] ?? 'Photo Report' ?></h4>
                <p><?= $t['wr_report_photo_desc'] ?? 'Photo documentation of completed services' ?></p>
            </div>
            <div class="report-tile-arrow"><i class="fas fa-chevron-down"></i></div>
        </div>
        <!-- Reporte de Producto -->
        <div class="report-tile disabled" id="tileProductReport">
            <div class="report-tile-icon" style="background: linear-gradient(135deg, #6f42c1, #8257d8);">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="report-tile-body">
                <h4><?= $t['wr_report_product'] ?? 'Product Report' ?></h4>
                <p><?= $t['wr_report_product_desc'] ?? 'Product report (coming soon)' ?></p>
            </div>
            <span class="report-tile-badge"><?= $t['wr_coming_soon'] ?? 'Coming soon' ?></span>
        </div>
    </div>
</div>

<div class="form-content">
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
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {

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

    // Photo Report tile: scroll to form
    var tilePhoto = document.getElementById("tilePhotoReport");
    if (tilePhoto) {
        tilePhoto.addEventListener("click", function() {
            var formContent = document.querySelector(".form-content");
            if (formContent) {
                formContent.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        });
    }

    document.querySelectorAll(".section-title").forEach(function(section) {
        var content = section.nextElementSibling;
        content.classList.add("hidden");
        section.classList.add("collapsed");

        section.addEventListener("click", function() {
            section.classList.toggle("collapsed");
            content.classList.toggle("hidden");
        });
    });

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
});
</script>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../app/Views/layouts/dashboard.php';
?>
