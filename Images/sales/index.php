<!DOCTYPE html>
<html lang="<?= $lang ?>">
<?php include 'header.php'; ?>
<body>
  <?php
// 🌐 LANGUAGE CONTROLLER
if (isset($_GET["lang"])) {
    $_SESSION["lang"] = $_GET["lang"];
}
$lang = $_SESSION["lang"] ?? "en";

include 'lang.php';
$t = $translations[$lang];
?>

<!-- 🏠 HOME BUTTON -->
<a href="../index.php" class="home-btn">🏠 <?= $t["home"] ?></a>

<!-- 🌍 LANGUAGE SWITCH -->
<div class="lang-switch">
    <a href="?lang=en" class="<?= $lang == 'en' ? 'active' : '' ?>">🇺🇸 EN</a>
    <a href="?lang=es" class="<?= $lang == 'es' ? 'active' : '' ?>">🇪🇸 ES</a>
</div>

<style>
/* HOME BUTTON */
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

/* LANGUAGE SWITCH */
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
    transition: .3s;
}
.lang-switch a:hover {
    background: #eee;
}
.lang-switch .active {
    background: #a30000;
    color: white;
}
</style>

<div class="container">

<!-- 🔹 Encabezado -->
<div class="form-header" id="formHeader"
     style="text-align:center; margin-bottom:25px; padding:25px 0;
            background-color:#a30000; border-radius:10px;">
  <h2 style="font-size:26px; font-weight:700; color:white; margin:0;">
    💰 <?= $t["registration_form"] ?>
  </h2>
</div>

<!-- 🔹 Contenido -->
<div class="form-content">
  <form id="main_form" action="pdf_y_enviar.php" method="POST">

    <!-- SECTION 1 -->
<div class="section-title collapsible">
    <?= $t["sec1_title"] ?? "Section 1: Commission Form" ?>
    <span class="toggle-icon">▼</span>
</div>


    <div class="section-content hidden">
      <?php include 'seller.php'; ?>
    </div>

    <!-- 🔹 Botón principal -->
    <div class="form-actions" style="text-align:center; margin-top:25px;">
      <button type="button" id="btnPreview"
              style="padding:10px 25px; font-size:16px; font-weight:600;
                     background:#a30000; color:white; border:none;
                     border-radius:6px; cursor:pointer;">
        📧 <?= $t["send"] ?>
      </button>
    </div>

    <!-- 🪟 Modal de previsualización -->
    <div id="previewModal" style="
      display:none; position:fixed; top:0; left:0; width:100%; height:100%;
      background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:9999;
    ">
      <div style="
        background:white; padding:25px; border-radius:10px; max-width:800px; width:90%;
        box-shadow:0 4px 20px rgba(0,0,0,0.3); overflow-y:auto; max-height:90vh;
      ">
        <h2 style="color:#a30000; margin-bottom:10px;">🧾 <?= $t["preview_form"] ?></h2>
        <div id="previewContent" style="text-align:left; font-size:15px; line-height:1.5;"></div>

        <div style="text-align:center; margin-top:20px;">
          <button id="confirmSend" 
                  style="background:#007bff; color:white; padding:8px 18px; border:none; border-radius:6px;">
            ✅ <?= $t["confirm_send"] ?>
          </button>
          <button id="cancelPreview" 
                  style="background:#ccc; padding:8px 18px; border:none; border-radius:6px;">
            ❌ <?= $t["cancel"] ?>
          </button>
        </div>
      </div>
    </div>

  </form>
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
  user-select: none;
  transition: background 0.3s ease;
}
.section-title:hover { background-color: #003080; }
.toggle-icon { transition: transform 0.3s ease; }
.section-title.collapsed .toggle-icon { transform: rotate(-90deg); }
.section-content {
  border: 1px solid #ccc;
  border-top: none;
  border-radius: 0 0 8px 8px;
  padding: 15px;
  background-color: #f9f9f9;
}
.section-content.hidden { display: none; }
</style>


<!-- ====================================================== -->
<!-- Scripts -->
<!-- ====================================================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {

  // 🔹 Secciones plegables
  const sections = document.querySelectorAll(".section-title");
  sections.forEach(section => {
    const content = section.nextElementSibling;
    content.classList.add("hidden");
    section.classList.add("collapsed");

    section.addEventListener("click", () => {
      section.classList.toggle("collapsed");
      content.classList.toggle("hidden");
    });
  });

  // 🔹 Previsualizar
  document.getElementById("btnPreview").addEventListener("click", () => {
    const form = document.getElementById("main_form");
    const data = new FormData(form);

    let html = "<table style='width:100%; border-collapse:collapse;'>";

    data.forEach((value, key) => {
      if (value.trim() !== "")
        html += `
        <tr>
          <td style='font-weight:bold; padding:6px; border-bottom:1px solid #ddd;'>
            ${key.replaceAll("_", " ")}
          </td>
          <td style='padding:6px; border-bottom:1px solid #ddd;'>${value}</td>
        </tr>`;
    });

    html += "</table>";

    document.getElementById("previewContent").innerHTML = html;
    document.getElementById("previewModal").style.display = "flex";
  });

  // 🔹 Cerrar modal
  document.getElementById("cancelPreview").addEventListener("click", () => {
    document.getElementById("previewModal").style.display = "none";
  });

  // 🔹 Confirmar envío
  document.getElementById("confirmSend").addEventListener("click", () => {
    document.getElementById("main_form").submit();
  });

});
</script>


