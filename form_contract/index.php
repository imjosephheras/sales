<?php
require_once __DIR__ . '/../app/bootstrap.php';
Middleware::module('contracts');
?>
<!DOCTYPE html>
<html lang="es">
<?php include 'header.php'; ?>

<?php
// LANGUAGE CONTROLLER
if (isset($_GET["lang"])) {
    $_SESSION["lang"] = $_GET["lang"];
}

$lang = $_SESSION["lang"] ?? "en";
?>
<body>

<!-- 🎯 FIXED TOP NAVIGATION BAR -->
<div class="top-navbar">
    <div class="nav-left">
        <a href="../index.php" class="nav-btn nav-home">
            🏠 <span>Home</span>
        </a>
        <button id="togglePendingPanel" class="nav-btn nav-pending">
            📋 <span><?= ($lang=='en') ? "Pending" : "Pendientes"; ?></span>
        </button>
    </div>
    
    <div class="nav-center">
        <h1 class="nav-title">
            <?= ($lang=='en') ? "Registration Form" : "Formulario de Registro"; ?>
        </h1>
    </div>
    
    <div class="nav-right">
        <button id="toggleCalculator" class="nav-btn nav-calculator">
            🧮 <span><?= ($lang=='en') ? "Calculator" : "Calculadora"; ?></span>
        </button>
        <a href="?lang=en" class="nav-btn nav-lang <?= $lang == 'en' ? 'active' : '' ?>">
            🇺🇸 EN
        </a>
        <a href="?lang=es" class="nav-btn nav-lang <?= $lang == 'es' ? 'active' : '' ?>">
            🇪🇸 ES
        </a>
    </div>
</div>

<style>
/* ========================================= */
/* TOP NAVIGATION BAR - FIXED */
/* ========================================= */
.top-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 70px;
    background: linear-gradient(135deg, #001f54 0%, #003080 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    z-index: 100000;
}

.nav-left,
.nav-right {
    display: flex;
    gap: 12px;
    align-items: center;
    flex: 1;
}

.nav-left {
    justify-content: flex-start;
}

.nav-right {
    justify-content: flex-end;
}

.nav-center {
    flex: 0 0 auto;
    text-align: center;
    padding: 0 20px;
}

.nav-title {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: white;
    letter-spacing: -0.5px;
    white-space: nowrap;
}

.nav-btn {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    padding: 10px 18px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
    backdrop-filter: blur(10px);
    white-space: nowrap;
}

.nav-btn:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.nav-btn.active {
    background: #c70734;
}

.nav-btn.active:hover {
    background: #a30000;
}

.nav-calculator.active {
    background: #28a745;
}

.nav-calculator.active:hover {
    background: #218838;
}

.nav-btn span {
    display: inline-block;
}

/* Responsive */
@media (max-width: 1024px) {
    .top-navbar {
        padding: 0 20px;
    }
    
    .nav-title {
        font-size: 18px;
    }
}

@media (max-width: 768px) {
    .top-navbar {
        padding: 0 15px;
        height: 60px;
    }
    
    .nav-title {
        font-size: 14px;
    }
    
    .nav-btn {
        padding: 8px 12px;
        font-size: 12px;
    }
    
    .nav-btn span {
        display: none;
    }
}
</style>

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
    border-radius: 50px;
    text-decoration: none;
    color: #001f54;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    z-index: 99999;
}

.home-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    background: #f0f4f8;
}

.calculator-btn {
    position: fixed;
    top: 20px;
    right: 180px;
    background: white;
    padding: 10px 14px;
    border-radius: 50px;
    border: none;
    color: #001f54;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    z-index: 99999;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.calculator-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    background: #28a745;
    color: white;
}

.calculator-btn.active {
    background: #28a745;
    color: white;
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
    padding: 10px 18px;
    border-radius: 50px;
    text-decoration: none;
    color: #001f54;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.lang-switch a:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    background: #f0f4f8;
}

.lang-switch .active {
    background: #c70734;
    color: white;
}

.lang-switch .active:hover {
    background: #a30000;
}

/* ========================================= */
/* PENDING FORMS SIDEBAR */
/* ========================================= */
.pending-forms-sidebar {
    width: 300px;
    min-width: 300px;
    background: white;
    border-right: 3px solid #001f54;
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
}

.pending-forms-sidebar.collapsed {
    width: 0;
    min-width: 0;
    border-right: none;
}

.sidebar-header {
    background: linear-gradient(135deg, #001f54 0%, #003080 100%);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.sidebar-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.sidebar-toggle {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.3s ease;
}

.sidebar-toggle:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

.pending-forms-sidebar.collapsed .sidebar-toggle span {
    transform: rotate(180deg);
}

.sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
}

.sidebar-content::-webkit-scrollbar {
    width: 8px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: #f4f7fc;
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: rgba(0, 31, 84, 0.3);
    border-radius: 4px;
}

.pending-forms-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.form-card {
    background: #f4f7fc;
    border: 2px solid #e1e8ed;
    border-radius: 12px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.form-card:hover {
    border-color: #001f54;
    background: #e6f0ff;
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,31,84,0.15);
}

.form-card.active {
    border-color: #c70734;
    background: #ffe6ec;
}

.form-card-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 8px;
}

.form-card-business {
    font-size: 13px;
    font-weight: 700;
    color: #001f54;
    max-width: 70%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.form-card-wo {
    font-size: 11px;
    font-weight: 600;
    color: #4a5568;
    background: white;
    padding: 2px 8px;
    border-radius: 8px;
    margin-bottom: 4px;
    display: inline-block;
}

.form-card-date {
    font-size: 11px;
    color: #718096;
}

.form-card-client {
    font-size: 14px;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.form-card-service {
    font-size: 12px;
    color: #718096;
    margin-bottom: 8px;
}

.form-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e1e8ed;
}

.form-card-status {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
}

.form-card-status.draft {
    background: #fef3c7;
    color: #92400e;
}

.form-card-status.pending {
    background: #fef3c7;
    color: #92400e;
}

.form-card-status.scheduled {
    background: #dbeafe;
    color: #1e40af;
}

.form-card-status.confirmed {
    background: #d1fae5;
    color: #065f46;
}

.form-card-status.in-progress {
    background: #e0e7ff;
    color: #3730a3;
}

.form-card-status.completed {
    background: #dcfce7;
    color: #166534;
}

.form-card-status.not-completed {
    background: #fee2e2;
    color: #991b1b;
}

.form-card-actions {
    display: flex;
    gap: 6px;
}

.form-card-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    padding: 4px;
    opacity: 0.6;
    transition: all 0.2s ease;
}

.form-card-btn:hover {
    opacity: 1;
    transform: scale(1.2);
}

.form-card-btn.delete {
    color: #dc3545;
}

.form-card-btn.edit {
    color: #001f54;
}

.no-forms-message {
    text-align: center;
    padding: 40px 20px;
    color: #718096;
}

.no-forms-message p {
    margin: 0;
    font-size: 14px;
}

.loading-indicator {
    text-align: center;
    padding: 40px 20px;
}

.loading-indicator .loader {
    margin: 0 auto 15px;
}

.loading-indicator p {
    color: #718096;
    font-size: 14px;
}

/* ========================================= */
/* PAGINATION */
/* ========================================= */
.pagination-wrapper {
    padding: 12px 15px;
    border-top: 2px solid #e1e8ed;
    background: #f4f7fc;
    flex-shrink: 0;
}

