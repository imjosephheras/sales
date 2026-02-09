<?php if (empty($GLOBALS['__contract_header_rendered'])): $GLOBALS['__contract_header_rendered'] = true; ?>
<!-- HEADER - position:fixed makes DOMPDF repeat this on every page -->
<div class="header-wrapper">
    <div class="header">
        <div class="header-left">
            <?php if (!empty($logo_base64)): ?>
            <img class="header-logo" src="<?php echo $logo_base64; ?>" alt="Prime Hospitality Services">
            <?php endif; ?>
        </div>
        <div class="header-right">
            <div class="doc-title">Temporary Staff</div>
            <div class="doc-subtitle">SERVICES AGREEMENT</div>
        </div>
    </div>
</div>
<?php endif; ?>
