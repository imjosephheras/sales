<!-- ================================== -->
<!-- 👤 Section 2: Client Information -->
<!-- ================================== -->

<div class="section-title">
  <?= ($lang=='en') ? "Section 2: Client Information" : "Sección 2: Información del Cliente"; ?>
</div>

<!-- 📧 Optional: Quick Access with Verification Code -->
<div class="question-block" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
  <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
    <span style="font-weight: 600; color: #001f54;">
      💡 <?= ($lang=='en') ? "Optional:" : "Opcional:"; ?>
    </span>
    <span style="color: #666;">
      <?= ($lang=='en')
        ? "Do you have an existing client account? Get quick access with a verification code."
        : "¿Tienes una cuenta de cliente existente? Obtén acceso rápido con un código de verificación."; ?>
    </span>
  </div>

  <!-- Email Input -->
  <div id="email_verification_step">
    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px;">
      <input
        type="email"
        id="user_email"
        placeholder="<?= ($lang=='en') ? 'your.email@company.com' : 'tu.correo@empresa.com'; ?>"
        style="flex: 1;"
      >
      <button
        type="button"
        id="send_code_btn"
        style="padding: 10px 20px; background: #001f54; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;"
      >
        <?= ($lang=='en') ? "Send Code" : "Enviar Código"; ?>
      </button>
    </div>
    <div id="email_message" style="font-size: 14px;"></div>
  </div>

  <!-- Code Verification -->
  <div id="code_verification_step" style="display: none; margin-top: 15px;">
    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px;">
      <input
        type="text"
        id="verification_code"
        maxlength="6"
        placeholder="000000"
        style="flex: 1; letter-spacing: 4px; font-size: 18px; text-align: center;"
      >
      <button
        type="button"
        id="verify_code_btn"
        style="padding: 10px 20px; background: #001f54; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;"
      >
        <?= ($lang=='en') ? "Verify Code" : "Verificar Código"; ?>
      </button>
    </div>
    <div id="code_message" style="font-size: 14px;"></div>
  </div>

  <!-- Client Selection -->
  <div id="client_selection_step" style="display: none; margin-top: 15px;">
    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #001f54;">
      <?= ($lang=='en') ? "Select from your clients:" : "Selecciona uno de tus clientes:"; ?>
    </label>
    <select id="select_client" style="width: 100%; padding: 10px;">
      <option value="">
        <?= ($lang=='en') ? "-- Select a client --" : "-- Selecciona un cliente --"; ?>
      </option>
    </select>
  </div>
</div>

<!-- 📝 Client Form Fields -->
<div id="client_form_fields">

  <!-- 5️⃣ Client Name -->
  <div class="question-block" id="q5">
    <label for="client_name" class="question-label">
      <?= ($lang=='en') ? "5. Client Name*" : "5. Nombre del Cliente*"; ?>
    </label>
    <input
      type="text"
      name="Client_Name"
      id="client_name"
      placeholder="<?= ($lang=='en') ? 'Enter client name' : 'Ingresa el nombre del cliente'; ?>"
      required
    >
  </div>

  <!-- 6️⃣ Client Title -->
  <div class="question-block" id="q6">
    <label for="Client_Title" class="question-label">
      <?= ($lang=='en') ? "6. Client Title" : "6. Puesto del Cliente"; ?>
    </label>
    <input
      type="text"
      name="Client_Title"
      id="Client_Title"
      placeholder="<?= ($lang=='en') ? 'Enter client title or position' : 'Ingresa el puesto o título del cliente'; ?>"
    >
  </div>

  <!-- 7️⃣ Email -->
  <div class="question-block" id="q7">
    <label for="Email" class="question-label">
      <?= ($lang=='en') ? "7. Email*" : "7. Correo Electrónico*"; ?>
    </label>
    <input
      type="email"
      name="Email"
      id="Email"
      placeholder="<?= ($lang=='en') ? 'example@domain.com' : 'ejemplo@dominio.com'; ?>"
      required
    >
  </div>

  <!-- 8️⃣ Phone Number -->
  <div class="question-block" id="q8">
    <label for="Number_Phone" class="question-label">
      <?= ($lang=='en') ? "8. Phone Number*" : "8. Número Telefónico*"; ?>
    </label>
    <input
      type="text"
      name="Number_Phone"
      id="Number_Phone"
      placeholder="<?= ($lang=='en') ? 'Enter phone number' : 'Ingresa el número telefónico'; ?>"
      required
    >
  </div>

  <!-- 9️⃣ Company Name -->
  <div class="question-block" id="q9">
    <label for="Company_Name" class="question-label">
      <?= ($lang=='en') ? "9. Company Name*" : "9. Nombre de la Empresa*"; ?>
    </label>
    <input
      type="text"
      name="Company_Name"
      id="Company_Name"
      placeholder="<?= ($lang=='en') ? 'Enter company name' : 'Ingresa el nombre de la empresa'; ?>"
      required
    >
  </div>

  <!-- 🔟 Company Address -->
  <div class="question-block" id="q10">
    <label for="Company_Address" class="question-label">
      <?= ($lang=='en') ? "10. Company Address*" : "10. Dirección de la Empresa*"; ?>
    </label>
    <input
      type="text"
      name="Company_Address"
      id="Company_Address"
      placeholder="<?= ($lang=='en') ? 'Enter company address' : 'Ingresa la dirección de la empresa'; ?>"
      required
    >
  </div>

  <!-- 1️⃣1️⃣ Is this a new client? -->
  <div class="question-block" id="q11">
    <label for="Is_New_Client" class="question-label">
      <?= ($lang=='en') ? "11. Is this a new client?*" : "11. ¿Es un cliente nuevo?*"; ?>
    </label>
    <select name="Is_New_Client" id="Is_New_Client" required>
      <option value="">
        <?= ($lang=='en') ? "-- Select an option --" : "-- Selecciona una opción --"; ?>
      </option>
      <option value="Yes"><?= ($lang=='en') ? "Yes" : "Sí"; ?></option>
      <option value="No"><?= ($lang=='en') ? "No" : "No"; ?></option>
    </select>
  </div>

