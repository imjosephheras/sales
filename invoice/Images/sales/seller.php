<?php
// Asegurar que $t esté definido para evitar warnings
$t = $t ?? [];
?>

<!-- ================================ -->
<!-- 🧩 SECTION 1: COMMISSION FORM -->
<!-- ================================ -->

<div class="section-title">
    <?= $t["sec1_title"] ?? "Section 1: Commission Form" ?>
</div>

<!-- 1️⃣ Job / Work Order Number -->
<div class="question-block" id="cc1">
  <label for="Job_Number" class="question-label">
    1. <?= $t["q1"] ?? "Job / Work Order Number" ?>*
  </label>
  <input type="text" id="Job_Number" name="Job_Number" required 
         placeholder="<?= $t["ph_job_number"] ?? "Enter Job or Work Order number" ?>">
</div>

<!-- 2️⃣ Lead Source -->
<div class="question-block" id="cc2">
  <label for="Lead_Source" class="question-label">
    2. <?= $t["q2"] ?? "Lead Source" ?>*
  </label>

  <select id="Lead_Source" name="Lead_Source" required onchange="toggleFinderCommission()">
    <option value=""><?= $t["ph_select_lead"] ?? "-- Select Lead Source --" ?></option>
    <option value="facebook"><?= $t["lead_facebook"] ?? "Facebook Ads" ?></option>
    <option value="google"><?= $t["lead_google"] ?? "Google Ads" ?></option>
    <option value="website"><?= $t["lead_website"] ?? "Website / Chat" ?></option>
    <option value="walkin"><?= $t["lead_walkin"] ?? "Walk-in" ?></option>
    <option value="sales"><?= $t["lead_sales"] ?? "Sales Rep Lead" ?></option>
    <option value="external"><?= $t["lead_external"] ?? "External Prospector (Uber/Other)" ?></option>
    <option value="referral"><?= $t["lead_referral"] ?? "Referral" ?></option>
    <option value="existing"><?= $t["lead_existing"] ?? "Existing Client" ?></option>
  </select>
</div>

<!-- 3️⃣ Lead Finder -->
<div class="question-block" id="cc3">
  <label for="Lead_Finder_Name" class="question-label">
    3. <?= $t["q3"] ?? "Lead Finder (If external/referral)" ?>
  </label>

  <input type="text" id="Lead_Finder_Name" name="Lead_Finder_Name"
         placeholder="<?= $t["ph_lead_finder"] ?? "Who found the lead? (Optional)" ?>">
</div>

<!-- 4️⃣ Lead Closer -->
<div class="question-block" id="cc4">
  <label for="Lead_Closer" class="question-label">
    4. <?= $t["q4"] ?? "Lead Closer (Salesperson)" ?>*
  </label>

  <select name="Lead_Closer" id="Lead_Closer" required>
    <option value=""><?= $t["ph_select_salesperson"] ?? "Select salesperson" ?></option>
    <option value="Keny Howe">Keny Howe</option>
    <option value="Norma Bustos">Norma Bustos</option>
    <option value="Sandra Hernandez">Sandra Hernandez</option>
    <option value="Miguel Palma">Miguel Palma</option>
    <option value="Rafael Perez JR">Rafael Perez JR</option>
    <option value="Patty Perez">Patty Perez</option>
  </select>
</div>

<!-- 5️⃣ Order Paid -->
<div class="question-block" id="cc5">
  <label for="Order_Paid" class="question-label">
    5. <?= $t["q5"] ?? "Order Paid?" ?>*
  </label>

  <select id="Order_Paid" name="Order_Paid" required>
    <option value=""><?= $t["ph_select_option"] ?? "-- Select an option --" ?></option>
    <option value="Yes"><?= $t["yes"] ?? "Yes" ?></option>
    <option value="No"><?= $t["no"] ?? "No" ?></option>
    <option value="Partial"><?= $t["partial"] ?? "Partial" ?></option>
  </select>
</div>

<!-- 6️⃣ Bill Rate -->
<div class="question-block" id="cc6">
  <label for="Bill_Rate" class="question-label">
    6. <?= $t["q6"] ?? "Bill Rate" ?>*
  </label>

  <input type="number" id="Bill_Rate" name="Bill_Rate" required step="0.01"
         placeholder="<?= $t["ph_bill_rate"] ?? "Example: 3500.00" ?>"
         oninput="updateTotals()">
</div>

