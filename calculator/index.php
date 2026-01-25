<?php
// =======================================
// 📌 CAPTURA SELECCIÓN DE SERVICE TYPE
// =======================================
$serviceType = $_POST["Service_Type"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<?php include "header.php"; ?>

<body>

<div class="container-calculator">

<h2>Cost Calculator</h2>

<!-- =======================================
     🔵 FORM SELECT SERVICE TYPE
======================================= -->
<form method="POST" class="no-print">
    <div class="question-block">
        <label><strong>Service Type</strong></label>
        <select name="Service_Type" onchange="this.form.submit()">
            <option value="">Select...</option>
            <option value="hoodvent" <?= $serviceType=="hoodvent" ? "selected" : "" ?>>
                Hood Vent
            </option>
            <option value="timesheet" <?= $serviceType=="timesheet" ? "selected" : "" ?>>
                Timesheet / Kitchen Cleaning
            </option>
            <option value="subcontract" <?= $serviceType=="subcontract" ? "selected" : "" ?>>
                Subcontractor
            </option>
        </select>
    </div>
</form>

<hr>

<!-- ===============================================================
     🔥 PHP ROUTER — CARGA FORMULARIOS
=============================================================== -->
<?php
if ($serviceType == "hoodvent") {
    include "form_hoodvent.php";
    include "form_labor.php";
    include "form_direct_costs.php";
    include "form_profit.php";
    include "form_taxes.php";
    include "form_fixed.php";
    include "form_seller.php";
    include "form_gauge.php";
}
elseif ($serviceType == "timesheet") {
    include "form_labor.php";
    include "form_direct_costs.php";
    include "form_profit.php";
    include "form_taxes.php";
    include "form_fixed.php";
    include "form_seller.php";
    include "form_gauge.php";
}
elseif ($serviceType == "subcontract") {
    include "form_subcontract.php";
    include "form_profit.php";
    include "form_taxes.php";
    include "form_fixed.php";
    include "form_seller.php";
    include "form_gauge.php";
}
?>

<!-- ===============================================================
     🪞 PRINT MIRROR – INPUTS MANUALES
     (inputs / selects escritos por usuario)
=============================================================== -->
<script>
document.addEventListener('input', function (e) {
  const span = e.target.nextElementSibling;
  if (span && span.classList.contains('print-only')) {
    span.textContent = e.target.value;
  }
});
</script>

<!-- ===============================================================
     🪞 PRINT MIRROR – LABOR TABLE (SYNC)
=============================================================== -->
<script>
function syncLaborTablePrint() {
  document.querySelectorAll(
    'select[name="labor_workers[]"],' +
    'select[name="labor_hours[]"],' +
    'select[name="labor_days[]"],' +
    'input[name="labor_rate[]"]'
  ).forEach(el => {
    const span = el.nextElementSibling;
    if (span && span.classList.contains('print-only')) {
      span.textContent = el.value;
    }
  });
}

document.addEventListener('DOMContentLoaded', syncLaborTablePrint);
window.addEventListener('beforeprint', syncLaborTablePrint);
</script>

<!-- ===============================================================
     CONTROL PRINT – LABOR TABLE
     👉 SOLO SI HAY LABOR REAL
=============================================================== -->
<script>
function shouldPrintLaborTable() {
  const laborCost =
    +document.querySelector('[name="Labor_Cost"]')?.value || 0;
  return laborCost > 0;
}

function updateLaborPrintVisibility() {
  const laborBlock = document.getElementById('q_labor_table');
  if (!laborBlock) return;

  if (shouldPrintLaborTable()) {
    laborBlock.classList.remove('no-print');
  } else {
    laborBlock.classList.add('no-print');
  }
}

window.addEventListener('beforeprint', updateLaborPrintVisibility);
document.addEventListener('DOMContentLoaded', updateLaborPrintVisibility);
</script>

<!-- ===============================================================
     CONTROL PRINT – FRINGE
     👉 SOLO SI HAY LABOR
=============================================================== -->
<script>
function updateFringePrintVisibility() {
  const labor =
    +document.querySelector('[name="Labor_Cost"]')?.value || 0;

  const fringeBlock = document.getElementById('laborExtras');
  if (!fringeBlock) return;

  if (labor > 0) {
    fringeBlock.classList.remove('no-print');
  } else {
    fringeBlock.classList.add('no-print');
  }
}

window.addEventListener('beforeprint', updateFringePrintVisibility);
document.addEventListener('DOMContentLoaded', updateFringePrintVisibility);
</script>

<!-- =========================
     BOTÓN IMPRIMIR
========================= -->
<button type="button"
        onclick="window.print()"
        class="print-btn no-print">
  🖨️ Print
</button>

</div> <!-- container-calculator -->

</body>
</html>
