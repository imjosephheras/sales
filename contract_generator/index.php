<?php
/**
 * CONTRACT GENERATOR - Main Interface
 * Pantalla principal con 3 columnas:
 * - Izquierda: Inbox de solicitudes pendientes
 * - Centro: Editor de formulario
 * - Derecha: Preview en tiempo real del contrato
 */

require_once __DIR__ . '/../app/bootstrap.php';
Middleware::module('generator');

require_once 'config/db_config.php';

$current_user = $_SESSION['full_name'] ?? 'Admin';

// Dashboard layout variables
$page_title = 'Contract Generator';
$page_icon  = 'fas fa-file-signature';
$page_slug  = 'generator';

$page_head = '<link rel="stylesheet" href="' . url('/contract_generator/styles/generator.css') . '?v=' . time() . '">'
    . '<style>
    /* Adjust for dashboard content area */
    .dashboard-body .container {
        max-width: 100%;
        height: calc(100vh - 60px);
    }
    .dashboard-body .main-header {
        display: none;
    }
    .dashboard-body .db-main { padding: 0; }
    .dashboard-body .db-content { padding: 0; }
    </style>';

ob_start();
?>

    <!-- Main Layout: 3 Columnas -->
    <div class="container">

        <!-- COLUMNA IZQUIERDA: INBOX -->
        <aside class="inbox-panel">
            <?php include 'includes/inbox_panel.php'; ?>
        </aside>

        <!-- COLUMNA CENTRO: EDITOR -->
        <main class="editor-panel">
            <?php include 'includes/editor_panel.php'; ?>
        </main>

        <!-- COLUMNA DERECHA: PREVIEW EN TIEMPO REAL -->
        <aside class="preview-panel">
            <?php include 'includes/preview_panel.php'; ?>
        </aside>

    </div>

    <!-- User role for RBAC in JS -->
    <script>
        window.__userRoleId = <?= (int)($_SESSION['role_id'] ?? 0) ?>;
    </script>

    <!-- Scripts -->
    <script src="<?= url('/contract_generator/js/inbox.js') ?>?v=<?php echo time(); ?>"></script>
    <script src="<?= url('/contract_generator/js/editor.js') ?>?v=<?php echo time(); ?>"></script>
    <script src="<?= url('/contract_generator/js/preview.js') ?>?v=<?php echo time(); ?>"></script>

    <!-- Auto-load request if request_id is in URL -->
    <script>
    (function() {
        // Get request_id from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const requestId = urlParams.get('request_id');

        if (requestId) {
            console.log('Auto-loading request ID:', requestId);

            // Wait for the page to fully load
            window.addEventListener('load', function() {
                // Wait a bit for modules to initialize
                setTimeout(function() {
                    // Dispatch event to load this request
                    const event = new CustomEvent('requestSelected', {
                        detail: { id: requestId }
                    });
                    document.dispatchEvent(event);
                }, 500);
            });
        }
    })();
    </script>

<?php
$page_content = ob_get_clean();
include __DIR__ . '/../app/Views/layouts/dashboard.php';
?>
