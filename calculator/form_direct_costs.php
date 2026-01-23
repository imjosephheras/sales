<!-- =========================
     DIRECT COSTS (BAR)
========================= -->
<div class="section-bar"
     onclick="toggleSection('direct_costs_block', this.querySelector('.icon'))">
    <span>DIRECT COSTS</span>
    <span class="icon">▲</span>
</div>

<!-- =========================
     DIRECT COSTS CONTENT
========================= -->
<div id="direct_costs_block" class="section-content" style="display:none;">

    <!-- LABOR COST -->
    <div class="question-block">
        <label>Labor Cost *</label>

        <div style="display:flex; gap:10px; align-items:center;">
            <input type="number"
                   name="Labor_Cost"
                   class="form-control"
                   style="flex:1;">

            <span class="print-only"></span>

            <button type="button"
                    class="btn btn-outline-secondary"
                    style="width:52px; height:52px; font-size:22px;"
                    onclick="toggleSection('q_labor_table')"
                    title="Labor Calculation">
                🧮
            </button>

            <button type="button"
                    class="btn btn-outline-secondary"
                    style="width:52px; height:52px; font-size:22px;"
                    onclick="toggleSection('laborExtras')"
                    title="Tx, Insurance, Fringe Benefits">
                📄
            </button>
        </div>
    </div>

    <!-- FRINGE -->
    <div id="laborExtras" style="display:none; margin-top:20px;">
        <div class="section-title">
            Tx, Insurance, Fringe Benefits
        </div>

        <div class="question-block">
            <label>19.93% of Labor Cost</label>
            <input type="number"
                   id="labor_fringe"
                   name="Labor_Fringe"
                   class="form-control"
                   readonly>

            <span class="print-only"></span>
        </div>
    </div>

    <!-- TRANSPORT -->
    <div class="question-block">
        <label>Transport</label>
        <input type="number"
               name="Transport_Cost"
               class="form-control">

        <span class="print-only"></span>
    </div>

    <!-- MATERIAL -->
    <div class="question-block">
        <label>Material</label>
        <input type="number"
               name="Material_Cost"
               class="form-control">

        <span class="print-only"></span>
    </div>

    <!-- EQUIPMENT -->
    <div class="question-block">
        <label>Equipment</label>
        <input type="number"
               name="Equipment_Cost"
               class="form-control">

        <span class="print-only"></span>
    </div>

    <!-- DIRECT SUBTOTAL -->
    <div class="question-block">
        <label>Direct Subtotal</label>
        <input type="number"
               name="Direct_Subtotal"
               readonly
               class="form-control">

        <span class="print-only"></span>
    </div>

</div>
