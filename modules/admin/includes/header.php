<?php
/**
 * Shared Admin Header - Dropdown menu for admin users
 *
 * Variables expected from the including page:
 *   $page_title  (string)  e.g. "Admin Panel"
 *   $page_icon   (string)  Font Awesome class, defaults to "fas fa-cogs"
 *   $back_url    (string)  URL for the back button, or '' to hide it
 *   $back_label  (string)  Label for the back button, or '' to hide Home btn
 */
$current_user = $_SESSION['full_name'] ?? 'Admin';
$isAdmin      = ((int)($_SESSION['role_id'] ?? 0)) === 1;
$page_title   = $page_title ?? 'Admin Panel';
$page_icon    = $page_icon ?? 'fas fa-cogs';
$back_url     = $back_url ?? '';
$back_label   = $back_label ?? 'Home';

// Generate initials from user name
$nameParts = explode(' ', trim($current_user));
$initials  = strtoupper(substr($nameParts[0], 0, 1));
if (count($nameParts) > 1) {
    $initials .= strtoupper(substr(end($nameParts), 0, 1));
}
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

    /* Avatar button */
    .adm-dropdown { position: relative; }
    .adm-avatar-btn {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: 2px solid rgba(255,255,255,0.3);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-size: 0.85rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        padding: 0;
        line-height: 1;
    }
    .adm-avatar-btn:hover {
        border-color: rgba(255,255,255,0.6);
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    .adm-dropdown.open .adm-avatar-btn {
        border-color: rgba(255,255,255,0.8);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
    }

    /* Dropdown menu */
    .adm-dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.18), 0 2px 8px rgba(0,0,0,0.08);
        min-width: 240px;
        z-index: 1000;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-8px) scale(0.96);
        transform-origin: top right;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .adm-dropdown.open .adm-dropdown-menu {
        display: block;
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    /* User info header inside dropdown */
    .adm-dropdown-user {
        padding: 16px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .adm-dropdown-user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(255,255,255,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        flex-shrink: 0;
    }
    .adm-dropdown-user-info {
        overflow: hidden;
    }
    .adm-dropdown-user-name {
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .adm-dropdown-user-role {
        font-size: 0.75rem;
        opacity: 0.85;
        margin-top: 2px;
    }

    /* Menu links */
    .adm-dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 20px;
        color: #374151;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background 0.15s ease;
    }
    .adm-dropdown-menu a:hover { background: #f3f4f6; }
    .adm-dropdown-menu a i {
        width: 18px;
        text-align: center;
        color: #6b7280;
        font-size: 0.9rem;
    }
    .adm-dropdown-menu a:hover i { color: #667eea; }
    .adm-dropdown-divider { height: 1px; background: #f0f0f0; margin: 4px 0; }

    /* Logout link */
    .adm-dropdown-menu a.logout-link { color: #ef4444; }
    .adm-dropdown-menu a.logout-link i { color: #ef4444; }
    .adm-dropdown-menu a.logout-link:hover { background: #fef2f2; }
    .adm-dropdown-menu a.logout-link:hover i { color: #dc2626; }

    /* Menu section padding */
    .adm-dropdown-links { padding: 6px 0; }
</style>

<div class="adm-header">
    <div class="adm-header-left">
        <?php if ($back_url): ?>
            <a href="<?= $back_url ?>" class="adm-back-btn"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars($back_label, ENT_QUOTES, 'UTF-8') ?></a>
        <?php elseif ($back_label !== ''): ?>
            <a href="<?= url('/') ?>" class="adm-back-btn"><i class="fas fa-home"></i> Home</a>
        <?php endif; ?>
        <h1><i class="<?= htmlspecialchars($page_icon, ENT_QUOTES, 'UTF-8') ?>"></i> <?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <?php if ($isAdmin): ?>
    <div class="adm-dropdown" id="adminDropdown">
        <button class="adm-avatar-btn" type="button" onclick="this.parentElement.classList.toggle('open')" title="<?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?>
        </button>
        <div class="adm-dropdown-menu">
            <div class="adm-dropdown-user">
                <div class="adm-dropdown-user-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="adm-dropdown-user-info">
                    <div class="adm-dropdown-user-name"><?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="adm-dropdown-user-role">Administrador</div>
                </div>
            </div>
            <div class="adm-dropdown-links">
                <a href="<?= url('/modules/admin/users/') ?>"><i class="fas fa-users-cog"></i> Gestión de Usuarios</a>
                <a href="<?= url('/modules/admin/roles/') ?>"><i class="fas fa-user-tag"></i> Gestión de Roles</a>
                <a href="<?= url('/modules/admin/permissions/') ?>"><i class="fas fa-key"></i> Gestión de Permisos</a>
                <div class="adm-dropdown-divider"></div>
                <a href="<?= url('/modules/admin/profile/') ?>"><i class="fas fa-user-edit"></i> Mi Perfil</a>
                <a href="<?= url('/public/index.php?action=logout') ?>" class="logout-link"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div style="display:flex;align-items:center;gap:14px;font-size:0.95rem;">
        <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8') ?></span>
        <a href="<?= url('/public/index.php?action=logout') ?>" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:0.85rem;" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i></a>
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