<!-- 7️⃣ Operating Cost -->
<div class="question-block" id="cc7">
  <label for="Operating_Cost" class="question-label">
    7. <?= $t["q7"] ?? "Operating Cost" ?>*
  </label>

  <input type="number" id="Operating_Cost" name="Operating_Cost" required step="0.01"
         placeholder="<?= $t["ph_operating_cost"] ?? "Example: 2100.00" ?>"
         oninput="updateTotals()">
</div>

<!-- 8️⃣ Supplies -->
<div class="question-block" id="cc8">
  <label for="Supplies" class="question-label">
    8. <?= $t["q8"] ?? "Supplies" ?>*
  </label>

  <input type="number" id="Supplies" name="Supplies" required step="0.01"
         placeholder="<?= $t["ph_supplies"] ?? "Example: 250.00" ?>"
         oninput="updateTotals()">
</div>

<!-- 9️⃣ Operational & Fixed Expenses -->
<div class="question-block" id="cc9">
  <label for="Fixed_Expenses" class="question-label">
    9. <?= $t["q9"] ?? "Operational & Fixed Expenses" ?>
  </label>

  <input type="number" id="Fixed_Expenses" name="Fixed_Expenses" readonly>
</div>

<!-- 🔟 Net Utility -->
<div class="question-block" id="cc10">
  <label for="Net_Utility" class="question-label">
    10. <?= $t["q10"] ?? "Net Utility" ?>
  </label>

  <input type="number" id="Net_Utility" name="Net_Utility" readonly>
</div>

<!-- 11️⃣ Finder Commission -->
<div class="question-block" id="cc11">
  <label class="question-label">
    11. <?= $t["q11"] ?? "Finder Commission (%)" ?>
  </label>

  <input type="range" id="Finder_Commission_Pct" name="Finder_Commission_Pct"
         min="0" max="20" step="1" value="0" disabled
         oninput="document.getElementById('Finder_Commission_Label').innerText = this.value + '%'; updateCommissions();">

  <span id="Finder_Commission_Label">0%</span>

  <input type="number" id="Finder_Commission_Amt" name="Finder_Commission_Amt" readonly
         placeholder="<?= $t["ph_finder_amount"] ?? "Finder Amount ($)" ?>">
</div>

<!-- 12️⃣ Closer Commission -->
<div class="question-block" id="cc12">
  <label class="question-label">
    12. <?= $t["q12"] ?? "Sales Closer Commission (%)" ?>
  </label>

  <input type="range" id="Closer_Commission_Pct" name="Closer_Commission_Pct"
         min="0" max="30" step="1" value="0"
         oninput="document.getElementById('Closer_Commission_Label').innerText = this.value + '%'; updateCommissions();">

  <span id="Closer_Commission_Label">0%</span>

  <input type="number" id="Closer_Commission_Amt" name="Closer_Commission_Amt" readonly
         placeholder="<?= $t["ph_closer_amount"] ?? "Closer Amount ($)" ?>">
</div>

<!-- ========================================================= -->
<!-- 🧠 SCRIPT: AUTOMATIC CALCULATIONS -->
<!-- ========================================================= -->

<script>
function toggleFinderCommission() {
    const source = document.getElementById("Lead_Source").value;
    const finderSlider = document.getElementById("Finder_Commission_Pct");

    if (source === "external" || source === "referral") {
        finderSlider.disabled = false;
    } else {
        finderSlider.disabled = true;
        finderSlider.value = 0;
        document.getElementById("Finder_Commission_Label").innerText = "0%";
        document.getElementById("Finder_Commission_Amt").value = "";
        updateCommissions();
    }
}

function updateTotals() {
    const bill = parseFloat(document.getElementById("Bill_Rate").value) || 0;
    const cost = parseFloat(document.getElementById("Operating_Cost").value) || 0;
    const supplies = parseFloat(document.getElementById("Supplies").value) || 0;

    const fixed = cost + supplies;
    document.getElementById("Fixed_Expenses").value = fixed.toFixed(2);

    const net = bill - fixed;
    document.getElementById("Net_Utility").value = net.toFixed(2);

    updateCommissions();
}

function updateCommissions() {
    const net = parseFloat(document.getElementById("Net_Utility").value) || 0;

    const finderPct = parseFloat(document.getElementById("Finder_Commission_Pct").value) || 0;
    const closerPct = parseFloat(document.getElementById("Closer_Commission_Pct").value) || 0;

    document.getElementById("Finder_Commission_Amt").value = (net * finderPct / 100).toFixed(2);
    document.getElementById("Closer_Commission_Amt").value = (net * closerPct / 100).toFixed(2);
}
</script>
