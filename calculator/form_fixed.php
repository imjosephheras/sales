<!-- =========================
     FIXED COSTS (BAR)
========================= -->
<div class="section-bar"
     onclick="toggleSection('fixed_costs_block', this.querySelector('.icon'))">
    <span>FIXED COSTS</span>
    <span class="icon">▲</span>
</div>

<!-- =========================
     FIXED COSTS CONTENT
========================= -->
<div id="fixed_costs_block" class="section-content" style="display:none;">

    <!-- OVERHEAD -->
    <div class="question-block">
        <label>Overhead </label>
        <input type="number"
               name="Overhead"
               readonly
               class="form-control">

        <span class="print-only"></span>
    </div>

    <!-- NET PROFIT -->
    <div class="question-block">
        <label>Net Profit </label>
        <input type="number"
               name="Fixed_Subtotal"
               readonly
               class="form-control">

        <span class="print-only"></span>
    </div>

</div>
