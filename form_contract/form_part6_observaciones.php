<!-- ============================================ -->
<!-- 🗒️ Section 6: Observations and Follow-up Info -->
<!-- ============================================ -->

<!-- 25️⃣ Site Observation -->
<div class="question-block" id="q25">
  <label for="Site_Observation" class="question-label">
    <?= ($lang=='en')
        ? "25. Site Observation"
        : "25. Observaciones del Sitio"; ?>
  </label>

  <textarea
    name="Site_Observation"
    id="Site_Observation"
    rows="3"
    placeholder="<?= ($lang=='en')
        ? 'Enter any observations from the site visit'
        : 'Ingrese cualquier observación de la visita al sitio'; ?>"
  ></textarea>
</div>

<!-- 26️⃣ Additional Comments -->
<div class="question-block" id="q26">
  <label for="Additional_Comments" class="question-label">
    <?= ($lang=='en')
        ? "26. Additional Comments"
        : "26. Comentarios Adicionales"; ?>
  </label>

  <textarea
    name="Additional_Comments"
    id="Additional_Comments"
    rows="3"
    placeholder="<?= ($lang=='en')
        ? 'Add any relevant notes or internal comments'
        : 'Agregue notas relevantes o comentarios internos'; ?>"
  ></textarea>
</div>

<!-- 27️⃣ Email Information Sent -->
<div class="question-block" id="q27">
  <label for="Email_Information_Sent" class="question-label">
    <?= ($lang=='en')
        ? "27. Email Information Sent"
        : "27. Información Enviada por Correo"; ?>
  </label>

  <select name="Email_Information_Sent" id="Email_Information_Sent">
    <option value="">
      <?= ($lang=='en') ? "-- Select an option --" : "-- Seleccione una opción --"; ?>
    </option>
    <option value="Yes"><?= ($lang=='en') ? "Yes" : "Sí"; ?></option>
    <option value="No"><?= ($lang=='en') ? "No" : "No"; ?></option>
  </select>
</div>