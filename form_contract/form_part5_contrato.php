<!-- ========================================= -->
<!-- 📆 Section 5: Contract Information -->
<!-- ========================================= -->

<!-- 📸 Se muestra solo si Request Type = Contract -->
<div id="contractFields" style="display: none;">

  <!-- 21️⃣ Inflation Adjustment -->
  <div class="question-block" id="q21">
    <label for="inflationAdjustment" class="question-label">
      <?= ($lang=='en')
        ? "21. Inflation Adjustment / Price Increase Rate"
        : "21. Ajuste por Inflación / Tasa de Incremento de Precio"; ?>
    </label>

    <input
      type="text"
      name="inflationAdjustment"
      id="inflationAdjustment"
      placeholder="<?= ($lang=='en')
        ? 'Enter inflation adjustment or % increase'
        : 'Ingrese ajuste por inflación o % de incremento'; ?>"
    >
  </div>

  <!-- 22️⃣ Total Area -->
  <div class="question-block" id="q22">
    <label for="totalArea" class="question-label">
      <?= ($lang=='en')
        ? "22. Total Area (sq ft)"
        : "22. Área Total (pies cuadrados)"; ?>
    </label>

    <input
      type="text"
      name="totalArea"
      id="totalArea"
      placeholder="<?= ($lang=='en')
        ? 'Enter total area in square feet'
        : 'Ingrese el área total en pies cuadrados'; ?>"
    >
  </div>

  <!-- 23️⃣ Buildings Included -->
  <div class="question-block" id="q23">
    <label for="buildingsIncluded" class="question-label">
      <?= ($lang=='en')
        ? "23. Buildings Included"
        : "23. Edificios Incluidos"; ?>
    </label>

    <input
      type="text"
      name="buildingsIncluded"
      id="buildingsIncluded"
      placeholder="<?= ($lang=='en')
        ? 'List buildings covered by the contract'
        : 'Liste los edificios incluidos en el contrato'; ?>"
    >
  </div>

  <!-- 24️⃣ Start Date -->
  <div class="question-block" id="q24">
    <label for="startDateServices" class="question-label">
      <?= ($lang=='en')
        ? "24. Start Date of Services"
        : "24. Fecha de Inicio de Servicios"; ?>
    </label>

    <input
      type="date"
      name="startDateServices"
      id="startDateServices"
    >
  </div>

</div>

<!-- ====================================================== -->
<!-- 🧠 SCRIPT: Mostrar Section 5 solo cuando es "Contract" -->
<!-- ====================================================== -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const requestTypeSelect = document.getElementById("Request_Type");
  const contractFields = document.getElementById("contractFields");

  if (!requestTypeSelect || !contractFields) return;

  function toggleContractFields() {
    const isContract = requestTypeSelect.value.trim().toLowerCase() === "contract";

    contractFields.style.display = isContract ? "block" : "none";

    contractFields.querySelectorAll("input").forEach(input => {
      if (isContract) {
        input.disabled = false;
      } else {
        input.value = "";
        input.disabled = true; // 🔒 CLAVE
      }
    });
  }

  toggleContractFields();
  requestTypeSelect.addEventListener("change", toggleContractFields);
});
</script>