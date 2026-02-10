<!-- ==================================== -->
<!-- Section 10: Service Status -->
<!-- Simple dropdown for service status -->
<!-- ==================================== -->

<div class="question-block" id="q30">
  <label for="service_status" class="question-label">
    <?= ($lang=='en') ? "30. Service Status" : "30. Estado del Servicio"; ?>
  </label>

  <select name="service_status" id="service_status">
    <option value="pending">
      <?= ($lang=='en') ? "Pending" : "Pendiente"; ?>
    </option>
    <option value="scheduled">
      <?= ($lang=='en') ? "Scheduled" : "Programado"; ?>
    </option>
    <option value="confirmed">
      <?= ($lang=='en') ? "Confirmed" : "Confirmado"; ?>
    </option>
    <option value="in_progress">
      <?= ($lang=='en') ? "In Progress" : "En Progreso"; ?>
    </option>
    <option value="completed">
      <?= ($lang=='en') ? "Completed" : "Completado"; ?>
    </option>
    <option value="not_completed">
      <?= ($lang=='en') ? "Not Completed" : "No Completado"; ?>
    </option>
  </select>
</div>
