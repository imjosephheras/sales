<!-- ======================================= -->
<!-- Section 7: Scope of Work (Dynamic) -->
<!-- ======================================= -->

<!-- ======================================= -->
<!-- Dynamic Scope Sections                 -->
<!-- ======================================= -->
<div class="question-block" id="qScopeSections">
  <label class="question-label">
    <?= ($lang=='en')
        ? "Scope Sections"
        : "Secciones de Alcance"; ?>
  </label>
  <div id="scopeSectionsContainer">
    <!-- Dynamic blocks will be added here -->
  </div>
  <button type="button" class="btn-add-scope-section" onclick="addScopeSection()">
    + <?= ($lang=='en') ? "Add New Scope Section" : "Agregar Nueva Seccion de Alcance"; ?>
  </button>
</div>


<!-- ======================================= -->
<!-- STYLES                                 -->
<!-- ======================================= -->
<style>
  /* ===== DYNAMIC SCOPE SECTIONS ===== */
  .btn-add-scope-section {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    background: linear-gradient(135deg, #001f54 0%, #003080 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,31,84,0.25);
    margin-top: 12px;
  }
  .btn-add-scope-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,31,84,0.35);
    background: linear-gradient(135deg, #003080 0%, #004ab5 100%);
  }
  .scope-section-block {
    background: #fff;
    border: 2px solid #e1e8ed;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 14px;
    position: relative;
    transition: all 0.2s ease;
  }
  .scope-section-block:hover {
    border-color: #001f54;
    box-shadow: 0 2px 10px rgba(0,31,84,0.1);
  }
  .scope-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
  }
  .scope-section-number {
    font-size: 12px;
    font-weight: 700;
    color: #001f54;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .scope-section-remove {
    background: #ff4d4d;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    font-size: 18px;
    line-height: 28px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
  }
  .scope-section-remove:hover {
    background: #cc0000;
    transform: scale(1.1);
  }
  .scope-section-block label.scope-field-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
  }
  .scope-section-block input[type="text"].scope-title-input {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
    margin-bottom: 10px;
    box-sizing: border-box;
  }
  .scope-section-block input[type="text"].scope-title-input:focus {
    border-color: #001f54;
  }
  .scope-section-block textarea.scope-content-input {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
    resize: vertical;
    min-height: 80px;
    box-sizing: border-box;
  }
  .scope-section-block textarea.scope-content-input:focus {
    border-color: #001f54;
  }

  /* ===== PRINT: hide scope controls ===== */
  @media print {
    .btn-add-scope-section,
    .scope-section-remove {
      display: none !important;
    }
  }
</style>


<script>
// =======================================
// DYNAMIC SCOPE SECTIONS
// =======================================
let scopeSectionCounter = 0;

function addScopeSection(title = '', content = '') {
  scopeSectionCounter++;
  const container = document.getElementById('scopeSectionsContainer');
  const index = container.children.length;

  const block = document.createElement('div');
  block.className = 'scope-section-block';
  block.dataset.scopeIndex = index;

  const langEn = <?= ($lang=='en') ? 'true' : 'false' ?>;
  const sectionLabel = langEn ? 'Scope Section' : 'Seccion de Alcance';
  const titleLabel = langEn ? 'Title' : 'Titulo';
  const scopeLabel = langEn ? 'Scope of Work' : 'Alcance del Trabajo';
  const titlePlaceholder = langEn ? 'Enter section title...' : 'Ingrese el titulo de la seccion...';
  const scopePlaceholder = langEn ? 'Describe the scope of work...' : 'Describa el alcance del trabajo...';

  block.innerHTML =
    '<div class="scope-section-header">' +
      '<span class="scope-section-number">' + sectionLabel + ' #' + (index + 1) + '</span>' +
      '<button type="button" class="scope-section-remove" onclick="removeScopeSection(this)">&times;</button>' +
    '</div>' +
    '<label class="scope-field-label">' + titleLabel + '</label>' +
    '<input type="text" class="scope-title-input" name="Scope_Sections_Title[]" value="' + escapeHtml(title) + '" placeholder="' + titlePlaceholder + '">' +
    '<label class="scope-field-label">' + scopeLabel + '</label>' +
    '<textarea class="scope-content-input" name="Scope_Sections_Content[]" rows="4" placeholder="' + scopePlaceholder + '">' + escapeHtml(content) + '</textarea>';

  container.appendChild(block);
  renumberScopeSections();
}

function removeScopeSection(btn) {
  const block = btn.closest('.scope-section-block');
  block.remove();
  renumberScopeSections();
}

function renumberScopeSections() {
  const container = document.getElementById('scopeSectionsContainer');
  const blocks = container.querySelectorAll('.scope-section-block');
  const langEn = <?= ($lang=='en') ? 'true' : 'false' ?>;
  const sectionLabel = langEn ? 'Scope Section' : 'Seccion de Alcance';

  blocks.forEach((block, i) => {
    block.dataset.scopeIndex = i;
    const numberSpan = block.querySelector('.scope-section-number');
    if (numberSpan) {
      numberSpan.textContent = sectionLabel + ' #' + (i + 1);
    }
  });
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(text));
  return div.innerHTML;
}
</script>
