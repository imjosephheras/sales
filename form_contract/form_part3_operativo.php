<!-- ========================================== -->
<!-- ⚙️ Section 3: Operational / Service Details -->
<!-- ========================================== -->

<!-- 12️⃣ Site Visit Conducted -->
<div class="question-block" id="q12">
  <label for="Site_Visit_Conducted" id="siteVisitLabel" class="question-label">
    <?= ($lang=='en') ? "12. Site Visit Conducted" : "12. ¿Se realizó visita al sitio?"; ?>
  </label>

  <select name="Site_Visit_Conducted" id="siteVisitSelect">
    <option value="">
      <?= ($lang=='en') ? "-- Select an option --" : "-- Selecciona una opción --"; ?>
    </option>
    <option value="Yes"><?= ($lang=='en') ? "Yes" : "Sí"; ?></option>
    <option value="No"><?= ($lang=='en') ? "No" : "No"; ?></option>
  </select>
</div>

<!-- 14️⃣ Invoice Frequency -->
<div class="question-block" id="q14">
  <label for="Invoice_Frequency" class="question-label">
    <?= ($lang=='en') ? "14. Invoice Frequency*" : "14. Frecuencia de Facturación*"; ?>
  </label>

  <select name="Invoice_Frequency" id="Invoice_Frequency" required>
    <option value="">
      <?= ($lang=='en') ? "-- Select an option --" : "-- Selecciona una opción --"; ?>
    </option>

    <option value="15">
      <?= ($lang=='en') ? "Every 15 days" : "Cada 15 días"; ?>
    </option>

    <option value="30">
      <?= ($lang=='en') ? "Every 30 days" : "Cada 30 días"; ?>
    </option>

    <option value="50_deposit">
      <?= ($lang=='en')
        ? "50% Deposit / 50% Upon Completion"
        : "50% Depósito / 50% al Finalizar"; ?>
    </option>

    <option value="completion">
      <?= ($lang=='en')
        ? "Payment Upon Completion"
        : "Pago al Finalizar"; ?>
    </option>
  </select>
</div>

<!-- 15️⃣ Contract Duration -->
<div class="question-block" id="q15">
  <label for="Contract_Duration" class="question-label">
    <?= ($lang=='en') ? "15. Contract Duration" : "15. Duración del Contrato"; ?>
  </label>

  <select name="Contract_Duration" id="Contract_Duration" class="duration-dropdown">
    <option value="">
      <?= ($lang=='en') ? "-- Select Duration --" : "-- Selecciona la duración --"; ?>
    </option>

    <!-- ✅ OPCIÓN PARA CASOS SIN CONTRATO -->
    <option value="not_applicable">
      <?= ($lang=='en')
        ? "Not applicable / No contract yet"
        : "No aplica / Aún sin contrato"; ?>
    </option>

    <option value="6_months">
      <?= ($lang=='en') ? "6 Months" : "6 Meses"; ?>
    </option>

    <option value="1_5_years">
      <?= ($lang=='en') ? "1.5 Years (18 Months)" : "1.5 Años (18 Meses)"; ?>
    </option>

    <option value="1_year">
      <?= ($lang=='en') ? "1 Year" : "1 Año"; ?>
    </option>

    <option value="2_years">
      <?= ($lang=='en') ? "2 Years" : "2 Años"; ?>
    </option>

    <option value="3_years">
      <?= ($lang=='en') ? "3 Years" : "3 Años"; ?>
    </option>

    <option value="4_years">
      <?= ($lang=='en') ? "4 Years" : "4 Años"; ?>
    </option>

    <option value="5_years">
      <?= ($lang=='en') ? "5 Years" : "5 Años"; ?>
    </option>
  </select>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const requestType = document.getElementById("Request_Type");
  const durationSelect = document.getElementById("Contract_Duration");

  // Opciones completas
  const allOptions = [
    { value: "6_months", text: "6 Months" },
    { value: "1_year", text: "1 Year" },
    { value: "1_5_years", text: "1.5 Years (18 Months)" },
    { value: "2_years", text: "2 Years" },
    { value: "3_years", text: "3 Years" },
    { value: "4_years", text: "4 Years" },
    { value: "5_years", text: "5 Years" }
  ];

  function updateContractDuration() {
    const type = requestType?.value?.toLowerCase() || "";
    let filtered = [];

    if (type === "proposal") {
      // Para PROPOSAL → solo 6 meses, 1.5 años, 1 año y 2 años
      filtered = allOptions.filter(opt =>
        ["6_months", "1_5_years", "1_year", "2_years"].includes(opt.value)
      );
    } else if (type === "contract") {
      // Para CONTRACT → desde 1.5 años hasta 5 años
      filtered = allOptions.filter(opt =>
        ["1_5_years", "1_year", "2_years", "3_years", "4_years", "5_years"].includes(opt.value)
      );
    } else {
      filtered = allOptions; // Si aún no se selecciona tipo, mostrar todas
    }

    // Limpiar y volver a generar las opciones
    durationSelect.innerHTML = '<option value="">-- Select Duration --</option>';
    filtered.forEach(opt => {
      const option = document.createElement("option");
      option.value = opt.value;
      option.textContent = opt.text;
      durationSelect.appendChild(option);
    });
  }

  // Ejecutar al cargar
  updateContractDuration();

  // Escuchar cambios en la pregunta 2
  if (requestType) {
    requestType.addEventListener("change", updateContractDuration);
  }
});
</script>