.pagination-info {
    text-align: center;
    font-size: 11px;
    color: #718096;
    margin-bottom: 8px;
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.pagination-btn {
    background: white;
    border: 1px solid #e1e8ed;
    color: #1a202c;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
}

.pagination-btn:hover:not(:disabled):not(.active) {
    background: #001f54;
    color: white;
    border-color: #001f54;
}

.pagination-btn.active {
    background: #001f54;
    color: white;
    border-color: #001f54;
    font-weight: 700;
    cursor: default;
}

.pagination-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-arrow {
    gap: 4px;
    padding: 4px 10px;
}

.pagination-ellipsis {
    padding: 4px;
    font-size: 12px;
    color: #718096;
    user-select: none;
}

/* ========================================= */
/* SPLIT SCREEN LAYOUT - 3 PANELS SUPPORT */
/* ========================================= */
.split-screen-wrapper {
    display: flex;
    height: 100vh;
    width: 100%;
    padding: 0;
    margin: 0;
    gap: 0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.split-screen-wrapper.calculator-visible {
    padding: 0;
}

.form-side {
    flex: 1;
    min-width: 0;
    padding: 30px 20px;
    overflow-y: auto;
    overflow-x: hidden;
    height: 100vh;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(135deg, #001f54 0%, #003080 100%);
}

/* Custom scrollbar for form side */
.form-side::-webkit-scrollbar {
    width: 12px;
}

.form-side::-webkit-scrollbar-track {
    background: rgba(0, 31, 84, 0.3);
    border-radius: 10px;
}

.form-side::-webkit-scrollbar-thumb {
    background: rgba(199, 7, 52, 0.6);
    border-radius: 10px;
    border: 2px solid rgba(0, 31, 84, 0.3);
}

.form-side::-webkit-scrollbar-thumb:hover {
    background: rgba(199, 7, 52, 0.8);
}

.calculator-side {
    flex: 0 0 0%;
    max-width: 0%;
    overflow: hidden;
    background: white;
    border-left: 3px solid #001f54;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    height: 100vh;
}

/* ========================================= */
/* DYNAMIC WIDTH CALCULATIONS */
/* ========================================= */

/* Solo pendientes abierto */
.split-screen-wrapper:not(.calculator-visible) .form-side {
    flex: 1;
}

/* Solo calculadora abierta (pendientes cerrado) */
.split-screen-wrapper.calculator-visible .pending-forms-sidebar.collapsed + .form-side {
    flex: 0 0 50%;
    max-width: 50%;
}

.split-screen-wrapper.calculator-visible .pending-forms-sidebar.collapsed ~ .calculator-side {
    flex: 0 0 50%;
    max-width: 50%;
}

/* AMBOS abiertos: Pendientes (300px) + Form (flex) + Calculator (35%) */
.split-screen-wrapper.calculator-visible .pending-forms-sidebar:not(.collapsed) ~ .form-side {
    flex: 1;
    min-width: 300px;
}

.split-screen-wrapper.calculator-visible .calculator-side {
    flex: 0 0 35%;
    max-width: 35%;
    overflow-y: auto;
    overflow-x: hidden;
}

/* Custom scrollbar for calculator side */
.calculator-side::-webkit-scrollbar {
    width: 12px;
}

.calculator-side::-webkit-scrollbar-track {
    background: #f4f7fc;
    border-radius: 10px;
}

.calculator-side::-webkit-scrollbar-thumb {
    background: rgba(0, 31, 84, 0.4);
    border-radius: 10px;
    border: 2px solid #f4f7fc;
}

.calculator-side::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 31, 84, 0.6);
}

.calculator-container {
    width: 100%;
    min-height: 100vh;
    position: relative;
    padding: 60px 20px 20px;
}

.calculator-iframe {
    width: 100%;
    min-height: calc(100vh - 80px);
    border: none;
    display: block;
    background: white;
}

.calculator-close {
    position: fixed;
    top: 80px;
    right: 30px;
    background: #dc3545;
    color: white;
    border: none;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
    z-index: 100000;
}

.calculator-close:hover {
    background: #c82333;
    transform: rotate(90deg) scale(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.4);
}

/* ========================================= */
/* RESPONSIVE ADJUSTMENTS */
/* ========================================= */

/* Tablets: Stack calculator below */
@media (max-width: 1400px) {
    .split-screen-wrapper.calculator-visible .calculator-side {
        flex: 0 0 40%;
        max-width: 40%;
    }
}

/* Small tablets: All vertical */
@media (max-width: 1024px) {
    .split-screen-wrapper {
        flex-direction: column;
        height: auto;
        min-height: 100vh;
    }
    
    .pending-forms-sidebar {
        width: 100%;
        min-width: 100%;
        height: auto;
        max-height: 40vh;
        border-right: none;
        border-bottom: 3px solid #001f54;
    }
    
    .pending-forms-sidebar.collapsed {
        max-height: 0;
        border-bottom: none;
    }
    
    .form-side {
        height: auto;
        min-height: 50vh;
        flex: 1;
        max-width: 100% !important;
    }
    
    .split-screen-wrapper.calculator-visible {
        flex-direction: column;
    }
    
    .calculator-side {
        border-left: none;
        border-top: 3px solid #001f54;
        height: auto;
        min-height: 50vh;
        flex: 0 0 auto !important;
        max-width: 100% !important;
    }
    
    .calculator-close {
        position: absolute;
        top: 20px;
        right: 20px;
    }
    
    .calculator-container {
        min-height: 50vh;
        padding: 60px 15px 15px;
    }
    
    .calculator-iframe {
        min-height: calc(50vh - 80px);
    }
}

/* Mobile: Even more compact */
@media (max-width: 768px) {
    .pending-forms-sidebar {
        max-height: 30vh;
    }
    
    .sidebar-content {
        padding: 10px;
    }
    
    .form-card {
        padding: 12px;
    }
}
</style>

<!-- SPLIT SCREEN WRAPPER -->
<div class="split-screen-wrapper" id="splitScreenWrapper">
    
    <!-- PENDING FORMS SIDEBAR -->
    <div class="pending-forms-sidebar" id="pendingFormsSidebar">
        <div class="sidebar-header">
            <h3>
                📋 <?= ($lang=='en') ? "Pending Forms" : "Formularios Pendientes"; ?>
            </h3>
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                <span>◀</span>
            </button>
        </div>
        
        <div class="sidebar-content" id="pendingFormsContent">
            <div class="loading-indicator" style="display:none;">
                <div class="loader"></div>
                <p><?= ($lang=='en') ? "Loading..." : "Cargando..."; ?></p>
            </div>
            
            <div class="pending-forms-list" id="pendingFormsList">
                <!-- Formularios pendientes se cargarán aquí dinámicamente -->
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper" id="pagination-container" style="display: none;"></div>

            <div class="no-forms-message" style="display:none;">
                <p>📭 <?= ($lang=='en') ? "No pending forms" : "No hay formularios pendientes"; ?></p>
            </div>
        </div>
    </div>
    
    <!-- FORM SIDE -->
    <div class="form-side" id="formSide">

<div class="container">

<!-- 🖼️ Logo dinámico -->
<div id="dynamicLogo" style="
  display: none;
  text-align: center;
  padding: 30px 40px 20px;
  background: white;
  border-radius: 24px 24px 0 0;
">
  <img id="logoImage" src="" alt="Logo" style="
    max-width: 280px;
    height: auto;
    transition: all 0.5s ease;
  ">
</div>

<!-- 📋 Encabezado -->
<div class="form-header" id="formHeader" style="border-radius: 24px 24px 0 0;">
  <h2>
    📄 <?= ($lang=='en') ? "Registration Form" : "Formulario de Registro"; ?>
  </h2>
  <p>
    <?= ($lang=='en') 
        ? "Complete all required information" 
        : "Complete toda la información requerida"; ?>
  </p>
</div>

<!-- 📋 Contenido -->
<div class="form-content">

  <!-- 📋 FORM with enctype for photos -->
  <form id="main_form" action="enviar_correo.php" method="POST" enctype="multipart/form-data">

    <div class="section-title collapsible" data-section="1">
      <?= ($lang=='en') ? "Section 1: Request Information" : "Sección 1: Información de Solicitud"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="1">
      <?php include 'form_part1_request.php'; ?>
    </div>

    <div class="section-title collapsible" data-section="2">
      <?= ($lang=='en') ? "Section 2: Client Information" : "Sección 2: Información del Cliente"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="2">
      <?php include 'form_part2_client.php'; ?>
    </div>

    <div class="section-title collapsible" data-section="3">
      <?= ($lang=='en') ? "Section 3: Operational / Service Details" : "Sección 3: Detalles Operativos / del Servicio"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="3">
      <?php include 'form_part3_operativo.php'; ?>
    </div>

    <div class="section-title collapsible" data-section="4">
      <?= ($lang=='en') ? "Section 4: Economic Information" : "Sección 4: Información Económica"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="4">
      <?php include 'form_part4_economico.php'; ?>
    </div>

    <div class="section-title collapsible" data-section="5">
      <?= ($lang=='en') ? "Section 5: Contract Information" : "Sección 5: Información del Contrato"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="5">
      <?php include 'form_part5_contrato.php'; ?>
    </div>

    <div class="section-title collapsible" data-section="6">
      <?= ($lang=='en') ? "Section 6: Observations" : "Sección 6: Observaciones"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="6">
      <?php include 'form_part6_observaciones.php'; ?>
    </div>

    <div class="section-title collapsible" data-section="7">
      <?= ($lang=='en') ? "Section 7: Scope of Work" : "Sección 7: Alcance del Trabajo"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="7">
      <?php include 'form_part7_scope.php'; ?>
    </div>

    <!-- 📸 SECTION 8 (PHOTOS) -->
    <div class="section-title collapsible" data-section="8">
      <?= ($lang=='en') ? "Section 8: Photos" : "Sección 8: Fotos"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="8">
      <?php include 'form_part8_photo.php'; ?>
    </div>

    <!-- 📅 SECTION 9 (DATES & NOMENCLATURE) -->
    <div class="section-title collapsible" data-section="9">
      <?= ($lang=='en') ? "Section 9: Document & Work Dates" : "Sección 9: Fechas de Documento y Trabajo"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="9">
      <?php include 'form_part9_dates.php'; ?>
    </div>

    <!-- ✅ SECTION 10 (SERVICE STATUS) -->
    <div class="section-title collapsible" data-section="10">
      <?= ($lang=='en') ? "Section 10: Service Status" : "Sección 10: Estado del Servicio"; ?>
      <span class="toggle-icon">▼</span>
    </div>
    <div class="section-content hidden" data-section-content="10">
      <?php include 'form_part10_status.php'; ?>
    </div>

    <!-- 📋 Botón principal -->
    <div class="form-actions">
      <button type="button" id="btnSaveDraft" class="btn-draft">
        💾 <?= ($lang=='en') ? "Save as Draft" : "Guardar Borrador"; ?>
      </button>
      
      <button type="button" id="btnPreview" class="btn-submit">
        ✅ <?= ($lang=='en') ? "Complete" : "Completado"; ?>
      </button>
    </div>

    <!-- 🪟 Modal de previsualización -->
    <div id="previewModal" style="
      display:none;
      position:fixed;
      top:0; left:0;
      width:100%; height:100%;
      background:rgba(0,0,0,0.7);
      justify-content:center;
      align-items:center;
      z-index:9999;
      backdrop-filter: blur(4px);
    ">
      <div style="
        background:white;
        padding:35px;
        border-radius:16px;
        max-width:850px;
        width:90%;
        box-shadow:0 8px 32px rgba(0,0,0,0.3);
        overflow-y:auto;
        max-height:85vh;
      ">
        <h2 style="color:#001f54; margin-bottom:20px; font-size:24px; font-weight:700;">
          🧾 <?= ($lang=='en') ? "Form Preview" : "Previsualización del Formulario"; ?>
        </h2>

        <div id="previewContent"
             style="text-align:left; font-size:14px; line-height:1.6;">
        </div>

        <div style="text-align:center; margin-top:30px; display:flex; gap:15px; justify-content:center;">
          
          <!-- ✅ SUBMIT BUTTON -->
          <button
            type="button"
            id="btnSubmitForm"
            style="
              background: linear-gradient(135deg, #28a745 0%, #218838 100%);
              color:white;
              padding:14px 32px;
              border:none;
              border-radius:50px;
              font-size:15px;
              font-weight:600;
              cursor:pointer;
              box-shadow: 0 4px 12px rgba(40,167,69,0.3);
              transition: all 0.3s ease;
            "
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(40,167,69,0.4)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(40,167,69,0.3)'"
          >
            ✅ <?= ($lang=='en') ? "Complete" : "Completado"; ?>
          </button>

          <!-- ❌ CANCEL -->
          <button
            type="button"
            id="cancelPreview"
            style="
              background:#6c757d;
              color:white;
              padding:14px 32px;
              border:none;
              border-radius:50px;
              font-size:15px;
              font-weight:600;
              cursor:pointer;
              transition: all 0.3s ease;
            "
            onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'"
            onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)'"
          >
            ❌ <?= ($lang=='en') ? "Cancel" : "Cancelar"; ?>
          </button>
        </div>
      </div>
    </div>

  </form>
</div>
</div>

<!-- ====================================================== -->
<!-- SCRIPT DE FOTOS -->
<!-- ====================================================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const addBox = document.getElementById("add-photo-box");
    const input = document.getElementById("photo-input");
    const container = document.getElementById("photo-container");

    if (!addBox || !input || !container) return;

    let photoFiles = [];

    addBox.addEventListener("click", () => {
        input.click();
    });

    input.addEventListener("change", (event) => {
        const files = Array.from(event.target.files);

        files.forEach(file => {
            photoFiles.push(file);
            renderPhoto(file);
        });

        updateRealInput();
    });

    function renderPhoto(file) {
        const reader = new FileReader();

        reader.onload = (e) => {
            const card = document.createElement("div");
            card.style.cssText = `
                width:120px; height:160px; position:relative;
                border-radius:12px; overflow:hidden;
                box-shadow:0 4px 12px rgba(0,0,0,0.15);
                transition: all 0.3s ease;
            `;

            card.onmouseover = () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 6px 20px rgba(0,0,0,0.25)';
            };

            card.onmouseout = () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            };

            const img = document.createElement("img");
            img.src = e.target.result;
            img.style.cssText = `width:100%; height:100%; object-fit:cover;`;

            const del = document.createElement("div");
            del.textContent = "×";
            del.style.cssText = `
                position:absolute; top:8px; right:8px; width:28px; height:28px;
                background:rgba(220,53,69,0.95); color:white; font-size:20px; font-weight:bold;
                display:flex; align-items:center; justify-content:center;
                border-radius:50%; cursor:pointer;
                transition: all 0.2s ease;
            `;

            del.onmouseover = () => {
                del.style.background = '#c82333';
                del.style.transform = 'scale(1.1)';
            };

            del.onmouseout = () => {
                del.style.background = 'rgba(220,53,69,0.95)';
                del.style.transform = 'scale(1)';
            };

            del.addEventListener("click", () => {
                const index = Array.from(container.children).indexOf(card);
                photoFiles.splice(index, 1);
                card.remove();
                updateRealInput();
            });

            card.appendChild(img);
            card.appendChild(del);
            container.insertBefore(card, addBox);
        };

        reader.readAsDataURL(file);
    }

    function updateRealInput() {
        const dataTransfer = new DataTransfer();
        photoFiles.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }
});
</script>

