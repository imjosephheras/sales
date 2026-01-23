<!-- ================================ -->
<!-- 🧩 Section 1: Request Information -->
<!-- ================================ -->

<!-- 1️⃣ Service Type -->
<div class="question-block" id="q1">
  <label for="Service_Type" class="question-label">
    <?= ($lang=='en') ? "1. Service Type*" : "1. Tipo de Servicio*"; ?>
  </label>

  <select
    name="Service_Type"
    id="Service_Type"
    required
    data-question="true"
    onchange="updateOptions()"
  >
    <option value="">
      <?= ($lang=='en') ? "-- Select an option --" : "-- Selecciona una opción --"; ?>
    </option>

    <option value="Janitorial">
      <?= ($lang=='en') ? "Janitorial" : "Limpieza (Janitorial)"; ?>
    </option>

    <option value="Hospitality">
      <?= ($lang=='en') ? "Hospitality" : "Hospitalidad"; ?>
    </option>
  </select>
</div>

<!-- 2️⃣ Request Type -->
<div class="question-block" id="q2">
  <label for="Request_Type" class="question-label">
    <?= ($lang=='en') ? "2. Request Type*" : "2. Tipo de Solicitud*"; ?>
  </label>

  <select
    name="Request_Type"
    id="Request_Type"
    required
    data-question="true"
  >
    <option value="">
      <?= ($lang=='en') ? "-- Select an option --" : "-- Selecciona una opción --"; ?>
    </option>
    <!-- Options generated dynamically in scripts_request.php -->
  </select>
</div>

<!-- 3️⃣ Priority -->
<div class="question-block" id="q3">
  <label for="Priority" class="question-label">
    <?= ($lang=='en') ? "3. Priority*" : "3. Prioridad*"; ?>
  </label>

  <select
    name="Priority"
    id="Priority"
    required
    data-question="true"
  >
    <option value="">
      <?= ($lang=='en') ? "-- Select an option --" : "-- Selecciona una opción --"; ?>
    </option>

    <option value="Standard">
      <?= ($lang=='en') ? "Standard" : "Estándar"; ?>
    </option>

    <option value="Rush">
      <?= ($lang=='en') ? "Rush" : "Urgente"; ?>
    </option>
  </select>
</div>

<!-- 4️⃣ Requested Service -->
<div class="question-block" id="q4">
  <label for="Requested_Service" class="question-label">
    <?= ($lang=='en') ? "4. Requested Service*" : "4. Servicio Solicitado*"; ?>
  </label>

  <select
    name="Requested_Service"
    id="Requested_Service"
    required
    data-question="true"
    onchange="updateScopeOfWork()"
  >
    <option value="">
      <?= ($lang=='en') ? "-- Select an option --" : "-- Selecciona una opción --"; ?>
    </option>
    <!-- Options loaded dynamically depending on Service Type -->
  </select>
</div>