<!-- ====================== -->
<!-- 🎨 Estilos mejorados -->
<!-- ====================== -->
<style>
.simple-frequency-inline {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 25px;
  background: #fff;
  border: 2px solid #e1e8ed;
  border-radius: 12px;
  padding: 20px;
  max-width: 700px;
}

.period-dropdown {
  background: linear-gradient(135deg, #001f54 0%, #003080 100%);
  color: white;
  font-weight: 600;
  padding: 12px 24px;
  border-radius: 10px;
  border: none;
  font-size: 14px;
  cursor: pointer;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,31,84,0.2);
  transition: all 0.3s ease;
}

.period-dropdown:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,31,84,0.3);
}

.period-dropdown option {
  background-color: white;
  color: #001f54;
  font-weight: 500;
}

.week-block {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.week-title {
  font-weight: 600;
  color: #1a202c;
  text-transform: capitalize;
  margin-bottom: 8px;
}

.days {
  display: flex;
  gap: 10px;
}

.day-box {
  position: relative;
  width: 40px;
  height: 40px;
  border: 2px solid #001f54;
  border-radius: 8px;
  color: #001f54;
  font-weight: bold;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  background: white;
}

.day-box:hover {
  background-color: #e6f0ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,31,84,0.15);
}

.day-box input[type="checkbox"] {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  width: 100%;
  height: 100%;
  margin: 0;
}

.day-box input[type="checkbox"]:checked + span {
  background: linear-gradient(135deg, #001f54 0%, #003080 100%);
  color: #fff;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}
</style>

<!-- ======================== -->
<!-- ⚙️ Script funcional -->
<!-- ======================== -->
<script>
  document.querySelectorAll('.day-box input[type="checkbox"]').forEach((checkbox, index, list) => {
    checkbox.addEventListener('change', () => {
      // Ignorar "One Time" en la lógica de los días secuenciales
      if (checkbox.value === 'one_time') return;

      const currentIndex = index;
      if (checkbox.checked) {
        // Selecciona todos los días anteriores (1→7)
        for (let i = 0; i <= currentIndex; i++) {
          if (list[i].value !== 'one_time') list[i].checked = true;
        }
      } else {
        // Deselecciona todos los días posteriores (1→7)
        for (let i = currentIndex + 1; i < list.length; i++) {
          if (list[i].value !== 'one_time') list[i].checked = false;
        }
      }
    });
  });
</script>