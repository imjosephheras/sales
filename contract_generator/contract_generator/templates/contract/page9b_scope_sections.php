<!-- PAGE 9B - SCOPE OF WORK SECTIONS (Dynamic) -->
<?php if (!empty($scopeSections)): ?>
<div class="page-break"></div>

<div class="appendix-title">SCOPE OF WORK</div>
<div class="appendix-subtitle">Detailed Service Descriptions</div>

<div class="appendix-content">
    <?php foreach ($scopeSections as $section): ?>
    <div class="no-break" style="margin-bottom: 18px;">
        <div class="section-number" style="margin-top: 10px;"><?php echo strtoupper(htmlspecialchars($section['title'] ?? '')); ?></div>
        <div class="subsection">
            <p><span class="subsection-label">WORK TO BE PERFORMED:</span></p>
            <p><?php echo nl2br(htmlspecialchars($section['scope_content'] ?? '')); ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
