<?php
/**
 * CONTRACT GENERATOR - Main Interface
 * Pantalla principal con 3 columnas:
 * - Izquierda: Inbox de solicitudes pendientes
 * - Centro: Editor de formulario
 * - Derecha: Preview en tiempo real del contrato
 */

require_once 'config/db_config.php';
session_start();

// Usuario actual (temporal, luego integrar con sistema de auth)
$current_user = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Generator</title>
    
    <!-- Styles -->
    <link rel="stylesheet" href="styles/generator.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    
    <!-- 🎯 Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <i class="fas fa-file-contract"></i>
                <h1>Contract Generator</h1>
            </div>
            <div class="user-section">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($current_user); ?></span>
            </div>
        </div>
    </header>

    <!-- 🏗️ Main Layout: 3 Columnas -->
    <div class="container">

        <!-- ========================================= -->
        <!-- 📥 COLUMNA IZQUIERDA: INBOX -->
        <!-- ========================================= -->
        <aside class="inbox-panel">
            <?php include 'includes/inbox_panel.php'; ?>
        </aside>

        <!-- ========================================= -->
        <!-- ✏️ COLUMNA CENTRO: EDITOR -->
        <!-- ========================================= -->
        <main class="editor-panel">
            <?php include 'includes/editor_panel.php'; ?>
        </main>

        <!-- ========================================= -->
        <!-- 👁️ COLUMNA DERECHA: PREVIEW EN TIEMPO REAL -->
        <!-- ========================================= -->
        <aside class="preview-panel">
            <?php include 'includes/preview_panel.php'; ?>
        </aside>

    </div>

    <!-- Scripts -->
    <script src="js/inbox.js"></script>
    <script src="js/editor.js"></script>
    <script src="js/preview.js"></script>

    <!-- Auto-load request if request_id is in URL -->
    <script>
    (function() {
        // Get request_id from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const requestId = urlParams.get('request_id');

        if (requestId) {
            console.log('📋 Auto-loading request ID:', requestId);

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

</body>
</html>