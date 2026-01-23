<?php $t = $t ?? []; ?>

<!-- ======================================= -->
<!-- 📄 Section 3: Service Evidence Summary  -->
<!-- ======================================= -->

<div class="section-title">
  <?= $t["wr_sec3_title"] ?? "Section 3: Service Evidence Summary" ?>
</div>

<div class="evidence-box">

    <p class="evidence-title">
        <?= $t["wr_sec3_statement_title"] ?? "📌 Service Evidence Statement" ?>
    </p>

    <p class="evidence-text">
        <?= $t["wr_sec3_p1"] ?? 
        "This report contains the photographic evidence corresponding to the service performed as part of Job Work Order" ?>
        <strong id="displayJWO">________</strong>.
    </p>

    <p class="evidence-text">
        <?= $t["wr_sec3_p2"] ??
        "The images included illustrate the condition of the work area prior to service (Before) and the resulting condition upon completion (After). These photos are intended to validate task completion, demonstrate service quality, and support the scope of work agreed with the client." ?>
    </p>

    <p class="evidence-text">
        <?= $t["wr_sec3_p3"] ??
        "This document serves as a visual record for internal review, client reporting, and quality assurance purposes." ?>
    </p>

</div>

<style>
.evidence-box {
    background: #f8f9fa;
    padding: 20px;
    border-left: 5px solid #001f54;
    border-radius: 8px;
    margin-top: 15px;
}

.evidence-title {
    font-size: 18px;
    font-weight: 700;
    color: #001f54;
    margin-bottom: 10px;
}

.evidence-text {
    font-size: 15px;
    line-height: 1.6;
    color: #333;
    margin-bottom: 12px;
}
</style>

<script>
// Insertar automáticamente el número de JWO desde Section 1
document.addEventListener("DOMContentLoaded", () => {
    const jwoInput = document.getElementById("JWO_Number");
    const jwoDisplay = document.getElementById("displayJWO");

    if (jwoInput && jwoDisplay) {
        jwoInput.addEventListener("input", () => {
            jwoDisplay.textContent = jwoInput.value.trim() || "________";
        });
    }
});
</script>
