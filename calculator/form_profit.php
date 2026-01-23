<!-- =========================
     PROFIT (BAR)
========================= -->
<div class="section-bar"
     onclick="toggleSection('profit_block', this.querySelector('.icon'))">
    <span>PROFIT</span>
    <span class="icon">▲</span>
</div>

<!-- =========================
     PROFIT CONTENT
========================= -->
<div id="profit_block" class="section-content" style="display:none;">

    <!-- =========================
         MARKUP (TIMESHEET / NORMAL)
    ========================= -->
    <div class="question-block" id="normal_markup_block">
        <label>Markup (%)</label>

        <div class="markup-bar-wrapper">
            <input type="range"
                   id="MarkupSlider"
                   min="0"
                   max="100"
                   step="0.1"
                   value="0"
                   class="markup-slider">

            <div class="markup-bar">
                <div class="markup-fill" id="markupFill">
                    <span id="markupLabel">0%</span>
                </div>
            </div>
        </div>

        <!-- PRINT -->
        <span class="print-only" id="markupLabelPrint"></span>
    </div>

    <!-- =========================
         SUBCONTRACT MARKUP
    ========================= -->
    <div class="question-block" id="subcontract_markup_block" style="display:none;">
        <label>Subcontract Markup (%)</label>

        <div class="markup-bar-wrapper">
            <input type="range"
                   id="SubcontractMarkupSlider"
                   min="0"
                   max="100"
                   step="0.1"
                   value="0"
                   class="markup-slider">

            <div class="markup-bar">
                <div class="markup-fill" id="subcontractMarkupFill">
                    <span id="subcontractMarkupLabel">0%</span>
                </div>
            </div>
        </div>

        <!-- PRINT -->
        <span class="print-only" id="subcontractMarkupLabelPrint"></span>
    </div>

    <!-- =========================
         MARKUP AMOUNT
    ========================= -->
    <div class="question-block">
        <label>Markup Amount</label>
        <input type="number"
               name="Markup_Amount"
               readonly
               class="form-control">

        <span class="print-only"></span>
    </div>

    <!-- =========================
         TOTAL NORMAL
    ========================= -->
    <div class="question-block" id="normal_total_block">
        <label>Total</label>
        <input type="number"
               name="Total"
               readonly
               class="form-control">

        <span class="print-only"></span>
    </div>

    <!-- =========================
         TOTAL HOOD VENT
    ========================= -->
    <div class="question-block" id="hoodvent_total_block" style="display:none;">
        <label>Total Hood Vent</label>
        <input type="number"
               id="HoodVent_Total"
               readonly
               class="form-control">

        <span class="print-only"></span>
    </div>

</div>
