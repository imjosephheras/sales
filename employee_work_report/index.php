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
include 'lang.php';
$t = $translations[$lang];

ob_start();
?>

<!-- LANGUAGE SWITCH -->
<div style="display:flex;gap:10px;margin-bottom:16px;justify-content:flex-end;">
    <a href="?lang=en" style="padding:6px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.85rem;background:<?= $lang == 'en' ? '#a30000;color:white;' : '#f3f4f6;color:#333;' ?>">EN</a>
    <a href="?lang=es" style="padding:6px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:0.85rem;background:<?= $lang == 'es' ? '#a30000;color:white;' : '#f3f4f6;color:#333;' ?>">ES</a>
</div>

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

<!-- VENHOOD REPORT BUTTON -->
<div class="venhood-action-bar">
    <button type="button" id="btnVentHoodReport" class="btn-venhood-report" title="<?= $t['wr_venhood_desc'] ?? 'Fill, print or download the Vent Hood Service Report' ?>">
        <i class="fas fa-file-pdf"></i> <?= $t['wr_venhood_btn'] ?? 'Vent Hood Report' ?>
    </button>
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
                    <span class="btn-desc"><?= $t["wr_btn_all_photos_desc"] ?? "All photos together (max 20 per page)" ?></span>
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
.venhood-action-bar {
    text-align: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f0f4ff;
    border-radius: 10px;
    border: 2px solid #d0d9f0;
}
.btn-venhood-report {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #001f54 0%, #003080 100%);
    color: white;
    padding: 14px 30px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,31,84,0.3);
}
.btn-venhood-report:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,31,84,0.4);
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

    var ventHoodBtn = document.getElementById("btnVentHoodReport");
    if (ventHoodBtn) {
        ventHoodBtn.addEventListener("click", function() {
            window.open("../contract_generator/vent_hood_editor.php", "_blank");
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
