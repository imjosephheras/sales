<?php
session_start();
include 'header.php';

// 🌐 LANGUAGE CONTROLLER
if (isset($_GET["lang"])) {
    $_SESSION["lang"] = $_GET["lang"];
}

$lang = $_SESSION["lang"] ?? "en";
include 'lang.php';
$t = $translations[$lang];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<body>

<!-- 🏠 HOME BUTTON -->
<a href="../index.php" class="home-btn">🏠 <?= $t["home"] ?></a>

<!-- 🌍 LANGUAGE SWITCH -->
<div class="lang-switch">
    <a href="?lang=en" class="<?= $lang == 'en' ? 'active' : '' ?>">🇺🇸 EN</a>
    <a href="?lang=es" class="<?= $lang == 'es' ? 'active' : '' ?>">🇪🇸 ES</a>
</div>

<style>
.home-btn {
    position: fixed;
    top: 20px;
    left: 20px;
    background: white;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: bold;
    color: #333;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    transition: .3s;
    z-index: 99999;
}
.home-btn:hover {
    background: #f0f0f0;
    transform: translateY(-2px);
}

.lang-switch {
    position: fixed;
    top: 20px;
    right: 20px;
    display: flex;
    gap: 12px;
    z-index: 99999;
}
.lang-switch a {
    background: white;
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    color: #333;
    font-size: 0.9rem;
    font-weight: bold;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}
.lang-switch a:hover { background: #eee; }
.lang-switch .active {
    background: #a30000;
    color: white;
}
</style>

<div class="container">

<!-- 🔹 HEADER -->
<div class="form-header"
     style="text-align:center; margin-bottom:25px; padding:25px 0;
            background:#a30000; border-radius:10px;">
    <h2 style="color:white; margin:0;">
        📄 <?= $t["wr_title"] ?? "Employee Work Report" ?>
    </h2>
    <p style="color:white; margin-top:5px;">
        <?= $t["wr_subtitle"] ?? "Internal form for job documentation" ?>
    </p>
</div>

<div class="form-content">
<form id="main_form" action="enviar_correo.php" method="POST" enctype="multipart/form-data">

    <!-- ===================== -->
    <!-- SECTION 1 -->
    <!-- ===================== -->
    <div class="section-title collapsible">
        <?= $t["wr_sec1_title"] ?> <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden">
        <?php include 'form_part1_questions.php'; ?>
    </div>

    <!-- ===================== -->
    <!-- SECTION 2 -->
    <!-- ===================== -->
    <div class="section-title collapsible">
        <?= $t["wr_sec2_title"] ?> <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden">
        <?php include 'form_part2_photos.php'; ?>
    </div>

    <!-- ===================== -->
    <!-- SECTION 3 (INTERNAL ONLY) -->
    <!-- ===================== -->
    <div class="section-internal-only">
        <?php include 'form_part3_template.php'; ?>
    </div>

    <!-- ===================== -->
    <!-- SUBMIT -->
    <!-- ===================== -->
    <div class="form-actions" style="text-align:center; margin-top:25px;">
        <button type="button" id="btnPreview"
                style="padding:10px 25px; font-size:16px; font-weight:600;
                       background:#a30000; color:white; border:none;
                       border-radius:6px; cursor:pointer;">
            📧 <?= $t["send"] ?>
        </button>
    </div>

    <!-- ===================== -->
    <!-- PREVIEW MODAL -->
    <!-- ===================== -->
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
                🧾 <?= $t["preview_form"] ?>
            </h2>

            <div id="previewContent"
                 style="text-align:left; font-size:15px; line-height:1.5;">
            </div>

            <div style="text-align:center; margin-top:20px;">
                <button id="confirmSend"
                        style="background:#007bff; color:white;
                               padding:8px 18px; border:none; border-radius:6px;">
                    ✅ <?= $t["confirm_send"] ?>
                </button>
                <button id="cancelPreview"
                        style="background:#ccc;
                               padding:8px 18px; border:none; border-radius:6px;">
                    ❌ <?= $t["cancel"] ?>
                </button>
            </div>

        </div>
    </div>

</form>
</div>
</div>

<!-- ===================== -->
<!-- STYLES -->
<!-- ===================== -->
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

/* 🔒 INTERNAL ONLY */
.section-internal-only {
    display: none;
}
</style>

<!-- ===================== -->
<!-- SCRIPTS -->
<!-- ===================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".section-title").forEach(section => {
        const content = section.nextElementSibling;
        content.classList.add("hidden");
        section.classList.add("collapsed");

        section.addEventListener("click", () => {
            section.classList.toggle("collapsed");
            content.classList.toggle("hidden");
        });
    });

    document.getElementById("btnPreview").addEventListener("click", () => {
        const form = document.getElementById("main_form");
        const data = new FormData(form);

        let html = "<table style='width:100%; border-collapse:collapse;'>";
        data.forEach((value, key) => {
            if (typeof value === "string" && value.trim() !== "") {
                html += `
                <tr>
                    <td style="font-weight:bold; padding:6px; border-bottom:1px solid #ddd;">
                        ${key}
                    </td>
                    <td style="padding:6px; border-bottom:1px solid #ddd;">
                        ${value}
                    </td>
                </tr>`;
            }
        });
        html += "</table>";

        document.getElementById("previewContent").innerHTML = html;
        document.getElementById("previewModal").style.display = "flex";
    });

    document.getElementById("cancelPreview").onclick =
        () => document.getElementById("previewModal").style.display = "none";

    document.getElementById("confirmSend").onclick =
        () => document.getElementById("main_form").submit();
});
</script>

</body>
</html>
