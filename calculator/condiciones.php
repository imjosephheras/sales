<script>
/* =====================================================
   SERVICE TYPE CONDITIONS
   ESTE ARCHIVO MANDA TODA LA LÓGICA POR MODO
===================================================== */

/* =====================================================
   HOOD VENT — TOTAL SOLO DESDE TABLA DE CAMPANAS
===================================================== */
function calculateHoodVentOnlyTotal() {
  let total = 0;

  const qtyEls   = document.querySelectorAll('select[name="hood_qty[]"]');
  const priceEls = document.querySelectorAll('select[name="hood_price[]"]');

  qtyEls.forEach((qtyEl, i) => {
    const qty   = Number(qtyEl.value) || 0;
    const price = Number(priceEls[i]?.value) || 0;
    total += qty * price;
  });

  return total;
}

/* =====================================================
   APLICADOR GLOBAL DE CONDICIONES
===================================================== */
function applyServiceTypeConditions(baseSubtotal) {

  /* -------------------------------------------------
     DETECCIÓN ROBUSTA DEL MODO
     1) Service_Type (si existe)
     2) ID Service_Type (fallback)
     3) Hood Vent table presente → hoodvent
  ------------------------------------------------- */
  const serviceType =
    document.querySelector('select[name="Service_Type"]')?.value ||
    document.getElementById('Service_Type')?.value ||
    (document.querySelector('select[name="hood_qty[]"]') ? "hoodvent" : "");

  /* -------------------------------------------------
     TIMESHEET → NO SE TOCA
  ------------------------------------------------- */
  if (serviceType === "timesheet") {
    return baseSubtotal;
  }

  /* -------------------------------------------------
     HOODVENT → BLOQUEA TODO, SOLO CAMPANAS
  ------------------------------------------------- */
  if (serviceType === "hoodvent") {
    return calculateHoodVentOnlyTotal();
  }

  /* -------------------------------------------------
     SUBCONTRACT → PRECIO DIRECTO
  ------------------------------------------------- */
  if (serviceType === "subcontract") {
    return (
      Number(document.querySelector('[name="Subcontract_Price"]')?.value) || 0
    );
  }

  /* -------------------------------------------------
     FALLBACK SEGURO
  ------------------------------------------------- */
  return baseSubtotal;
}
</script>
