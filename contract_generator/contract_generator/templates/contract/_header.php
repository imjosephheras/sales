<?php if (empty($GLOBALS['__contract_header_rendered'])): $GLOBALS['__contract_header_rendered'] = true; ?>
<!-- HEADER - position:fixed makes DOMPDF repeat this on every page -->
<div class="header-wrapper">
    <table class="header-table">
        <tr>
            <td style="width: 40%; padding: 10px 0;">
                <?php if (!empty($logo_base64)): ?>
                <img class="header-logo" src="<?php echo $logo_base64; ?>" alt="Prime Hospitality Services">
                <?php endif; ?>
            </td>
            <td style="width: 60%; padding: 10px 0 10px 15px; text-align: left;">
                <div class="doc-title">Temporary Staff</div>
                <div class="doc-subtitle">SERVICES AGREEMENT</div>
            </td>
        </tr>
    </table>
</div>
<?php endif; ?>
