<!-- Print Footer (hidden on screen) -->
    <?php if (isset($showPrintFooter) && $showPrintFooter): ?>
        <?php component('print-footer', [
            'year' => $year ?? date('Y')
        ]); ?>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/calendar.js') ?>"></script>
    <script src="<?= asset('js/calendar-dragdrop.js') ?>"></script>
    <script src="<?= asset('js/delete-event-ui.js') ?>"></script>
    <script src="<?= asset('js/nomenclature-autofill.js') ?>"></script>
    <script src="<?= asset('js/client-filters.js') ?>"></script>
    <script src="<?= asset('js/print.js') ?>"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        // Theme toggle
        function toggleTheme() {
            const body = document.body;
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.body.setAttribute('data-theme', savedTheme);
        
        <?php if (isset($flash)): ?>
        // Auto-hide flash messages
        setTimeout(() => {
            const flashEl = document.getElementById('flashMessage');
            if (flashEl) {
                flashEl.style.opacity = '0';
                setTimeout(() => flashEl.remove(), 300);
            }
        }, 5000);
        <?php endif; ?>
    </script>
    
    <?php if (isset($inlineJS)): ?>
        <script><?= $inlineJS ?></script>
    <?php endif; ?>
    
    <?php if (ENVIRONMENT === 'development'): ?>
    <!-- Development Console Info -->
    <script>
        console.log('%c📅 Calendar System', 'color: #dc2626; font-size: 16px; font-weight: bold;');
        console.log('%cEnvironment: Development', 'color: #f59e0b;');
        console.log('User ID: <?= $currentUser['user_id'] ?? 'N/A' ?>');
        console.log('Page loaded: <?= date('Y-m-d H:i:s') ?>');
    </script>
    <?php endif; ?>
</body>
</html>