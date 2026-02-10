<head>
  <meta charset="UTF-8">
  <title>Formulario de Registro | Prime Facility Services</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    /* ======= VARIABLES DE COLOR ======= */
    :root {
      --primary-color: #001f54;
      --primary-light: #003080;
      --accent-color: #c70734;
      --accent-hover: #a30000;
      --success-color: #28a745;
      --dark-color: #2c3e50;
      --light-bg: #f4f7fc;
      --card-bg: #ffffff;
      --border-color: #e1e8ed;
      --text-primary: #1a202c;
      --text-secondary: #718096;
      --shadow-sm: 0 2px 8px rgba(0,31,84,0.08);
      --shadow-md: 0 4px 16px rgba(0,31,84,0.12);
      --shadow-lg: 0 8px 32px rgba(0,31,84,0.16);
    }

    /* ======= RESET Y ESTILOS BASE ======= */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #001f54 0%, #003080 100%);
      min-height: 100vh;
      padding: 70px 0 0 0;
      margin: 0;
      color: var(--text-primary);
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* ======= CONTENEDOR PRINCIPAL ======= */
    .container {
      max-width: 1000px;
      margin: 0 auto;
      background: var(--card-bg);
      border-radius: 24px;
      box-shadow: var(--shadow-lg);
      overflow: hidden;
      animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(40px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ======= HEADER DEL FORMULARIO ======= */
    .form-header {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
      color: white;
      padding: 50px 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
      transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      border-radius: 50%;
    }

    .form-header h2 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 12px;
      letter-spacing: -0.5px;
      position: relative;
      z-index: 1;
    }

    .form-header p {
      font-size: 15px;
      opacity: 0.95;
      font-weight: 400;
      position: relative;
      z-index: 1;
    }

    /* ======= CONTENIDO DEL FORMULARIO ======= */
    .form-content {
      padding: 45px;
    }

    /* ======= SECCIONES MEJORADAS ======= */
    .section-title {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
      color: white;
      padding: 18px 24px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 12px;
      margin-top: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      user-select: none;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: var(--shadow-sm);
      position: relative;
      overflow: hidden;
    }

    .section-title::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 0;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      transition: width 0.3s ease;
    }

    .section-title:hover::before {
      width: 100%;
    }

    .section-title:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .section-title:first-of-type {
      margin-top: 0;
    }

    .toggle-icon {
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-size: 14px;
      font-weight: 600;
    }

    .section-title.collapsed .toggle-icon {
      transform: rotate(-90deg);
    }

    .section-content {
      border: 2px solid var(--border-color);
      border-top: none;
      border-radius: 0 0 12px 12px;
      padding: 30px;
      background: var(--light-bg);
      animation: expandSection 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      margin-bottom: 2px;
    }

    @keyframes expandSection {
      from {
        opacity: 0;
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
      }
      to {
        opacity: 1;
        max-height: 5000px;
        padding-top: 30px;
        padding-bottom: 30px;
      }
    }

    .section-content.hidden {
      display: none;
    }

    /* ======= COMPACT: Sections 5 (Terms) & 7 (Scope) ======= */
    .section-title[data-section="5"],
    .section-title[data-section="7"] {
      margin-top: 10px;
      padding: 12px 20px;
    }

    .section-content[data-section-content="5"],
    .section-content[data-section-content="7"] {
      padding: 12px 20px;
    }

    .section-content[data-section-content="5"] .question-block,
    .section-content[data-section-content="7"] .question-block {
      margin-bottom: 10px;
    }

    /* ======= BLOQUES DE PREGUNTAS ======= */
    .question-block {
      margin-bottom: 25px;
      animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ======= ETIQUETAS MEJORADAS ======= */
    .question-label,
    .sub-label {
      display: block;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--text-primary);
      font-size: 14px;
      position: relative;
      padding-left: 0;
    }

    .question-label::before {
      display: none;
    }

    /* ======= INPUTS Y SELECTS MODERNOS ======= */
    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="date"],
    input[type="tel"],
    select,
    textarea {
      width: 100%;
      padding: 14px 18px;
      margin-top: 6px;
      border: 2px solid var(--border-color);
      border-radius: 10px;
      font-size: 14px;
      font-family: inherit;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: white;
      color: var(--text-primary);
    }

    input:hover,
    select:hover,
    textarea:hover {
      border-color: #cbd5e0;
    }

    input:focus,
    select:focus,
    textarea:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(0,31,84,0.08);
      transform: translateY(-1px);
    }

    textarea {
      resize: vertical;
      min-height: 120px;
      font-family: inherit;
    }

    /* ======= CHECKBOXES MODERNOS ======= */
    .checkbox-group {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 6px;
      margin-top: 8px;
      padding: 10px;
      background: white;
      border-radius: 10px;
      border: 2px solid var(--border-color);
    }

    .checkbox-group label {
      display: flex;
      align-items: center;
      font-weight: 400;
      color: var(--text-primary);
      cursor: pointer;
      padding: 8px 10px;
      border-radius: 8px;
      transition: all 0.2s ease;
      background: var(--light-bg);
    }

    .checkbox-group label:hover {
      background: #e6f0ff;
      transform: translateX(3px);
    }

    .checkbox-group input[type="checkbox"] {
      margin-right: 12px;
      width: 20px;
      height: 20px;
      cursor: pointer;
      accent-color: var(--primary-color);
    }

    /* ======= BOTÓN DE ENVÍO MEJORADO ======= */
    .form-actions {
      margin-top: 40px;
      text-align: center;
      padding: 30px;
      background: var(--light-bg);
      border-radius: 12px;
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
    }

    button[type="submit"],
    .btn-submit,
    .btn-draft {
      color: white;
      padding: 16px 48px;
      border: none;
      border-radius: 50px;
      font-size: 16px;
      font-weight: 600;
      letter-spacing: 0.3px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .btn-submit {
      background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%);
      box-shadow: 0 4px 16px rgba(199,7,52,0.3);
    }

    .btn-draft {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      box-shadow: 0 4px 16px rgba(108,117,125,0.3);
    }

    button[type="submit"]::before,
    .btn-submit::before,
    .btn-draft::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255,255,255,0.2);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }

    button[type="submit"]:hover::before,
    .btn-submit:hover::before,
    .btn-draft:hover::before {
      width: 300px;
      height: 300px;
    }

    button[type="submit"]:hover,
    .btn-submit:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 24px rgba(199,7,52,0.4);
    }

    .btn-draft:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 24px rgba(108,117,125,0.4);
    }

    button[type="submit"]:active,
    .btn-submit:active,
    .btn-draft:active {
      transform: translateY(-1px);
    }

    /* ======= BOTONES SECUNDARIOS ======= */
    button[type="button"]:not(#btnPreview) {
      background: var(--primary-color);
      color: white;
      padding: 10px 24px;
      border: none;
      border-radius: 24px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      margin: 10px 5px;
    }

    button[type="button"]:not(#btnPreview):hover {
      background: var(--primary-light);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,31,84,0.25);
    }

    /* ======= TABLAS MEJORADAS ======= */
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin-top: 15px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }

    table thead {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
      color: white;
    }

    table th {
      padding: 14px;
      text-align: center;
      font-weight: 600;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    table tbody tr {
      background: white;
      transition: all 0.2s;
    }

    table tbody tr:nth-child(even) {
      background: var(--light-bg);
    }

    table tbody tr:hover {
      background: #e6f0ff;
      transform: scale(1.005);
    }

    table td {
      padding: 12px;
      text-align: center;
      border-top: 1px solid var(--border-color);
    }

    table input {
      width: 100%;
      max-width: 120px;
      padding: 8px 12px;
      border: 2px solid var(--border-color);
      border-radius: 8px;
      text-align: center;
    }

    /* ======= SECCIÓN CON ERROR ======= */
    .section-error {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
      animation: shake 0.5s;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-10px); }
      75% { transform: translateX(10px); }
    }

    .section-error::after {
      content: " ⚠ Required fields missing";
      font-size: 12px;
      font-weight: normal;
      margin-left: 10px;
      opacity: 0.95;
    }

    /* ======= RESPONSIVE ======= */
    @media (max-width: 768px) {
      .form-content {
        padding: 30px 20px;
      }
      
      .form-header {
        padding: 40px 20px;
      }
      
      .form-header h2 {
        font-size: 26px;
      }
      
      .checkbox-group {
        grid-template-columns: 1fr;
      }
      
      button[type="submit"],
      button[type="button"]#btnPreview {
        width: 100%;
        padding: 16px 30px;
      }
      
      table {
        font-size: 12px;
      }
      
      table th,
      table td {
        padding: 10px 6px;
      }

      .section-title {
        font-size: 14px;
        padding: 16px 18px;
      }

      .section-content {
        padding: 20px 15px;
      }
    }

    /* ======= LOADER MEJORADO ======= */
    .loader {
      border: 3px solid var(--light-bg);
      border-top: 3px solid var(--primary-color);
      border-radius: 50%;
      width: 50px;
      height: 50px;
      animation: spin 0.8s linear infinite;
      margin: 30px auto;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* ======= ESTILOS ADICIONALES ======= */
    .simple-frequency-inline {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 25px;
      background: white;
      border: 2px solid var(--border-color);
      border-radius: 12px;
      padding: 20px;
      max-width: 700px;
    }

    .period-dropdown {
      background: var(--primary-color);
      color: white;
      font-weight: 600;
      padding: 12px 24px;
      border-radius: 10px;
      border: none;
      font-size: 14px;
      cursor: pointer;
      text-align: center;
      box-shadow: var(--shadow-sm);
      transition: all 0.3s ease;
    }

    .period-dropdown:hover {
      background: var(--primary-light);
      transform: translateY(-2px);
    }

    .period-dropdown option {
      background: white;
      color: var(--primary-color);
      font-weight: 500;
    }

    /* ======= PRINT STYLES ======= */
    @media print {
      @page {
        margin: 15mm;
      }

      body {
        background: white !important;
        padding: 0 !important;
        color: #000 !important;
      }

      /* Hide navigation, sidebar, buttons, modals, calculator */
      .top-navbar,
      .pending-forms-sidebar,
      .form-actions,
      .janitorial-modal-overlay,
      .service-modal-overlay,
      .product-modal-overlay,
      #previewModal,
      .addRow18, .removeRow18,
      .addRow19, .removeRow19,
      .btn18, .btn19,
      .product-catalog-btn-wrap,
      .selected-product-remove,
      #toggleCalculator,
      .nav-btn,
      .calculator-side {
        display: none !important;
      }

      /* Hide catalog sections (Q18 and Q19 service tables) */
      #q18,
      #q19 {
        display: none !important;
      }

      /* Clean container */
      .container {
        box-shadow: none !important;
        border-radius: 0 !important;
        max-width: 100% !important;
      }

      .form-header {
        background: #001f54 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        padding: 20px 30px !important;
      }

      .section-title {
        background: #001f54 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }

      .section-content {
        display: block !important;
        border: 1px solid #ccc !important;
      }

      .section-content.hidden {
        display: block !important;
      }

      /* Replace empty date inputs with blank format */
      .print-date-blank {
        display: inline-block;
        font-size: 14px;
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        min-width: 150px;
        color: #999;
        letter-spacing: 2px;
      }

      input[type="date"].print-hidden,
      select.print-hidden,
      input[type="text"].print-hidden,
      input[type="email"].print-hidden,
      input[type="number"].print-hidden,
      input[type="tel"].print-hidden,
      textarea.print-hidden {
        display: none !important;
      }

      .print-value-display {
        display: inline-block !important;
        font-size: 14px;
        padding: 8px 12px;
        border-bottom: 1px solid #333;
        min-width: 200px;
      }
    }

    /* Hide print elements in screen mode */
    @media screen {
      .print-date-blank,
      .print-value-display {
        display: none !important;
      }
    }
  </style>
</head>