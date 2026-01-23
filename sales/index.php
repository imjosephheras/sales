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
.home-btn {
    position: fixed;
    top: 20px;
    left: 20px;
    background: white;
    padding: 10px 18px;
    border-radius: 12px;
    font-weight: bold;
    color: #333;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 99999;
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
    font-weight: bold;
    color: #333;
}
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
    💰 <?= $t["registration_form"] ?>
  </h2>
</div>

<div class="form-content">
<form id="main_form" action="pdf_y_enviar.php" method="POST">

<!-- ================================ -->
<!-- 🧩 SECTION 1: COMMISSION FORM -->
<!-- ================================ -->

<div class="section-title collapsible">
    <?= $t["sec1_title"] ?? "Section 1: Commission Form" ?>
    <span class="toggle-icon">▼</span>
</div>

<div class="section-content hidden">

<!-- 1️⃣ Job Number -->
<div class="question-block">
  <label class="question-label">1. Job / Work Order Number *</label>
  <input type="text" name="Job_Number" required>
</div>

<!-- 2️⃣ Lead Source -->
<div class="question-block">
  <label class="question-label">2. Lead Source *</label>
  <select name="Lead_Source" required>
    <option value="">-- Select --</option>
    <option value="facebook">Facebook Ads</option>
    <option value="google">Google Ads</option>
    <option value="website">Website / Chat</option>
    <option value="walkin">Walk-in</option>
    <option value="sales">Sales Rep Lead</option>
    <option value="referral">Referral</option>
    <option value="existing">Existing Client</option>
  </select>
</div>

<!-- 3️⃣ Lead Closer -->
<div class="question-block">
  <label class="question-label">3. Lead Closer *</label>
  <select name="Lead_Closer" required>
    <option value="">Select salesperson</option>
    <option value="Keny Howe">Keny Howe</option>
    <option value="Norma Bustos">Norma Bustos</option>
    <option value="Sandra Hernandez">Sandra Hernandez</option>
    <option value="Miguel Palma">Miguel Palma</option>
    <option value="Rafael Perez JR">Rafael Perez JR</option>
    <option value="Patty Perez">Patty Perez</option>
  </select>
</div>

<!-- 4️⃣ Order Paid -->
<div class="question-block">
  <label class="question-label">4. Order Paid? *</label>
  <select name="Order_Paid" required>
    <option value="">-- Select --</option>
    <option value="Yes">Yes</option>
    <option value="No">No</option>
    <option value="Partial">Partial</option>
  </select>
</div>

<!-- 5️⃣ Net Utility -->
<div class="question-block">
  <label class="question-label">5. Net Utility</label>
  <input type="number"
         id="Net_Utility"
         name="Net_Utility"
         step="0.01"
         oninput="updateCommissions()">
</div>

<!-- 6️⃣ Closer Commission -->
<div class="question-block">
  <label class="question-label">6. Sales Closer Commission (%)</label>

  <input type="range"
         id="Closer_Commission_Pct"
         name="Closer_Commission_Pct"
         min="0" max="30" step="1"
         value="0"
         oninput="updateCommissions()">

  <span id="Closer_Commission_Label">0%</span>

  <input type="number"
         id="Closer_Commission_Amt"
         name="Closer_Commission_Amt"
         readonly
         step="0.01"
         placeholder="Commission Amount">
</div>

</div> <!-- section-content -->

<!-- 🔹 ACTIONS -->
<div class="form-actions" style="text-align:center; margin-top:25px;">
  <button type="button" id="btnPreview"
          style="padding:10px 25px; background:#a30000; color:white;
                 border:none; border-radius:6px;">
    📧 <?= $t["send"] ?>
  </button>
</div>

<!-- 🔹 PREVIEW MODAL -->
<div id="previewModal" style="
  display:none; position:fixed; inset:0;
  background:rgba(0,0,0,.6);
  justify-content:center; align-items:center; z-index:9999;">
  <div style="background:white; padding:25px; border-radius:10px; width:90%; max-width:800px;">
    <h2 style="color:#a30000;">🧾 <?= $t["preview_form"] ?></h2>
    <div id="previewContent"></div>
    <div style="text-align:center; margin-top:20px;">
      <button id="confirmSend">✅ <?= $t["confirm_send"] ?></button>
      <button id="cancelPreview">❌ <?= $t["cancel"] ?></button>
    </div>
  </div>
</div>

</form>
</div>
</div>

<!-- =============================== -->
<!-- 🧠 SCRIPTS -->
<!-- =============================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {

  document.querySelectorAll(".section-title").forEach(title => {
    const content = title.nextElementSibling;
    content.classList.add("hidden");
    title.addEventListener("click", () => {
      content.classList.toggle("hidden");
    });
  });

  document.getElementById("btnPreview").onclick = () => {
    const data = new FormData(document.getElementById("main_form"));
    let html = "<table>";
    data.forEach((v,k)=>{ if(v) html+=`<tr><td><b>${k}</b></td><td>${v}</td></tr>`;});
    html += "</table>";
    document.getElementById("previewContent").innerHTML = html;
    document.getElementById("previewModal").style.display="flex";
  };

  document.getElementById("cancelPreview").onclick =
    () => document.getElementById("previewModal").style.display="none";

  document.getElementById("confirmSend").onclick =
    () => document.getElementById("main_form").submit();
});

// 💰 Comisión
function updateCommissions() {
    const net = parseFloat(document.getElementById("Net_Utility").value) || 0;
    const pct = parseFloat(document.getElementById("Closer_Commission_Pct").value) || 0;
    document.getElementById("Closer_Commission_Label").innerText = pct + "%";
    document.getElementById("Closer_Commission_Amt").value =
        (net * pct / 100).toFixed(2);
}
</script>

</body>
</html>
