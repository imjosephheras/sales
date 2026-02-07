<head>
  <meta charset="UTF-8">
  <title>Formulario de Registro | Prime Facility Services</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    /* ======= VARIABLES DE COLOR ======= */
    :root {
      --primary-color: #c70734;
      --secondary-color: #007bff;
      --success-color: #28a745;
      --dark-color: #2c3e50;
      --light-bg: #f8f9fa;
      --border-color: #dee2e6;
      --shadow: 0 5px 15px rgba(0,0,0,0.08);
      --shadow-hover: 0 8px 25px rgba(0,0,0,0.12);
    }

    /* ======= RESET Y ESTILOS BASE ======= */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #03194e 0%, #48577f 100%);
      min-height: 100vh;
      padding: 20px;
      color: var(--dark-color);
      line-height: 1.6;
    }

    /* ======= CONTENEDOR PRINCIPAL ======= */
    .container {
      max-width: 900px;
      margin: 0 auto;
      background: white;
      border-radius: 20px;
      box-shadow: var(--shadow-hover);
      overflow: hidden;
      animation: slideIn 0.5s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ======= HEADER DEL FORMULARIO ======= */
    .form-header {
      background: linear-gradient(135deg, var(--primary-color) 0%, #8b0000 100%);
      color: white;
      padding: 40px;
      text-align: center;
    }

    .form-header h2 {
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 10px;
      letter-spacing: -0.5px;
    }

    .form-header p {
      font-size: 14px;
      opacity: 0.9;
      font-weight: 300;
    }

    /* ======= CONTENIDO DEL FORMULARIO ======= */
    .form-content {
      padding: 40px;
    }

    /* ======= SECCIONES ======= */
    .section-title {
      margin: 40px 0 25px;
      font-size: 18px;
      font-weight: 600;
      color: var(--primary-color);
      padding: 15px 20px;
      background: linear-gradient(90deg, rgba(204,0,0,0.05) 0%, rgba(204,0,0,0) 100%);
      border-left: 4px solid var(--primary-color);
      border-radius: 5px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      position: relative;
    }

    .section-title:first-of-type {
      margin-top: 0;
    }

    /* ======= ETIQUETAS ======= */
    .question-label {
      display: block;
      font-weight: 500;
      margin: 25px 0 10px;
      color: var(--dark-color);
      font-size: 15px;
      position: relative;
      padding-left: 15px;
    }

    .question-label::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 6px;
      height: 6px;
      background: var(--primary-color);
      border-radius: 50%;
    }

    /* ======= INPUTS Y SELECTS ======= */
    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="date"],
    input[type="tel"],
    select,
    textarea {
      width: 100%;
      padding: 12px 15px;
      margin-top: 5px;
      border: 2px solid var(--border-color);
      border-radius: 10px;
      font-size: 14px;
      font-family: inherit;
      transition: all 0.3s ease;
      background: white;
    }

    input:hover,
    select:hover,
    textarea:hover {
      border-color: #c0c4cc;
    }

    input:focus,
    select:focus,
    textarea:focus {
      outline: none;
      border-color: var(--secondary-color);
      box-shadow: 0 0 0 4px rgba(0,123,255,0.1);
      transform: translateY(-1px);
    }

    textarea {
      resize: vertical;
      min-height: 100px;
      font-family: inherit;
    }

    /* ======= CHECKBOXES MEJORADOS ======= */
    .checkbox-group {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-top: 15px;
      padding: 20px;
      background: var(--light-bg);
      border-radius: 10px;
    }

    .checkbox-group label {
      display: flex;
      align-items: center;
      font-weight: 400;
      color: var(--dark-color);
      cursor: pointer;
      padding: 10px;
      border-radius: 8px;
      transition: background 0.2s;
    }

    .checkbox-group label:hover {
      background: white;
    }

    .checkbox-group input[type="checkbox"] {
      margin-right: 10px;
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    /* ======= BOTÓN DE ENVÍO ======= */
    .form-actions {
      margin-top: 40px;
      text-align: center;
      padding: 20px;
      background: var(--light-bg);
      border-radius: 10px;
    }

    button[type="submit"] {
      background: linear-gradient(135deg, var(--primary-color) 0%, #8b0000 100%);
      color: white;
      padding: 15px 50px;
      border: none;
      border-radius: 30px;
      font-size: 16px;
      font-weight: 600;
      letter-spacing: 0.5px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(204,0,0,0.3);
    }

    button[type="submit"]:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(204,0,0,0.4);
    }

    button[type="submit"]:active {
      transform: translateY(0);
    }

    /* ======= BOTONES SECUNDARIOS ======= */
    button[type="button"] {
      background: var(--secondary-color);
      color: white;
      padding: 8px 20px;
      border: none;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      margin: 10px 5px;
    }

    button[type="button"]:hover {
      background: #0056b3;
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(0,123,255,0.3);
    }

    /* ======= TABLAS MEJORADAS ======= */
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin-top: 15px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    table thead {
      background: linear-gradient(135deg, var(--primary-color) 0%, #8b0000 100%);
      color: white;
    }

    table th {
      padding: 12px;
      text-align: center;
      font-weight: 600;
      font-size: 14px;
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
      background: #f0f0f0;
      transform: scale(1.01);
    }

    table td {
      padding: 12px;
      text-align: center;
      border-top: 1px solid var(--border-color);
    }

    table input {
      width: 100%;
      max-width: 120px;
      padding: 6px 10px;
      border: 1px solid var(--border-color);
      border-radius: 6px;
      text-align: center;
    }

    /* ======= CAMPOS DINÁMICOS ======= */
    .kitchen-table,
    .staff-table {
      margin-bottom: 20px;
    }

    .kitchen-category h4,
    .staff-category h4 {
      background: linear-gradient(135deg, var(--primary-color) 0%, #8b0000 100%);
      color: white;
      padding: 10px 15px;
      border-radius: 10px 10px 0 0;
      margin: 0;
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* ======= RESPONSIVE ======= */
    @media (max-width: 768px) {
      .form-content {
        padding: 25px;
      }
      
      .form-header {
        padding: 30px 20px;
      }
      
      .form-header h2 {
        font-size: 24px;
      }
      
      .checkbox-group {
        grid-template-columns: 1fr;
      }
      
      button[type="submit"] {
        width: 100%;
      }
      
      table {
        font-size: 12px;
      }
      
      table th,
      table td {
        padding: 8px 5px;
      }
    }

    /* ======= ANIMACIONES ADICIONALES ======= */
    .fade-in {
      animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    /* ======= LOADER ======= */
    .loader {
      border: 3px solid var(--light-bg);
      border-top: 3px solid var(--primary-color);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 20px auto;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>