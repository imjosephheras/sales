<?php
// Aseguramos que $t exista (por si este archivo se incluye suelto)
$t = $t ?? [];
?>

<!-- ================================ -->
<!-- 🧩 Section 1: Employee Information -->
<!-- ================================ -->

<div id="section_employee_information" class="section-title">
  <?= $t["wr_sec1_title"] ?? "Section 1: Employee Information" ?>
</div>

<!-- 1️⃣ Employee Name -->
<div class="question-block" id="employee_name_block">
  <label for="Employee_Name" class="question-label">
    1. <?= $t["wr_employee_name"] ?? "Employee Name" ?>*
  </label>
  <input 
      type="text" 
      name="Employee_Name" 
      id="Employee_Name" 
      class="form-control" 
      placeholder="<?= $t["wr_ph_employee_name"] ?? "Enter employee name" ?>"
      required>
</div>

<!-- 2️⃣ Job Work Order (JWO) -->
<div class="question-block" id="jwo_block">
  <label for="JWO_Number" class="question-label">
    2. <?= $t["wr_jwo"] ?? "Job Work Order (JWO)" ?>*
  </label>
  <input 
      type="text" 
      name="JWO_Number" 
      id="JWO_Number" 
      class="form-control" 
      placeholder="<?= $t["wr_ph_jwo"] ?? "Enter JWO number" ?>"
      required>
</div>