</div>


<script>
const API_BASE_URL = 'https://primefsgroup.com/api';

let clientsData = [];
let currentUserEmail = '';
let isAutoFilled = false; // Track if data came from API

// Referencias
const userEmailInput = document.getElementById('user_email');
const sendCodeBtn = document.getElementById('send_code_btn');
const emailMessage = document.getElementById('email_message');
const codeVerificationStep = document.getElementById('code_verification_step');
const verificationCodeInput = document.getElementById('verification_code');
const verifyCodeBtn = document.getElementById('verify_code_btn');
const codeMessage = document.getElementById('code_message');
const clientSelectionStep = document.getElementById('client_selection_step');
const selectClient = document.getElementById('select_client');

// 📧 Step 1: Send verification code
sendCodeBtn.addEventListener('click', async function() {
  const email = userEmailInput.value.trim();
  
  if (!email) {
    showMessage(emailMessage, 'Please enter your email address', 'error');
    return;
  }

  if (!isValidEmail(email)) {
    showMessage(emailMessage, 'Please enter a valid email address', 'error');
    return;
  }

  // Disable button and show loading
  sendCodeBtn.disabled = true;
  sendCodeBtn.textContent = 'Sending...';
  
  try {
    const response = await fetch(`${API_BASE_URL}/client-access/send-code`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email })
    });

    const data = await response.json();

    if (data.success) {
      currentUserEmail = email;
      showMessage(emailMessage, '✓ Code sent successfully! Check your email.', 'success');
      
      // Show code verification step
      setTimeout(() => {
        codeVerificationStep.style.display = 'block';
        verificationCodeInput.focus();
      }, 1000);
      
      userEmailInput.disabled = true;
      sendCodeBtn.disabled = true;
      sendCodeBtn.textContent = 'Code Sent';
      
    } else {
      showMessage(emailMessage, '✗ ' + data.message, 'error');
      sendCodeBtn.disabled = false;
      sendCodeBtn.textContent = 'Send Code';
    }

  } catch (error) {
    showMessage(emailMessage, '✗ Network error. Please try again.', 'error');
    sendCodeBtn.disabled = false;
    sendCodeBtn.textContent = 'Send Code';
    console.error('Error:', error);
  }
});

// 🔐 Step 2: Verify code
verifyCodeBtn.addEventListener('click', async function() {
  const code = verificationCodeInput.value.trim();
  
  if (!code || code.length !== 6) {
    showMessage(codeMessage, 'Please enter the 6-digit code', 'error');
    return;
  }

  // Disable button and show loading
  verifyCodeBtn.disabled = true;
  verifyCodeBtn.textContent = 'Verifying...';
  
  try {
    const response = await fetch(`${API_BASE_URL}/client-access/verify-code`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ 
        email: currentUserEmail,
        code: code 
      })
    });

    const data = await response.json();

    if (data.success) {
      showMessage(codeMessage, '✓ Code verified! Loading clients...', 'success');
      
      // Store clients data
      clientsData = data.clients;
      
      // Load clients into dropdown
      loadClients(data.clients);
      
      // Show client selection step
      setTimeout(() => {
        clientSelectionStep.style.display = 'block';
        verificationCodeInput.disabled = true;
        verifyCodeBtn.disabled = true;
        verifyCodeBtn.textContent = 'Verified';
      }, 1000);
      
    } else {
      showMessage(codeMessage, '✗ ' + data.message, 'error');
      verifyCodeBtn.disabled = false;
      verifyCodeBtn.textContent = 'Verify Code';
    }

  } catch (error) {
    showMessage(codeMessage, '✗ Network error. Please try again.', 'error');
    verifyCodeBtn.disabled = false;
    verifyCodeBtn.textContent = 'Verify Code';
    console.error('Error:', error);
  }
});

