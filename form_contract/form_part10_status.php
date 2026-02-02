<!-- ==================================== -->
<!-- ✅ Section 10: Service Status -->
<!-- Muestra el estado actual del servicio -->
<!-- ==================================== -->

<div class="question-block" id="q30">
  <label class="question-label">
    <?= ($lang=='en') ? "30. Service Status" : "30. Estado del Servicio"; ?>
  </label>

  <div class="status-display-container">
    <!-- Status Badge -->
    <div class="status-badge-wrapper" id="statusBadgeWrapper">
      <div class="status-badge pending" id="statusBadge">
        <span class="status-icon">⏳</span>
        <span class="status-text"><?= ($lang=='en') ? "PENDING" : "PENDIENTE"; ?></span>
      </div>
    </div>

    <!-- Status Info -->
    <div class="status-info" id="statusInfo">
      <p class="status-description">
        <?= ($lang=='en')
          ? "This form is awaiting confirmation from Admin Panel."
          : "Este formulario está pendiente de confirmación desde el Panel de Administración."; ?>
      </p>
    </div>

    <!-- Hidden field for status value -->
    <input type="hidden" name="service_status" id="service_status" value="pending">

    <!-- Admin Panel Link -->
    <div class="admin-panel-link">
      <p class="admin-info-text">
        <?= ($lang=='en')
          ? "To confirm or update the status of this service, use the"
          : "Para confirmar o actualizar el estado de este servicio, utilice el"; ?>
        <a href="../service_confirmation/" target="_blank" class="admin-link">
          <?= ($lang=='en') ? "Admin Panel" : "Panel de Administración"; ?>
          <span class="link-icon">↗</span>
        </a>
      </p>
    </div>
  </div>
</div>

<style>
/* ========================================= */
/* SECTION 10: SERVICE STATUS STYLES */
/* ========================================= */

.status-display-container {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-radius: 16px;
  padding: 30px;
  text-align: center;
  border: 2px solid #e2e8f0;
  margin-top: 15px;
}

.status-badge-wrapper {
  margin-bottom: 20px;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 16px 32px;
  border-radius: 50px;
  font-size: 18px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  transition: all 0.3s ease;
}

.status-badge.pending {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.status-badge.confirmed {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.status-badge.not_completed {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.status-icon {
  font-size: 24px;
}

.status-info {
  margin: 20px 0;
}

.status-description {
  color: #64748b;
  font-size: 15px;
  line-height: 1.6;
  margin: 0;
}

.admin-panel-link {
  margin-top: 25px;
  padding-top: 20px;
  border-top: 1px solid #cbd5e1;
}

.admin-info-text {
  color: #475569;
  font-size: 14px;
  margin: 0;
}

.admin-link {
  color: #001f54;
  font-weight: 600;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 12px;
  background: rgba(0, 31, 84, 0.1);
  border-radius: 20px;
  transition: all 0.3s ease;
}

.admin-link:hover {
  background: #001f54;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 31, 84, 0.3);
}

.link-icon {
  font-size: 12px;
  transition: transform 0.3s ease;
}

.admin-link:hover .link-icon {
  transform: translate(2px, -2px);
}

/* Animation for status badge */
@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.02);
  }
}

.status-badge.pending {
  animation: pulse 2s infinite;
}

.status-badge.confirmed {
  animation: none;
}

/* Responsive */
@media (max-width: 768px) {
  .status-display-container {
    padding: 20px;
  }

  .status-badge {
    padding: 12px 24px;
    font-size: 16px;
  }

  .status-icon {
    font-size: 20px;
  }
}
</style>

<script>
// Función para actualizar el estado visual basado en el valor
function updateStatusDisplay(status) {
  const badge = document.getElementById('statusBadge');
  const statusText = badge.querySelector('.status-text');
  const statusIcon = badge.querySelector('.status-icon');
  const statusInfo = document.getElementById('statusInfo');
  const statusInput = document.getElementById('service_status');

  // Actualizar valor del input
  if (statusInput) statusInput.value = status;

  // Actualizar badge según estado
  badge.className = 'status-badge ' + status;

  const lang = '<?= $lang ?>';

  switch(status) {
    case 'completed':
    case 'confirmed':
      statusIcon.textContent = '✅';
      statusText.textContent = lang === 'en' ? 'CONFIRMED' : 'CONFIRMADO';
      statusInfo.innerHTML = `<p class="status-description" style="color: #059669;">
        ${lang === 'en'
          ? 'This service has been confirmed and completed.'
          : 'Este servicio ha sido confirmado y completado.'}
      </p>`;
      break;

    case 'not_completed':
      statusIcon.textContent = '❌';
      statusText.textContent = lang === 'en' ? 'NOT COMPLETED' : 'NO COMPLETADO';
      statusInfo.innerHTML = `<p class="status-description" style="color: #dc2626;">
        ${lang === 'en'
          ? 'This service was marked as not completed.'
          : 'Este servicio fue marcado como no completado.'}
      </p>`;
      break;

    default: // pending
      statusIcon.textContent = '⏳';
      statusText.textContent = lang === 'en' ? 'PENDING' : 'PENDIENTE';
      statusInfo.innerHTML = `<p class="status-description">
        ${lang === 'en'
          ? 'This form is awaiting confirmation from Admin Panel.'
          : 'Este formulario está pendiente de confirmación desde el Panel de Administración.'}
      </p>`;
  }
}

// Exponer la función globalmente para que populateForm pueda usarla
window.updateStatusDisplay = updateStatusDisplay;
</script>
