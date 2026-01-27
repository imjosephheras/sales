<!-- ====================================== -->
<!-- Section 9: Document & Work Dates -->
<!-- ====================================== -->

<div class="question-block" id="q30">
  <label class="question-label">
    <?= ($lang=='en')
        ? "30. Document Date *"
        : "30. Fecha del Documento *"; ?>
  </label>
  <input type="date" name="Document_Date" id="Document_Date" required
         class="form-input" onchange="generateNomenclature()">
</div>

<div class="question-block" id="q31">
  <label class="question-label">
    <?= ($lang=='en')
        ? "31. Work Date *"
        : "31. Fecha del Trabajo *"; ?>
  </label>
  <input type="date" name="Work_Date" id="Work_Date" required class="form-input">
</div>

<div class="question-block" id="q_nomenclature">
  <label class="question-label">
    <?= ($lang=='en')
        ? "Order Nomenclature (auto-generated)"
        : "Nomenclatura de Orden (auto-generada)"; ?>
  </label>
  <input type="text" name="Order_Nomenclature" id="Order_Nomenclature"
         class="form-input" readonly
         style="background: #f0f7ff; font-weight: 700; font-size: 16px; letter-spacing: 1px; color: #001f54;">
  <p class="nomenclature-hint" style="margin-top:8px; font-size:12px; color:#718096;">
    <?= ($lang=='en')
        ? "Format: [Service Type Initial][Request Type Initial]-[Order Number][MMDDYYYY]"
        : "Formato: [Inicial Tipo Servicio][Inicial Tipo Solicitud]-[Número de Orden][MMDDAAAA]"; ?>
  </p>
</div>

<script>
function generateNomenclature() {
    const serviceType = document.getElementById('Service_Type');
    const requestType = document.getElementById('Request_Type');
    const documentDate = document.getElementById('Document_Date');
    const nomenclatureField = document.getElementById('Order_Nomenclature');

    if (!serviceType || !requestType || !documentDate || !nomenclatureField) return;

    const stValue = serviceType.value || '';
    const rtValue = requestType.value || '';
    const dateValue = documentDate.value || '';

    if (!stValue || !rtValue || !dateValue) {
        nomenclatureField.value = '';
        return;
    }

    // First letter of Service Type and Request Type
    const stInitial = stValue.charAt(0).toUpperCase();
    const rtInitial = rtValue.charAt(0).toUpperCase();

    // Format date as MMDDYYYY
    const parts = dateValue.split('-'); // yyyy-mm-dd
    const dateFormatted = parts[1] + parts[2] + parts[0]; // MMDDYYYY

    // The order number will be assigned server-side, show placeholder
    nomenclatureField.value = stInitial + rtInitial + '-____' + dateFormatted;

    // Fetch next available order number from server
    fetch('get_next_order_number.php')
        .then(r => r.json())
        .then(data => {
            if (data.next_number) {
                nomenclatureField.value = stInitial + rtInitial + '-' + data.next_number + dateFormatted;
            }
        })
        .catch(() => {
            // Keep placeholder if fetch fails
        });
}

// Also regenerate when Service Type or Request Type changes
document.addEventListener('DOMContentLoaded', function() {
    const st = document.getElementById('Service_Type');
    const rt = document.getElementById('Request_Type');
    if (st) st.addEventListener('change', generateNomenclature);
    if (rt) rt.addEventListener('change', generateNomenclature);
});
</script>
