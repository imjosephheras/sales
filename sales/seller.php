<?php
$t = $t ?? [];
?>

<div class="section-title">
    <?= $t["sec1_title"] ?? "Section 1: Commission Form" ?>
</div>

<!-- 1️⃣ Job / Work Order Number -->
<div class="question-block">
  <label for="Job_Number" class="question-label">
    1. <?= $t["q1"] ?? "Job / Work Order Number" ?>*
  </label>
  <input type="text" id="Job_Number" name="Job_Number" required>
</div>

<!-- 2️⃣ Lead Source -->
<div class="question-block">
  <label for="Lead_Source" class="question-label">
    2. <?= $t["q2"] ?? "Lead Source" ?>*
  </label>

  <select id="Lead_Source" name="Lead_Source" required>
    <option value="">-- Select Lead Source --</option>
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
  <label for="Lead_Closer" class="question-label">
    3. <?= $t["q4"] ?? "Lead Closer (Salesperson)" ?>*
  </label>

  <select name="Lead_Closer" id="Lead_Closer" required>
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
  <label for="Order_Paid" class="question-label">
    4. <?= $t["q5"] ?? "Order Paid?" ?>*
  </label>

  <select id="Order_Paid" name="Order_Paid" required>
    <option value="">-- Select an option --</option>
    <option value="Yes">Yes</option>
    <option value="No">No</option>
    <option value="Partial">Partial</option>
  </select>
</div>

<!-- 5️⃣ Net Utility -->
<div class="question-block">
  <label for="Net_Utility" class="question-label">
    5. <?= $t["q10"] ?? "Net Utility" ?>
  </label>

  <input type="number"
       id="Net_Utility"
       name="Net_Utility"
       step="0.01"
       placeholder="Enter Net Utility"
       oninput="updateCommissions()">

</div>

<!-- 6️⃣ Closer Commission -->
<div class="question-block">
  <label class="question-label">
    6. <?= $t["q12"] ?? "Sales Closer Commission (%)" ?>
  </label>

  <input type="range"
       id="Closer_Commission_Pct"
       name="Closer_Commission_Pct"
       min="0"
       max="30"
       step="1"
       value="0"
       oninput="updateCommissions()">

<span id="Closer_Commission_Label">0%</span>


  <input type="number" id="Closer_Commission_Amt" name="Closer_Commission_Amt" readonly>
</div>

<!-- ========================================================= -->
<!-- 🧠 SCRIPT: AUTOMATIC CALCULATIONS -->
<!-- ========================================================= -->

<script>
function updateCommissions() {
    const net = Math.max(
        parseFloat(document.getElementById("Net_Utility").value) || 0,
        0
    );

    const pct = parseFloat(
        document.getElementById("Closer_Commission_Pct").value
    ) || 0;

    document.getElementById("Closer_Commission_Label").innerText = pct + "%";
    document.getElementById("Closer_Commission_Amt").value =
        (net * pct / 100).toFixed(2);
}
</script>

