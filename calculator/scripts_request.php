<script>
document.addEventListener("DOMContentLoaded", () => {

    // ⛔ Si estamos en modo impresión, no ejecutar lógica visual
    if (window.matchMedia && window.matchMedia('print').matches) {
        return;
    }

    const serviceType = document.getElementById("Service_Type");

    // TABLAS
    const blockHoodVent   = document.getElementById("q_hood_vent");
    const blockLaborCalc  = document.getElementById("q_labor_table");

    // DIRECT COSTS (q2–q6)
    const blockLabor      = document.getElementById("q2");
    const blockTransport  = document.getElementById("q3");
    const blockMaterial   = document.getElementById("q4");
    const blockEquipment  = document.getElementById("q5");
    const blockDirectSub  = document.getElementById("q6");

    // SUBCONTRACT PRICE
    const blockSubPrice = document.getElementById("subcontractor_price_block");

    function showAll() {
        if (blockHoodVent)  blockHoodVent.style.display = "block";
        if (blockLaborCalc) blockLaborCalc.style.display = "block";

        if (blockLabor)     blockLabor.style.display = "block";
        if (blockTransport) blockTransport.style.display = "block";
        if (blockMaterial)  blockMaterial.style.display = "block";
        if (blockEquipment) blockEquipment.style.display = "block";
        if (blockDirectSub) blockDirectSub.style.display = "block";

        if (blockSubPrice)
            blockSubPrice.style.display = "none";
    }

    showAll();

    if (serviceType) {
        serviceType.addEventListener("change", function () {

            const mode = this.value;
            showAll();

            if (mode === "subcontract") {
                if (blockHoodVent)  blockHoodVent.style.display = "none";
                if (blockLaborCalc) blockLaborCalc.style.display = "none";

                if (blockLabor)     blockLabor.style.display = "none";
                if (blockTransport) blockTransport.style.display = "none";
                if (blockMaterial)  blockMaterial.style.display = "none";
                if (blockEquipment) blockEquipment.style.display = "none";
                if (blockDirectSub) blockDirectSub.style.display = "none";

                if (blockSubPrice)
                    blockSubPrice.style.display = "block";
            }

            if (mode === "hoodvent") {
                if (blockHoodVent)  blockHoodVent.style.display = "block";
                if (blockLaborCalc) blockLaborCalc.style.display = "block";
            }

            if (mode === "timesheet") {
                if (blockHoodVent)  blockHoodVent.style.display = "none";
                if (blockLaborCalc) blockLaborCalc.style.display = "block";
            }
        });
    }

});

/* 🔽 FUNCIÓN DEL BOTÓN (NO afecta impresión) */
function toggleSection(id) {
    const el = document.getElementById(id);
    if (!el) return;

    // ⛔ No permitir toggle durante impresión
    if (window.matchMedia && window.matchMedia('print').matches) {
        return;
    }

    el.style.display =
        (el.style.display === "none" || el.style.display === "")
            ? "block"
            : "none";
}
</script>