// 📋 Load clients into dropdown
function loadClients(clients) {
  selectClient.innerHTML = '<option value="">-- Select a client --</option>';
  
  clients.forEach((client, index) => {
    const option = document.createElement('option');
    option.value = index;
    option.textContent = `${client.name} - ${client.company}`;
    option.dataset.client = JSON.stringify(client);
    selectClient.appendChild(option);
  });
}

// 📝 Auto-fill form when client is selected
selectClient.addEventListener('change', function() {
  if (this.value !== '') {
    const clientData = JSON.parse(this.options[this.selectedIndex].dataset.client);
    fillClientForm(clientData, true);
    isAutoFilled = true;
    
    // Add visual indicator that data is from API (read-only)
    addReadOnlyIndicator();
  } else {
    clearClientForm();
    isAutoFilled = false;
    removeReadOnlyIndicator();
  }
});

// 🔄 Fill form with client data (DATOS ENMASCARADOS desde API)
function fillClientForm(client, readonly = true) {
  document.getElementById('client_name').value = client.name || '';
  document.getElementById('Client_Title').value = client.area || '';
  document.getElementById('Email').value = client.email || ''; // s.......o@g......m
  document.getElementById('Number_Phone').value = client.phone || ''; // (***) ***-1234
  document.getElementById('Company_Name').value = client.company || '';
  document.getElementById('Company_Address').value = client.address || '';
  document.getElementById('Is_New_Client').value = client.isNew || 'No';
  
  setFieldsReadOnly(readonly);
}

// 🔒 Set fields readonly
function setFieldsReadOnly(readonly) {
  document.getElementById('client_name').readOnly = readonly;
  document.getElementById('Client_Title').readOnly = readonly;
  document.getElementById('Email').readOnly = readonly;
  document.getElementById('Number_Phone').readOnly = readonly;
  document.getElementById('Company_Name').readOnly = readonly;
  document.getElementById('Company_Address').readOnly = readonly;
  
  // Visual feedback
  const fields = ['client_name', 'Client_Title', 'Email', 'Number_Phone', 'Company_Name', 'Company_Address'];
  fields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (readonly) {
      field.style.backgroundColor = '#f8f9fa';
      field.style.cursor = 'not-allowed';
    } else {
      field.style.backgroundColor = '';
      field.style.cursor = '';
    }
  });
}

// 📌 Add indicator for read-only data
function addReadOnlyIndicator() {
  if (document.getElementById('readonly_indicator')) return;
  
  const indicator = document.createElement('div');
  indicator.id = 'readonly_indicator';
  indicator.style.cssText = `
    background: #e3f2fd;
    border-left: 4px solid #001f54;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 4px;
    font-size: 14px;
    color: #001f54;
  `;
  indicator.innerHTML = `
    <strong>ℹ️ Protected Client Data</strong><br>
    <span style="font-size: 13px; color: #666;">
      This information is loaded from your verified account. 
      <a href="#" id="edit_client_btn" style="color: #001f54; text-decoration: underline;">Click here to edit manually</a>
    </span>
  `;
  
  const clientFormFields = document.getElementById('client_form_fields');
  clientFormFields.insertBefore(indicator, clientFormFields.firstChild);
  
  // Add click handler for edit button
  document.getElementById('edit_client_btn').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to edit this client information? Protected data will be cleared.')) {
      clearClientForm();
      isAutoFilled = false;
      removeReadOnlyIndicator();
      selectClient.value = '';
    }
  });
}

// 🗑️ Remove indicator
function removeReadOnlyIndicator() {
  const indicator = document.getElementById('readonly_indicator');
  if (indicator) {
    indicator.remove();
  }
}

// 🧹 Clear form
function clearClientForm() {
  document.getElementById('client_name').value = '';
  document.getElementById('Client_Title').value = '';
  document.getElementById('Email').value = '';
  document.getElementById('Number_Phone').value = '';
  document.getElementById('Company_Name').value = '';
  document.getElementById('Company_Address').value = '';
  document.getElementById('Is_New_Client').value = '';
  
  setFieldsReadOnly(false);
}

// 💬 Show message
function showMessage(element, text, type) {
  element.textContent = text;
  element.style.color = type === 'success' ? '#10b981' : '#ef4444';
  element.style.fontWeight = '600';
}

// ✅ Email validation
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Only allow numbers in verification code
verificationCodeInput.addEventListener('input', function(e) {
  this.value = this.value.replace(/[^0-9]/g, '');
});
</script>