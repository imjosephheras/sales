<?php
/**
 * Topbar Component
 *
 * Renders the top navigation bar with page title, hamburger menu,
 * and optional right-side actions.
 *
 * Expected variables (from the including page):
 *   $page_title  (string)  Title of the current page
 *   $page_icon   (string)  Font Awesome class for the page icon (optional)
 */

$_topbar_title = $page_title ?? 'Dashboard';
$_topbar_icon  = $page_icon ?? '';
?>

<header class="db-topbar" id="dbTopbar">
    <div class="db-topbar-left">
        <button class="db-topbar-hamburger" id="dbHamburger" type="button" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="db-topbar-title">
            <?php if ($_topbar_icon): ?>
                <i class="<?= htmlspecialchars($_topbar_icon, ENT_QUOTES, 'UTF-8') ?>"></i>
            <?php endif; ?>
            <?= htmlspecialchars($_topbar_title, ENT_QUOTES, 'UTF-8') ?>
        </h1>
    </div>
    <div class="db-topbar-right">
        <span class="db-topbar-date"><?= date('M d, Y') ?></span>
    </div>
</header>