<!-- ======================================================= -->
<!-- ⭐ LÓGICA DE COMISIONES - FINAL ⭐ -->
<!-- ======================================================= -->
<script>
// =======================================
// 💰 CALCULAR BASE, FIJOS Y NET UTILITY
// =======================================
function calculateCommissionForm() {
    const billRate       = parseFloat(document.getElementById("Bill_Rate")?.value) || 0;
    const operatingCost  = parseFloat(document.getElementById("Operating_Cost")?.value) || 0;
    const supplies       = parseFloat(document.getElementById("Supplies")?.value) || 0;
    const commissionPct  = parseFloat(document.getElementById("Commission_Percentage")?.value) || 0;

    const base = billRate - operatingCost - supplies;

    const fixed = base * 0.44;
    document.getElementById("Fixed_Expenses").value = fixed.toFixed(2);

    const net = base * 0.66;
    document.getElementById("Net_Utility").value = net.toFixed(2);

    const commissionTotal = net * (commissionPct / 100);
    document.getElementById("Commission_Amount").value = commissionTotal.toFixed(2);

    updateCommissions();
}


// =======================================
// 💰 VISIBILIDAD Y REGLAS DE COMISIONES
// =======================================
function updateLeadVisibility() {
  const leadSource = document.getElementById("Lead_Source")?.value;

  const q3  = document.getElementById("cc3");   // Lead Finder
  const q11 = document.getElementById("cc11");  // Finder Commission
  const q12 = document.getElementById("cc12");  // Closer Commission

  const finderPctEl = document.getElementById("Finder_Commission_Pct");
  const closerPctEl = document.getElementById("Closer_Commission_Pct");

  // -----------------------------------
  // CASO 1: EL VENDEDOR TRAJO EL LEAD
  // -----------------------------------
  if (leadSource === "sales") {

    q3.style.display  = "none";
    q11.style.display = "none";

    q12.style.display = "block";

    closerPctEl.disabled = false;
    closerPctEl.max = 30;

    finderPctEl.value = 0;
    document.getElementById("Finder_Commission_Label").innerText = "0%";
  }

  // -----------------------------------
  // CASO 2: CUALQUIER OTRO LEAD
  // -----------------------------------
  else {
    q3.style.display  = "block";
    q11.style.display = "block";
    q12.style.display = "block";

    finderPctEl.disabled = true;
    closerPctEl.disabled = true;

    finderPctEl.value = 20;
    closerPctEl.value = 10;

    document.getElementById("Finder_Commission_Label").innerText = "20%";
    document.getElementById("Closer_Commission_Label").innerText = "10%";
  }

  updateCommissions();
}


// =======================================
// 💰 CALCULAR COMISIONES AUTOMÁTICAMENTE
// =======================================
function updateCommissions() {
  const netUtility = parseFloat(document.getElementById("Net_Utility")?.value) || 0;

  const finderPct = parseFloat(document.getElementById("Finder_Commission_Pct")?.value) || 0;
  const closerPct = parseFloat(document.getElementById("Closer_Commission_Pct")?.value) || 0;

  const finderAmtEl = document.getElementById("Finder_Commission_Amt");
  const closerAmtEl = document.getElementById("Closer_Commission_Amt");

  finderAmtEl.value = (netUtility * (finderPct / 100)).toFixed(2);
  closerAmtEl.value = (netUtility * (closerPct / 100)).toFixed(2);
}


// =======================================
// 🔄 EVENTOS
// =======================================
document.addEventListener("DOMContentLoaded", () => {
    calculateCommissionForm();
    updateLeadVisibility();

    ["Bill_Rate","Operating_Cost","Supplies"].forEach(id => {
        document.getElementById(id)?.addEventListener("input", calculateCommissionForm);
    });

    document.getElementById("Lead_Source")?.addEventListener("change", updateLeadVisibility);
});
</script>

</body>
</html>
