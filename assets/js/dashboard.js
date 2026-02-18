/**
 * Dashboard Layout - Sidebar toggle and responsive behavior
 */
(function () {
    'use strict';

    var sidebar   = document.getElementById('dbSidebar');
    var hamburger = document.getElementById('dbHamburger');
    var overlay   = document.getElementById('dbOverlay');

    if (!sidebar || !hamburger) return;

    // Toggle sidebar on hamburger click
    hamburger.addEventListener('click', function () {
        sidebar.classList.toggle('open');
        if (overlay) {
            overlay.classList.toggle('visible', sidebar.classList.contains('open'));
        }
    });

    // Close sidebar on overlay click
    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('visible');
        });
    }

    // Close sidebar on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('visible');
        }
    });

    // Auto-close sidebar when resizing to desktop
    var mql = window.matchMedia('(min-width: 1025px)');
    function handleResize(e) {
        if (e.matches) {
            sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('visible');
        }
    }
    if (mql.addEventListener) {
        mql.addEventListener('change', handleResize);
    } else if (mql.addListener) {
        mql.addListener(handleResize);
    }
})();
