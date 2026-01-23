<!-- =========================
     TAXES (BAR)
========================= -->
<div class="section-bar"
     onclick="toggleSection('taxes_block', this.querySelector('.icon'))">
    <span>TAXES</span>
    <span class="icon">▲</span>
</div>

<!-- =========================
     TAXES CONTENT
========================= -->
<div id="taxes_block" class="section-content" style="display:none;">

    <!-- SALES TAX -->
    <div class="question-block">
        <label>Sales Tax (8.25%)</label>
        <input type="number"
               name="Taxes"
               readonly
               class="form-control">

        <span class="print-only"></span>
    </div>

    <!-- GRAND TOTAL -->
    <div class="question-block">
        <label>Grand Total</label>
        <input type="number"
               name="Grand_Total"
               readonly
               class="form-control">

        <span class="print-only"></span>
    </div>

</div>