<!-- ====================================================== -->
<!-- SCRIPT PRINCIPAL (ACCORDION AUTOMÁTICO + PREVIEW + DRAFTS) -->
<!-- ====================================================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {

  const titles = Array.from(document.querySelectorAll(".section-title"));
  const form = document.getElementById("main_form");
  let currentFormId = null; // Track if we're editing an existing form

  /* ===============================
     SIDEBAR TOGGLE
  =============================== */
    const sidebarToggle = document.getElementById("sidebarToggle");
  const togglePendingPanel = document.getElementById("togglePendingPanel");
  const sidebar = document.querySelector(".pending-forms-sidebar");
  
  // Toggle desde el botón dentro del sidebar
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
    });
  }
  
  // Toggle desde el botón de la barra superior
  if (togglePendingPanel) {
    togglePendingPanel.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      togglePendingPanel.classList.toggle("active");
    });
  }

  /* ===============================
     SELLER LOCAL STORAGE MANAGEMENT
     Guarda el vendedor en localStorage para filtrar formularios
  =============================== */
  const SELLER_STORAGE_KEY = 'prime_facility_seller';

  function getSavedSeller() {
    return localStorage.getItem(SELLER_STORAGE_KEY) || '';
  }

  function saveSeller(seller) {
    if (seller) {
      localStorage.setItem(SELLER_STORAGE_KEY, seller);
    }
  }

  // Cargar vendedor guardado al iniciar
  const sellerSelect = document.getElementById('Seller');
  if (sellerSelect) {
    const savedSeller = getSavedSeller();
    if (savedSeller) {
      sellerSelect.value = savedSeller;
      // Trigger change event to update related fields
      sellerSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Guardar vendedor cuando cambie
    sellerSelect.addEventListener('change', function() {
      if (this.value) {
        saveSeller(this.value);
      }
    });
  }

  /* ===============================
     PAGINATION STATE
  =============================== */
  let pendingCurrentPage = 1;
  const pendingItemsPerPage = 20;
  let pendingTotalItems = 0;
  let pendingTotalPages = 1;

  /* ===============================
     LOAD PENDING FORMS (ALL FORMS)
     Muestra todos los formularios pendientes con paginación
  =============================== */
  function loadPendingForms(page) {
    if (page !== undefined) {
      pendingCurrentPage = page;
    }

    const loadingIndicator = document.querySelector(".loading-indicator");
    const formsList = document.getElementById("pendingFormsList");
    const noFormsMessage = document.querySelector(".no-forms-message");
    const paginationContainer = document.getElementById("pagination-container");

    if (loadingIndicator) loadingIndicator.style.display = "block";
    if (formsList) formsList.innerHTML = "";
    if (noFormsMessage) noFormsMessage.style.display = "none";
    if (paginationContainer) paginationContainer.style.display = "none";

    // Cargar formularios pendientes con paginación
    fetch(`load_drafts_by_seller.php?page=${pendingCurrentPage}&limit=${pendingItemsPerPage}`)
      .then(response => response.json())
      .then(data => {
        if (loadingIndicator) loadingIndicator.style.display = "none";

        // Update pagination state
        if (data.pagination) {
          pendingTotalItems = data.pagination.total_count;
          pendingTotalPages = data.pagination.total_pages;
          pendingCurrentPage = data.pagination.page;
        }

        if (data.success && data.forms && data.forms.length > 0) {
          formsList.innerHTML = "";
          data.forms.forEach(form => {
            const card = createFormCard(form);
            formsList.appendChild(card);
          });
          renderPendingPagination();
        } else {
          if (noFormsMessage) {
            noFormsMessage.innerHTML = '<p>📭 No hay formularios pendientes</p>';
            noFormsMessage.style.display = "block";
          }
        }
      })
      .catch(error => {
        console.error("Error loading drafts:", error);
        if (loadingIndicator) loadingIndicator.style.display = "none";
        if (noFormsMessage) {
          noFormsMessage.innerHTML = '<p style="color:#dc3545;">❌ Error loading forms</p>';
          noFormsMessage.style.display = "block";
        }
      });
  }

  /* ===============================
     PAGINATION RENDERING
  =============================== */
  function goToPendingPage(page) {
    if (page < 1 || page > pendingTotalPages || page === pendingCurrentPage) return;
    loadPendingForms(page);

    // Scroll sidebar content to top
    const sidebarContent = document.getElementById("pendingFormsContent");
    if (sidebarContent) sidebarContent.scrollTop = 0;
  }

  function renderPendingPagination() {
    const container = document.getElementById("pagination-container");
    if (!container) return;

    // Hide pagination if only one page
    if (pendingTotalPages <= 1) {
      container.style.display = "none";
      return;
    }

    container.style.display = "block";

    const startItem = ((pendingCurrentPage - 1) * pendingItemsPerPage) + 1;
    const endItem = Math.min(pendingCurrentPage * pendingItemsPerPage, pendingTotalItems);

    // Build page numbers with ellipsis
    let pages = [];
    const maxVisible = 3;

    if (pendingTotalPages <= maxVisible + 2) {
      for (let i = 1; i <= pendingTotalPages; i++) pages.push(i);
    } else {
      pages.push(1);

      let start = Math.max(2, pendingCurrentPage - 1);
      let end = Math.min(pendingTotalPages - 1, pendingCurrentPage + 1);

      if (pendingCurrentPage <= 3) {
        start = 2;
        end = Math.min(maxVisible, pendingTotalPages - 1);
      } else if (pendingCurrentPage >= pendingTotalPages - 2) {
        start = Math.max(2, pendingTotalPages - maxVisible + 1);
        end = pendingTotalPages - 1;
      }

      if (start > 2) pages.push('...');
      for (let i = start; i <= end; i++) pages.push(i);
      if (end < pendingTotalPages - 1) pages.push('...');

      pages.push(pendingTotalPages);
    }

    const pageButtons = pages.map(p => {
      if (p === '...') {
        return '<span class="pagination-ellipsis">...</span>';
      }
      const activeClass = p === pendingCurrentPage ? ' active' : '';
      return '<button class="pagination-btn' + activeClass + '" data-page="' + p + '">' + p + '</button>';
    }).join('');

    container.innerHTML =
      '<div class="pagination-info">' +
        startItem + '-' + endItem + ' of ' + pendingTotalItems + ' forms' +
      '</div>' +
      '<div class="pagination-controls">' +
        '<button class="pagination-btn pagination-arrow" data-page="' + (pendingCurrentPage - 1) + '"' + (pendingCurrentPage === 1 ? ' disabled' : '') + '>◀ Prev</button>' +
        pageButtons +
        '<button class="pagination-btn pagination-arrow" data-page="' + (pendingCurrentPage + 1) + '"' + (pendingCurrentPage === pendingTotalPages ? ' disabled' : '') + '>Next ▶</button>' +
      '</div>';

    // Event delegation for pagination buttons
    container.querySelectorAll('.pagination-btn[data-page]').forEach(btn => {
      btn.addEventListener('click', function() {
        const page = parseInt(this.dataset.page);
        if (!isNaN(page)) goToPendingPage(page);
      });
    });
  }

  /* ===============================
     CREATE FORM CARD
     Muestra el estado del servicio (Sección 10)
  =============================== */
  function createFormCard(formData) {
    const card = document.createElement("div");
    card.className = "form-card";
    card.dataset.formId = formData.form_id;

    const date = new Date(formData.created_at);
    const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

    // Determinar el estado del servicio
    const serviceStatus = formData.service_status || formData.status || 'pending';
    let statusClass = 'pending';
    let statusText = 'PENDING';
    let statusIcon = '⏳';

    if (serviceStatus === 'scheduled') {
      statusClass = 'scheduled';
      statusText = 'SCHEDULED';
      statusIcon = '📅';
    } else if (serviceStatus === 'confirmed') {
      statusClass = 'confirmed';
      statusText = 'CONFIRMED';
      statusIcon = '✅';
    } else if (serviceStatus === 'in_progress') {
      statusClass = 'in-progress';
      statusText = 'IN PROGRESS';
      statusIcon = '🔄';
    } else if (serviceStatus === 'completed') {
      statusClass = 'completed';
      statusText = 'COMPLETED';
      statusIcon = '✔️';
    } else if (serviceStatus === 'not_completed') {
      statusClass = 'not-completed';
      statusText = 'NOT COMPLETED';
      statusIcon = '❌';
    }

    card.innerHTML = `
      <div class="form-card-header">
        <div class="form-card-business">${formData.company_name || 'No Business'}</div>
        <div class="form-card-date">${formattedDate}</div>
      </div>
      <div class="form-card-wo">WO #${formData.order_number || '---'}</div>
      <div class="form-card-client">${formData.client_name || 'No Client'}</div>
      <div class="form-card-service">${formData.requested_service || 'No Service'}</div>
      <div class="form-card-footer">
        <span class="form-card-status ${statusClass}" title="Service Status">
          ${statusIcon} ${statusText}
        </span>
        <div class="form-card-actions">
          <button class="form-card-btn edit" title="Edit" onclick="loadFormData(${formData.form_id})">✏️</button>
          <button class="form-card-btn delete" title="Delete" onclick="deleteDraft(${formData.form_id})">🗑️</button>
        </div>
      </div>
    `;

    card.addEventListener("click", (e) => {
      if (!e.target.closest('.form-card-actions')) {
        loadFormData(formData.form_id);
      }
    });

    return card;
  }

  /* ===============================
     POPULATE FORM - FUNCIÓN PRINCIPAL
     Rellena todos los campos del formulario con datos cargados
  =============================== */
  window.populateForm = function(formData, additionalData = {}) {
    console.log('📝 Populating form with data:', formData);

    // Reset form first
    form.reset();

    // Populate simple form fields
    Object.keys(formData).forEach(key => {
      const field = document.getElementById(key) ||
                    document.querySelector(`[name="${key}"]`) ||
                    document.querySelector(`input[name="${key}"]`) ||
                    document.querySelector(`select[name="${key}"]`) ||
                    document.querySelector(`textarea[name="${key}"]`);

      if (field && formData[key] !== null && formData[key] !== undefined) {
        if (field.type === 'checkbox') {
          field.checked = formData[key] == '1' || formData[key] === 'true' || formData[key] === true;
        } else if (field.type === 'radio') {
          const radio = document.querySelector(`input[name="${key}"][value="${formData[key]}"]`);
          if (radio) radio.checked = true;
        } else {
          field.value = formData[key];
        }

        // Trigger change event for fields with dynamic behavior
        field.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });

    // Handle scope_tasks if present
    if (additionalData.scope_tasks && additionalData.scope_tasks.length > 0) {
      console.log('📋 Loading scope tasks:', additionalData.scope_tasks);
      populateScopeTasks(additionalData.scope_tasks);
    }

    // Handle janitorial_costs if present
    if (additionalData.janitorial_costs && additionalData.janitorial_costs.length > 0) {
      console.log('🧹 Loading janitorial costs:', additionalData.janitorial_costs);
      populateJanitorialCosts(additionalData.janitorial_costs);
    }

    // Handle kitchen_costs and hood_costs if present
    if ((additionalData.kitchen_costs && additionalData.kitchen_costs.length > 0) ||
        (additionalData.hood_costs && additionalData.hood_costs.length > 0)) {
      console.log('🍳 Loading kitchen/hood costs:', {
        kitchen: additionalData.kitchen_costs,
        hood: additionalData.hood_costs
      });
      populateKitchenCosts(additionalData.kitchen_costs || [], additionalData.hood_costs || []);
    }

    // Handle photos if present
    if (additionalData.photos && additionalData.photos.length > 0) {
      console.log('📸 Loading photos:', additionalData.photos);
      // If you have a specific function to handle photos, call it here
      // populatePhotos(additionalData.photos);
    }

    // Handle service status (Section 10)
    if (formData.service_status || formData.status) {
      const serviceStatus = formData.service_status || formData.status || 'pending';
      console.log('✅ Loading service status:', serviceStatus);
      const statusSelect = document.getElementById('service_status');
      if (statusSelect) {
        statusSelect.value = serviceStatus;
      }
    }

    console.log('✅ Form populated successfully');
  };

  /* ===============================
     POPULATE SCOPE OF WORK (Q28)
     =============================== */
  window.populateScopeTasks = function(tasks) {
    console.log('📋 Populating Scope of Work (Q28) with tasks:', tasks);

    // Check if tasks array is valid
    if (!tasks || tasks.length === 0) {
      console.log('No scope tasks to populate');
      return;
    }

    // Check if Requested_Service is already set
    const serviceSelect = document.getElementById('Requested_Service');
    if (!serviceSelect || !serviceSelect.value) {
      console.warn('Requested_Service not set yet, scope tasks cannot be populated');
      return;
    }

    // Wait for checkboxes to be generated dynamically
    // The change event on Requested_Service generates the checkboxes
    setTimeout(() => {
      const scopeContainer = document.getElementById('scopeOfWorkContainer');
      if (!scopeContainer) {
        console.error('scopeOfWorkContainer not found');
        return;
      }

      // Find all checkboxes in the container
      const checkboxes = scopeContainer.querySelectorAll('input[type="checkbox"][name="Scope_Of_Work[]"]');

      if (checkboxes.length === 0) {
        console.warn('No checkboxes found in scopeOfWorkContainer. Service may not have scope options.');
        return;
      }

      // Mark checkboxes that match the saved tasks
      let markedCount = 0;
      checkboxes.forEach(checkbox => {
        if (tasks.includes(checkbox.value)) {
          checkbox.checked = true;
          markedCount++;
        }
      });

      console.log(`✅ Scope of Work populated: ${markedCount} of ${tasks.length} tasks marked`);
    }, 150); // Small delay to ensure checkboxes are generated
  };

  /* ===============================
     POPULATE JANITORIAL COSTS (Q18)
     =============================== */
  window.populateJanitorialCosts = function(costs) {
    console.log('Populating Janitorial Services (Q18) with data:', costs);

    // Set includeJanitorial to "Yes"
    const includeJanitorial = document.getElementById('includeJanitorial');
    if (includeJanitorial) {
      includeJanitorial.value = 'Yes';
      includeJanitorial.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Show the section
    const section18Container = document.getElementById('section18Container');
    if (section18Container) {
      section18Container.style.display = 'block';
    }

    // Get the tbody
    const tbody = document.getElementById('table18body');
    if (!tbody) {
      console.error('table18body not found');
      return;
    }

    // Clear existing rows
    tbody.innerHTML = '';

    // Add rows with data (using new modal-based selector)
    costs.forEach((cost, index) => {
      const serviceType = cost.service_type || '';
      const bundleGroup = cost.bundle_group || '';

      // Look up scope from janitorial catalog if available
      let scopeJson = '[]';
      if (typeof janitorialCatalogByName !== 'undefined' && janitorialCatalogByName[serviceType]) {
        scopeJson = JSON.stringify(janitorialCatalogByName[serviceType].scope || []);
      }

      const hasValue = serviceType ? ' has-value' : '';
      const displayText = serviceType || 'Select Service...';
      const textColor = serviceType ? 'color: #001f54; font-weight: 600;' : '';

      const newRow = document.createElement('tr');
      newRow.innerHTML = `
        <td class="td-check">
          <input type="checkbox" class="bundle-check18">
          <input type="hidden" class="bundleGroup18" name="bundleGroup18[]" value="${bundleGroup}">
        </td>
        <td>
          <input type="hidden" class="type18" name="type18[]" value="${serviceType}">
          <input type="hidden" class="scope18" name="scope18[]" value='${scopeJson.replace(/'/g, "&#39;")}'>
          <div class="janitorial-selector-btn${hasValue}" onclick="openJanitorialModal(this)">
            <span class="janitorial-selector-text" style="${textColor}">${serviceType || 'Select Service...'}</span>
            <span class="janitorial-selector-icon">&#9662;</span>
          </div>
        </td>
        <td>
          <select class="time18" name="time18[]">
            <option value="">-- Select Time --</option>
            <option>1 Day</option>
            <option>1-2 Days</option>
            <option>3 Days</option>
            <option>4 Days</option>
            <option>5 Days</option>
            <option>6 Days</option>
            <option>7 Days</option>
          </select>
        </td>
        <td>
          <select class="freq18" name="freq18[]">
            <option value="">-- Select Period --</option>
            <option>One Time</option>
            <option>Weekly</option>
            <option>Every 2 Weeks</option>
            <option>Every 3 Weeks</option>
            <option>Monthly</option>
            <option>Bimonthly</option>
            <option>Quarterly</option>
            <option>Every 4 Months</option>
            <option>Semiannual</option>
            <option>Annual</option>
          </select>
        </td>
        <td>
          <input type="text" class="desc18" name="desc18[]" placeholder="Write description...">
        </td>
        <td class="subtotal-cell18">
          <input type="number" step="0.01" class="subtotal18" name="subtotal18[]" placeholder="0.00" oninput="calcTotals18()">
        </td>
      `;
      tbody.appendChild(newRow);

      // Set the remaining values
      const timeSelect = newRow.querySelector('.time18');
      const freqSelect = newRow.querySelector('.freq18');
      const descInput = newRow.querySelector('.desc18');
      const subtotalInput = newRow.querySelector('.subtotal18');

      if (timeSelect) timeSelect.value = cost.service_time || '';
      if (freqSelect) freqSelect.value = cost.frequency || '';
      if (descInput) descInput.value = cost.description || '';
      if (subtotalInput) subtotalInput.value = cost.subtotal || '';
    });

    // Apply bundle visuals for grouped rows
    if (typeof applyBundleVisuals18 === 'function') {
      applyBundleVisuals18();
    }

    // Recalculate totals
    if (typeof calcTotals18 === 'function') {
      calcTotals18();
    }

    console.log('Janitorial costs populated successfully');
  };

  /* ===============================
     POPULATE KITCHEN COSTS (Q19)
     =============================== */
  window.populateKitchenCosts = function(kitchenCosts, hoodCosts) {
    console.log('Populating Kitchen & Hood Services (Q19)');

    // Combine both arrays
    const allCosts = [...kitchenCosts, ...hoodCosts];

    if (allCosts.length === 0) {
      console.log('No kitchen/hood costs to populate');
      return;
    }

    // Set includeKitchen to "Yes"
    const includeKitchen = document.getElementById('includeKitchen');
    if (includeKitchen) {
      includeKitchen.value = 'Yes';
      includeKitchen.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Show the section
    const section19Container = document.getElementById('section19Container');
    if (section19Container) {
      section19Container.style.display = 'block';
    }

    // Get the tbody
    const tbody = document.getElementById('table19body');
    if (!tbody) {
      console.error('table19body not found');
      return;
    }

    // Clear existing rows
    tbody.innerHTML = '';

    // Add rows with data (using modal-based selector, same as create mode)
    allCosts.forEach((cost, index) => {
      const serviceType = cost.service_type || '';
      const bundleGroup = cost.bundle_group || '';

      // Look up scope from services catalog if available
      let scopeJson = '[]';
      if (typeof servicesCatalogByName !== 'undefined' && servicesCatalogByName[serviceType]) {
        scopeJson = JSON.stringify(servicesCatalogByName[serviceType].scope || []);
      }

      const hasValue = serviceType ? ' has-value' : '';
      const displayText = serviceType || 'Select Service...';
      const textColor = serviceType ? 'color: #001f54; font-weight: 600;' : '';

      const newRow = document.createElement('tr');
      newRow.innerHTML = `
        <td class="td-check">
          <input type="checkbox" class="bundle-check19">
          <input type="hidden" class="bundleGroup19" name="bundleGroup19[]" value="${bundleGroup}">
        </td>
        <td>
          <input type="hidden" class="type19" name="type19[]" value="${serviceType}">
          <input type="hidden" class="scope19" name="scope19[]" value='${scopeJson.replace(/'/g, "&#39;")}'>
          <div class="service-selector-btn${hasValue}" onclick="openServiceModal(this)">
            <span class="service-selector-text" style="${textColor}">${displayText}</span>
            <span class="service-selector-icon">&#9662;</span>
          </div>
        </td>
        <td>
          <select class="time19" name="time19[]">
            <option value="">-- Select Time --</option>
            <option>1 Day</option>
            <option>1-2 Days</option>
            <option>3 Days</option>
            <option>4 Days</option>
            <option>5 Days</option>
            <option>6 Days</option>
            <option>7 Days</option>
          </select>
        </td>
        <td>
          <select class="freq19" name="freq19[]">
            <option value="">-- Select Period --</option>
            <option>One Time</option>
            <option>Weekly</option>
            <option>Every 2 Weeks</option>
            <option>Every 3 Weeks</option>
            <option>Monthly</option>
            <option>Bimonthly</option>
            <option>Quarterly</option>
            <option>Every 4 Months</option>
            <option>Semiannual</option>
            <option>Annual</option>
          </select>
        </td>
        <td>
          <input type="text" class="desc19" name="desc19[]" placeholder="Write description...">
        </td>
        <td class="subtotal-cell19">
          <input type="number" step="0.01" class="subtotal19" name="subtotal19[]" placeholder="0.00" oninput="calcTotals19()">
        </td>
      `;
      tbody.appendChild(newRow);

      // Set the remaining values
      const timeSelect = newRow.querySelector('.time19');
      const freqSelect = newRow.querySelector('.freq19');
      const descInput = newRow.querySelector('.desc19');
      const subtotalInput = newRow.querySelector('.subtotal19');

      if (timeSelect) timeSelect.value = cost.service_time || '';
      if (freqSelect) freqSelect.value = cost.frequency || '';
      if (descInput) descInput.value = cost.description || '';
      if (subtotalInput) subtotalInput.value = cost.subtotal || '';
    });

    // Apply bundle visuals for grouped rows
    if (typeof applyBundleVisuals19 === 'function') {
      applyBundleVisuals19();
    }

    // Recalculate totals
    if (typeof calcTotals19 === 'function') {
      calcTotals19();
    }

    console.log('Kitchen/Hood costs populated successfully');
  };

/* ===============================
   LOAD FORM DATA - VERSIÓN CORREGIDA
   ⚠️ ESTA ES LA VERSIÓN QUE DEBES USAR
=============================== */
window.loadFormData = function(formId) {
  console.log('🔄 Loading form ID:', formId);
  
  fetch(`load_form_data.php?form_id=${formId}`)
    .then(response => response.json())
    .then(data => {
      console.log('📦 Data received from server:', data);
      
      if (!data.success) {
        alert('Error loading form: ' + (data.message || 'Unknown error'));
        return;
      }

      currentFormId = formId;

      // ✅ CRITICAL: Pasar datos como SEGUNDO parámetro
      populateForm(data.form, {
        scope_tasks: data.scope_tasks || [],
        janitorial_costs: data.janitorial_costs || [],
        kitchen_costs: data.kitchen_costs || [],
        hood_costs: data.hood_costs || [],
        photos: data.photos || []
      });

      // Mark card as active
      document.querySelectorAll('.form-card').forEach(card =>
        card.classList.remove('active')
      );
      document.querySelector(`[data-form-id="${formId}"]`)?.classList.add('active');

      window.scrollTo({ top: 0, behavior: 'smooth' });
    })
    .catch(err => {
      console.error('❌ Error:', err);
      alert('Error loading form data');
    });
};


/* ===============================
   LOAD FORM DATA - VERSIÓN ACTUALIZADA
   Ahora pasa los datos adicionales a populateForm
=============================== */

  /* ===============================
     DELETE DRAFT
  =============================== */
  window.deleteDraft = function(formId) {
    if (!confirm('¿Está seguro de eliminar este borrador?')) return;
    
    fetch('delete_draft.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ form_id: formId })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadPendingForms();
        if (currentFormId === formId) {
          form.reset();
          currentFormId = null;
        }
      } else {
        alert('Error deleting draft: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error("Error:", error);
      alert('Error deleting draft');
    });
  };

  /* ===============================
     SAVE AS DRAFT
  =============================== */
  document.getElementById("btnSaveDraft")?.addEventListener("click", () => {
    const formData = new FormData(form);
    if (currentFormId) {
      formData.append('form_id', currentFormId);
    }
    formData.append('status', 'draft');
    
    fetch("save_draft.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        currentFormId = data.form_id;
        alert('✅ Borrador guardado correctamente');
        loadPendingForms();
      } else {
        alert('❌ Error: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error("Error:", error);
      alert('❌ Error guardando el borrador');
    });
  });

  // Load pending forms on page load
  loadPendingForms();

  /* ===============================
     ESTADO INICIAL (TODO CERRADO)
  =============================== */
  titles.forEach(title => {
    title.classList.add("collapsed");
    title.nextElementSibling.classList.add("hidden");
  });

  /* ===============================
     ABRIR SECCIÓN 1 AL INICIO
  =============================== */
  if (titles[0]) {
    titles[0].classList.remove("collapsed");
    titles[0].nextElementSibling.classList.remove("hidden");
  }

  /* ===============================
     🎨 DYNAMIC LOGO AND COLOR THEME
  =============================== */
  const serviceTypeSelect = document.getElementById("Service_Type");
  const dynamicLogo = document.getElementById("dynamicLogo");
  const logoImage = document.getElementById("logoImage");
  const formHeader = document.getElementById("formHeader");
  const container = document.querySelector(".container");

  function updateTheme() {
    const serviceType = serviceTypeSelect.value;

    if (serviceType === "Hospitality") {
      // 🔴 HOSPITALITY - RED THEME
      logoImage.src = "Images/Hospitality.png";
      dynamicLogo.style.display = "block";
      formHeader.style.borderRadius = "0";
      
      // Change all primary colors to red
      document.documentElement.style.setProperty('--primary-color', '#c70734');
      document.documentElement.style.setProperty('--primary-light', '#a30000');
      
      // Update header gradient
      formHeader.style.background = 'linear-gradient(135deg, #c70734 0%, #a30000 100%)';
      
      // Update all section titles
      document.querySelectorAll('.section-title').forEach(title => {
        title.style.background = 'linear-gradient(135deg, #c70734 0%, #a30000 100%)';
      });

      // Update button colors
      const submitBtn = document.getElementById('btnPreview');
      if (submitBtn) {
        submitBtn.style.background = 'linear-gradient(135deg, #c70734 0%, #a30000 100%)';
      }

    } else if (serviceType === "Janitorial") {
      // 🔵 JANITORIAL - BLUE THEME
      logoImage.src = "Images/Facility.png";
      dynamicLogo.style.display = "block";
      formHeader.style.borderRadius = "0";
      
      // Restore original blue colors
      document.documentElement.style.setProperty('--primary-color', '#001f54');
      document.documentElement.style.setProperty('--primary-light', '#003080');
      
      // Restore header gradient
      formHeader.style.background = 'linear-gradient(135deg, #001f54 0%, #003080 100%)';
      
      // Restore section titles
      document.querySelectorAll('.section-title').forEach(title => {
        title.style.background = 'linear-gradient(135deg, #001f54 0%, #003080 100%)';
      });

      // Restore button colors
      const submitBtn = document.getElementById('btnPreview');
      if (submitBtn) {
        submitBtn.style.background = 'linear-gradient(135deg, #c70734 0%, #a30000 100%)';
      }

    } else {
      // No selection - hide logo
      dynamicLogo.style.display = "none";
      formHeader.style.borderRadius = "24px 24px 0 0";
    }
  }

  // Listen for changes in Service Type
  if (serviceTypeSelect) {
    serviceTypeSelect.addEventListener("change", updateTheme);
  }

  /* ===============================
     AUTO-OPEN/CLOSE LOGIC FOR SECTIONS 1-3
  =============================== */
  function setupAutoProgress() {
    // Secciones con progresión automática
    const guidedSections = [1, 2, 3];

    guidedSections.forEach((sectionNum, index) => {
      const currentTitle = document.querySelector(`[data-section="${sectionNum}"]`);
      const currentContent = document.querySelector(`[data-section-content="${sectionNum}"]`);
      
      if (!currentTitle || !currentContent) return;

      // Obtener todos los inputs requeridos de esta sección
      const requiredFields = currentContent.querySelectorAll('[required], [data-question="true"]');
      
      requiredFields.forEach(field => {
        field.addEventListener('change', () => {
          // Verificar si todos los campos requeridos de esta sección están completos
          const allFilled = Array.from(requiredFields).every(f => {
            if (f.type === 'checkbox') return f.checked;
            return f.value.trim() !== '';
          });

          // Si todos están completos, cerrar esta y abrir la siguiente
          if (allFilled && index < guidedSections.length - 1) {
            const nextSectionNum = guidedSections[index + 1];
            const nextTitle = document.querySelector(`[data-section="${nextSectionNum}"]`);
            const nextContent = document.querySelector(`[data-section-content="${nextSectionNum}"]`);

            if (nextTitle && nextContent) {
              // Cerrar sección actual
              setTimeout(() => {
                currentTitle.classList.add("collapsed");
                currentContent.classList.add("hidden");

                // Abrir siguiente sección
                nextTitle.classList.remove("collapsed");
                nextContent.classList.remove("hidden");

                // Scroll suave a la siguiente sección
                nextTitle.scrollIntoView({
                  behavior: "smooth",
                  block: "start"
                });
              }, 300);
            }
          }
        });
      });
    });
  }

  setupAutoProgress();

  /* ===============================
     TOGGLE MANUAL (TODAS LAS SECCIONES)
  =============================== */
  titles.forEach(title => {
    title.addEventListener("click", () => {
      title.classList.toggle("collapsed");
      title.nextElementSibling.classList.toggle("hidden");
    });
  });

  /* ===============================
     PREVIEW MODAL
  =============================== */
  document.getElementById("btnPreview").addEventListener("click", () => {
    const data = new FormData(form);

    let html = "<table style='width:100%; border-collapse:collapse; border:2px solid #e1e8ed; border-radius:8px; overflow:hidden;'>";

    data.forEach((value, key) => {
      if (typeof value === "string" && value.trim() !== "") {
        html += `
          <tr style="border-bottom:1px solid #e1e8ed;">
            <td style="font-weight:600; padding:12px; background:#f4f7fc; width:40%; color:#001f54;">
              ${key.replaceAll("_", " ")}
            </td>
            <td style="padding:12px; background:white;">
              ${value}
            </td>
          </tr>
        `;
      }
    });

    html += "</table>";
    document.getElementById("previewContent").innerHTML = html;
    document.getElementById("previewModal").style.display = "flex";
  });

  document.getElementById("cancelPreview").addEventListener("click", () => {
    document.getElementById("previewModal").style.display = "none";
  });

  /* ===============================
     SUBMIT BUTTON HANDLER
     Expande secciones con campos inválidos antes de validar
  =============================== */
  document.getElementById("btnSubmitForm").addEventListener("click", () => {
    // Limpia errores previos
    document.querySelectorAll(".section-title").forEach(t =>
      t.classList.remove("section-error")
    );

    // Primero, expandir TODAS las secciones que tengan campos inválidos
    const invalidFields = form.querySelectorAll(":invalid");

    invalidFields.forEach(field => {
      const sectionContent = field.closest(".section-content");
      if (sectionContent && sectionContent.classList.contains("hidden")) {
        const sectionTitle = sectionContent.previousElementSibling;
        if (sectionTitle) {
          sectionTitle.classList.remove("collapsed");
          sectionContent.classList.remove("hidden");
          sectionTitle.classList.add("section-error");
        }
      }
    });

    // Ahora validar el formulario
    if (form.checkValidity()) {
      // Formulario válido, enviar
      form.submit();
    } else {
      // Mostrar mensaje de error y enfocar el primer campo inválido
      const firstInvalid = form.querySelector(":invalid");
      if (firstInvalid) {
        const sectionContent = firstInvalid.closest(".section-content");
        if (sectionContent) {
          const sectionTitle = sectionContent.previousElementSibling;
          if (sectionTitle) {
            sectionTitle.scrollIntoView({
              behavior: "smooth",
              block: "center"
            });
          }
        }
        setTimeout(() => {
          firstInvalid.focus();
          firstInvalid.reportValidity();
        }, 300);
      }
    }
  });

  /* ===============================
     VALIDACIÓN GLOBAL
  =============================== */
  form.addEventListener("submit", (e) => {
    // Limpia errores previos
    document.querySelectorAll(".section-title").forEach(t =>
      t.classList.remove("section-error")
    );

    if (!form.checkValidity()) {
      e.preventDefault();

      const firstInvalid = form.querySelector(":invalid");

      if (firstInvalid) {
        const sectionContent = firstInvalid.closest(".section-content");

        if (sectionContent) {
          const sectionTitle = sectionContent.previousElementSibling;

          // 🔴 Marcar sección con error
          sectionTitle.classList.add("section-error");

          // 📖 Abrir sección
          sectionTitle.classList.remove("collapsed");
          sectionContent.classList.remove("hidden");

          // 🔍 Scroll
          sectionTitle.scrollIntoView({
            behavior: "smooth",
            block: "center"
          });

          // ⚠️ Focus en el campo inválido
          setTimeout(() => {
            firstInvalid.focus();
          }, 300);
        }
      }
    }
  });

});
</script>

<?php 
  include 'scripts_request.php';             
  include 'scripts_operativo.php';     
  include 'scripts_economico.php';     
?>

    </div><!-- END .form-side -->

    <!-- CALCULATOR SIDE -->
    <div class="calculator-side" id="calculatorSide">
        <div class="calculator-container">
            <button class="calculator-close" id="closeCalculator" title="Close Calculator">×</button>
            <iframe 
                id="calculatorIframe" 
                class="calculator-iframe" 
                src="../calculator/index.php"
                title="Cost Calculator">
            </iframe>
        </div>
    </div><!-- END .calculator-side -->

</div><!-- END .split-screen-wrapper -->

<!-- ====================================================== -->
<!-- CALCULATOR TOGGLE SCRIPT -->
<!-- ====================================================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("toggleCalculator");
    const closeBtn = document.getElementById("closeCalculator");
    const wrapper = document.getElementById("splitScreenWrapper");
    
    function toggleCalculator() {
        wrapper.classList.toggle("calculator-visible");
        toggleBtn.classList.toggle("active");
        
        // Scroll to top when opening calculator
        if (wrapper.classList.contains("calculator-visible")) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
    
    toggleBtn.addEventListener("click", toggleCalculator);
    closeBtn.addEventListener("click", toggleCalculator);
});
</script>

<!-- ====================================================== -->
<!-- PRINT PREPARATION: Blank fields show empty format -->
<!-- ====================================================== -->
<script>
(function() {
  const printOverlays = [];

  function preparePrintFields() {
    // Handle empty date inputs: show "  /  /    " instead of "dd/mm/aaaa"
    document.querySelectorAll('input[type="date"]').forEach(input => {
      const span = document.createElement('span');
      span.className = 'print-date-blank';
      if (input.value) {
        // Format filled date as mm/dd/yyyy
        const parts = input.value.split('-');
        span.textContent = parts[1] + '/' + parts[2] + '/' + parts[0];
      } else {
        span.textContent = '  /  /      ';
      }
      input.classList.add('print-hidden');
      input.parentNode.insertBefore(span, input.nextSibling);
      printOverlays.push({ input: input, span: span });
    });

    // Handle empty text/email/tel/number inputs
    document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="number"]').forEach(input => {
      // Skip hidden inputs and catalog-related inputs (inside #q18, #q19)
      if (input.type === 'hidden' || input.closest('#q18') || input.closest('#q19')) return;
      const span = document.createElement('span');
      span.className = 'print-value-display';
      span.textContent = input.value || ' ';
      input.classList.add('print-hidden');
      input.parentNode.insertBefore(span, input.nextSibling);
      printOverlays.push({ input: input, span: span });
    });

    // Handle empty selects
    document.querySelectorAll('select').forEach(sel => {
      if (sel.closest('#q18') || sel.closest('#q19')) return;
      const span = document.createElement('span');
      span.className = 'print-value-display';
      const selected = sel.options[sel.selectedIndex];
      span.textContent = (selected && selected.value) ? selected.text : ' ';
      sel.classList.add('print-hidden');
      sel.parentNode.insertBefore(span, sel.nextSibling);
      printOverlays.push({ input: sel, span: span });
    });

    // Handle empty textareas
    document.querySelectorAll('textarea').forEach(ta => {
      const span = document.createElement('span');
      span.className = 'print-value-display';
      span.style.whiteSpace = 'pre-wrap';
      span.textContent = ta.value || ' ';
      ta.classList.add('print-hidden');
      ta.parentNode.insertBefore(span, ta.nextSibling);
      printOverlays.push({ input: ta, span: span });
    });

    // Expand all collapsed sections for print
    document.querySelectorAll('.section-content.hidden').forEach(sec => {
      sec.classList.remove('hidden');
      sec.dataset.wasHidden = 'true';
    });
  }

  function restorePrintFields() {
    printOverlays.forEach(({ input, span }) => {
      input.classList.remove('print-hidden');
      if (span.parentNode) span.parentNode.removeChild(span);
    });
    printOverlays.length = 0;

    // Re-collapse sections that were hidden
    document.querySelectorAll('.section-content[data-was-hidden="true"]').forEach(sec => {
      sec.classList.add('hidden');
      delete sec.dataset.wasHidden;
    });
  }

  window.addEventListener('beforeprint', preparePrintFields);
  window.addEventListener('afterprint', restorePrintFields);
})();
</script>

</body>
</html>