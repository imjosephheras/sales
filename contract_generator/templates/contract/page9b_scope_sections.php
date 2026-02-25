<!-- PAGE 9B - SCOPE OF WORK SECTIONS (Auto-generated + Manual) -->
<?php
$autoScope = $data['auto_scope_sections'] ?? [];
$hasAutoScope = !empty($autoScope);
$hasManualScope = !empty($scopeSections);
?>
<?php if ($hasAutoScope || $hasManualScope): ?>
<div class="page-break"></div>

<div class="appendix-title">SCOPE OF WORK</div>
<div class="appendix-subtitle">Detailed Service Descriptions</div>

<div class="appendix-content">
    <?php // Auto-generated scope from services catalog (linked to service types) ?>
    <?php if ($hasAutoScope): ?>
    <?php foreach ($autoScope as $section): ?>
    <div class="no-break" style="margin-bottom: 18px;">
        <div class="section-number" style="margin-top: 10px;"><?php echo strtoupper(htmlspecialchars($section['title'] ?? '')); ?></div>
        <div class="subsection">
            <p><span class="subsection-label">WORK TO BE PERFORMED:</span></p>
            <ul>
            <?php foreach (explode("\n", $section['scope_content'] ?? '') as $task): ?>
                <?php if (trim($task) !== ''): ?>
                <li><?php echo htmlspecialchars(trim($task)); ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php // Manual/custom scope sections (complement auto-generated) ?>
    <?php if ($hasManualScope): ?>
    <?php foreach ($scopeSections as $section): ?>
    <div class="no-break" style="margin-bottom: 18px;">
        <div class="section-number" style="margin-top: 10px;"><?php echo strtoupper(htmlspecialchars($section['title'] ?? '')); ?></div>
        <div class="subsection">
            <p><span class="subsection-label">WORK TO BE PERFORMED:</span></p>
            <p><?php echo nl2br(htmlspecialchars($section['scope_content'] ?? '')); ?></p>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php endif; ?>
