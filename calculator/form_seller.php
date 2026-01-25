<!-- =========================
     SELLER (BAR)
========================= -->
<div class="section-bar"
     onclick="toggleSection('seller_block', this.querySelector('.icon'))">
    <span>SELLER</span>
    <span class="icon">▲</span>
</div>

<!-- =========================
     SELLER CONTENT
========================= -->
<div id="seller_block" class="section-content" style="display:none;">

    <!-- SELLER SELECT -->
    <div class="question-block">
        <label>Seller</label>
        <select name="Seller"
                class="form-control"
                onchange="calculateSellerCommission()">
            <option value="">Select a seller...</option>
            <option value="Keny Howe">Keny Howe</option>
            <option value="Norma Bustos">Norma Bustos</option>
            <option value="Sandra Hernandez">Sandra Hernandez</option>
            <option value="Miguel Palma">Miguel Palma</option>
            <option value="Rafael Perez JR">Rafael Perez JR</option>
            <option value="Patty Perez">Patty Perez</option>
        </select>
        <span class="print-only"></span>
    </div>

    <!-- COMMISSION PERCENTAGE SLIDER -->
    <div class="question-block">
        <label>Commission Percentage</label>

        <div class="markup-bar-wrapper">
            <input type="range"
                   id="SellerCommissionSlider"
                   class="markup-slider"
                   min="0"
                   max="30"
                   step="0.5"
                   value="0"
                   oninput="calculateSellerCommission()">
            <div class="markup-bar">
                <div id="sellerCommissionFill" class="markup-fill">
                    <span id="sellerCommissionLabel">0%</span>
                </div>
            </div>
        </div>

        <!-- Print version -->
        <div class="markup-bar-wrapper print-only-bar" style="display:none;">
            <div class="markup-bar">
                <div id="sellerCommissionFillPrint" class="markup-fill">
                    <span id="sellerCommissionLabelPrint">0%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- COMMISSION TOTAL -->
    <div class="question-block">
        <label>Commission Total</label>
        <input type="number"
               name="Seller_Commission"
               readonly
               class="form-control">
        <span class="print-only"></span>
    </div>

</div>
