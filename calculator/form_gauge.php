<!-- =========================
     PROFIT NET INDICATOR (BAR)
========================= -->
<div class="section-bar"
     onclick="toggleSection('profit_net_block', this.querySelector('.icon'))">
    <span>PROFIT NET INDICATOR</span>
    <span class="icon">▲</span>
</div>

<!-- =========================
     PROFIT NET INDICATOR CONTENT
========================= -->
<div id="profit_net_block" class="section-content" style="display:none;">

    <div class="question-block" style="text-align:center;">
        <canvas id="profitGauge" width="220" height="140"></canvas>
        <div id="profitGaugeLabel"
             style="margin-top:10px;font-weight:700;font-size:16px;">
            0%
        </div>
    </div>

</div>
