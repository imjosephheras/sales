<!DOCTYPE html>
<html lang="es">
<?php include 'header.php'; ?>
<body>
  <div class="container">

<!-- 🔹 Encabezado -->
<div class="form-header" id="formHeader"
     style="text-align:center; margin-bottom:25px; padding:25px 0;
            background-color:#a30000; border-radius:10px;">
  <h2 style="font-size:26px; font-weight:700; color:white; margin:0;">
    🧾 Registration Form
  </h2>
</div>

<!-- 🔹 Contenido -->
<div class="form-content">
  <form id="main_form" action="pdf_y_correo.php" method="POST">


    <div class="section-title collapsible">Invoice <span class="toggle-icon">▼</span></div>
    <div class="section-content hidden"><?php include 'invoice.php'; ?></div>



    <!-- 🔹 Botón principal -->
    <div class="form-actions" style="text-align:center; margin-top:25px;">
      <button type="button" id="btnPreview"
              style="padding:10px 25px; font-size:16px; font-weight:600;
                     background:#a30000; color:white; border:none;
                     border-radius:6px; cursor:pointer;">
        📧 Enviar
      </button>
    </div>

    <!-- 🪟 Modal de previsualización -->
    <div id="previewModal" style="
      display:none; position:fixed; top:0; left:0; width:100%; height:100%;
      background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:9999;
    ">
      <div style="
        background:white; padding:25px; border-radius:10px; max-width:800px; width:90%;
        box-shadow:0 4px 20px rgba(0,0,0,0.3); overflow-y:auto; max-height:90vh;
      ">
        <h2 style="color:#a30000; margin-bottom:10px;">🧾 Previsualización del Formulario</h2>
        <div id="previewContent" style="text-align:left; font-size:15px; line-height:1.5;"></div>
        <div style="text-align:center; margin-top:20px;">
          <button id="confirmSend" style="background:#007bff; color:white; padding:8px 18px; border:none; border-radius:6px;">✅ Confirmar y Enviar</button>
          <button id="cancelPreview" style="background:#ccc; padding:8px 18px; border:none; border-radius:6px;">❌ Cancelar</button>
        </div>
      </div>
    </div>

  </form>
</div>

<style>
.section-title {
  background-color: #001f54;
  color: white;
  padding: 12px 16px;
  font-size: 18px;
  font-weight: bold;
  border-radius: 8px;
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  user-select: none;
  transition: background 0.3s ease;
}
.section-title:hover { background-color: #003080; }
.toggle-icon { transition: transform 0.3s ease; }
.section-title.collapsed .toggle-icon { transform: rotate(-90deg); }
.section-content {
  border: 1px solid #ccc;
  border-top: none;
  border-radius: 0 0 8px 8px;
  padding: 15px;
  background-color: #f9f9f9;
}
.section-content.hidden { display: none; }
</style>

<!-- ====================================================== -->
<!-- Scripts -->
<!-- ====================================================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  // 🔹 Comportamiento de secciones plegables
  const sections = document.querySelectorAll(".section-title");
  sections.forEach(section => {
    const content = section.nextElementSibling;
    content.classList.add("hidden");
    section.classList.add("collapsed");
    section.addEventListener("click", () => {
      section.classList.toggle("collapsed");
      content.classList.toggle("hidden");
    });
  });

  // 🔹 Abrir previsualización
  document.getElementById("btnPreview").addEventListener("click", () => {
    const form = document.getElementById("main_form");
    const data = new FormData(form);

    let html = "<table style='width:100%; border-collapse:collapse;'>";
    data.forEach((value, key) => {
      if (value.trim() !== "")
        html += `<tr>
          <td style='font-weight:bold; padding:6px; border-bottom:1px solid #ddd;'>${key.replaceAll("_", " ")}</td>
          <td style='padding:6px; border-bottom:1px solid #ddd;'>${value}</td>
        </tr>`;
    });
    html += "</table>";

    document.getElementById("previewContent").innerHTML = html;
    document.getElementById("previewModal").style.display = "flex";
  });

  // 🔹 Cerrar modal
  document.getElementById("cancelPreview").addEventListener("click", () => {
    document.getElementById("previewModal").style.display = "none";
  });

    // 🔹 Confirmar envío
    document.getElementById("confirmSend").addEventListener("click", () => {
        prepareInvoiceDataForSubmit();    // ← crear hidden inputs
        document.getElementById("main_form").requestSubmit(); // ← sí activa onsubmit
    });

});
</script>

<?php 
// (scripts eliminados — no usados en este módulo)
?>

</body>
</html>

