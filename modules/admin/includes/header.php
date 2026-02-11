<?php
/**
 * Shared Admin Header - Dropdown menu for admin users
 *
 * Variables expected from the including page:
 *   $page_title  (string)  e.g. "Admin Panel"
 *   $back_url    (string)  URL for the back button, or '' to hide it
 *   $back_label  (string)  Label for the back button
 */
$current_user = $_SESSION['full_name'] ?? 'Admin';
$isAdmin      = ((int)($_SESSION['role_id'] ?? 0)) === 1;
$page_title   = $page_title ?? 'Admin Panel';
$back_url     = $back_url ?? '';
$back_label   = $back_label ?? 'Home';
?>
<!-- Admin Header CSS -->
<style>
    .adm-header {
        background: rgba(0,0,0,0.3);
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }
    .adm-header-left { display: flex; align-items: center; gap: 15px; }
    .adm-header-left a.adm-back-btn {
        color: white;
        text-decoration: none;
        background: rgba(255,255,255,0.15);
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        transition: background 0.2s;
        font-size: 0.9rem;
    }
    .adm-header-left a.adm-back-btn:hover { background: rgba(255,255,255,0.25); }
    .adm-header-left h1 { font-size: 1.4rem; margin: 0; }

    /* Dropdown */
    .adm-dropdown { position: relative; }
    .adm-dropdown-btn {
        background: rgba(255,255,255,0.15);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        font-family: inherit;
        font-weight: 500;
        transition: background 0.2s;
    }
    .adm-dropdown-btn:hover { background: rgba(255,255,255,0.25); }
    .adm-dropdown-btn .chevron {
        font-size: 0.7rem;
        transition: transform 0.2s;
    }
    .adm-dropdown.open .chevron { transform: rotate(180deg); }
    .adm-dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        min-width: 230px;
        z-index: 1000;
        overflow: hidden;
    }
    .adm-dropdown.open .adm-dropdown-menu { display: block; }
    .adm-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        color: #333;
        text-decoration: none;
        font-size: 0.9rem;
        transition: background 0.15s;
    }
    .adm-dropdown-menu a:hover { background: #f0f2f5; }
    .adm-dropdown-menu a i { width: 18px; text-align: center; color: #666; }
    .adm-dropdown-divider { height: 1px; background: #eee; margin: 4px 0; }
    .adm-dropdown-menu a.logout-link { color: #dc3545; }
    .adm-dropdown-menu a.logout-link i { color: #dc3545; }
</style>

<div class="adm-header">
    <div class="adm-header-left">
        <?php if ($back_url): ?>
            <a href="<?= $back_url ?>" class="adm-back-btn"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars($back_label, ENT_QUOTES, 'UTF-8') ?></a>
        <?php else: ?>
            <a href="<?= url('/') ?>" class="adm-back-btn"><i class="fas fa-home"></i> Home</a>
        <?php endif; ?>
        <h1><i class="fas fa-cogs"></i> <?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <?php if ($isAdmin): ?>
    <div class="adm-dropdown" id="adminDropdown">
        <button class="adm-dropdown-btn" type="button" onclick="this.parentElement.classList.toggle('open')">
            <i class="fas fa-user-shield"></i>
            <span><?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?></span>
            <i class="fas fa-chevron-down chevron"></i>
        </button>
        <div class="adm-dropdown-menu">
            <a href="<?= url('/modules/admin/users/') ?>"><i class="fas fa-users-cog"></i> Gestión de Usuarios</a>
            <a href="<?= url('/modules/admin/roles/') ?>"><i class="fas fa-user-tag"></i> Gestión de Roles</a>
            <a href="<?= url('/modules/admin/permissions/') ?>"><i class="fas fa-key"></i> Gestión de Permisos</a>
            <div class="adm-dropdown-divider"></div>
            <a href="<?= url('/modules/admin/profile/') ?>"><i class="fas fa-user-edit"></i> Mi Perfil</a>
            <a href="<?= url('/public/index.php?action=logout') ?>" class="logout-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </div>
    <?php else: ?>
    <div style="display:flex;align-items:center;gap:8px;font-size:0.95rem;">
        <i class="fas fa-user-circle"></i>
        <span><?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- Close dropdown on outside click -->
<script>
document.addEventListener('click', function(e) {
    var dd = document.getElementById('adminDropdown');
    if (dd && !dd.contains(e.target)) {
        dd.classList.remove('open');
    }
});
</script>
