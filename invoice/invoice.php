<!-- ==================================== -->
<!-- 🧾 ORDER ITEMS MODULE - API VERSION -->
<!-- ==================================== -->

<form action="enviar_correo.php" method="POST" onsubmit="return handleFormSubmit(event)">

<div class="section-title">ORDER ITEMS</div>

<!-- 🔹 PROVIDER NAME -->
<div class="question-block">
  <label class="question-label">Provider Name:</label>
  <input type="text" name="provider_name" placeholder="Enter provider name" required>
</div>

<!-- 🔹 SELECT MONTH -->
<div class="question-block">
  <label class="question-label">Select Month:</label>
  <select name="invoice_month" required>
    <option value="">-- Select Month --</option>
    <option value="January">January</option>
    <option value="February">February</option>
    <option value="March">March</option>
    <option value="April">April</option>
    <option value="May">May</option>
    <option value="June">June</option>
    <option value="July">July</option>
    <option value="August">August</option>
    <option value="September">September</option>
    <option value="October">October</option>
    <option value="November">November</option>
    <option value="December">December</option>
  </select>
</div>

<!-- 🔹 SELECT YEAR -->
<div class="question-block">
  <label class="question-label">Select Year:</label>
  <select name="invoice_year" required>
    <option value="">-- Select Year --</option>
    <option value="2024">2024</option>
    <option value="2025">2025</option>
    <option value="2026">2026</option>
    <option value="2027">2027</option>
  </select>
</div>

<!-- 🔹 INCLUDE ORDER ITEMS -->
<div class="question-block">
  <label class="question-label">Include Order Items?</label>

  <select id="includeItems" name="includeItems" onchange="toggleItemsTable()">
    <option value="">-- Select an option --</option>
    <option value="No">No</option>
    <option value="Yes">Yes</option>
  </select>

  <!-- 📧 VERIFICATION MODULE (Hidden by default) -->
  <div id="productVerificationModule" style="display:none; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
      <span style="font-weight: 600; color: #001f54;">🔐 Product Access Verification</span>
    </div>
    
    <!-- Email Input -->
    <div id="product_email_step">
      <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px;">
        <input 
          type="email" 
          id="product_user_email" 
          placeholder="your.email@company.com"
          style="flex: 1;"
        >
        <button 
          type="button" 
          id="product_send_code_btn" 
          style="
            padding: 10px 20px;
            background: #c00;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
          "
        >
          Send Code
        </button>
      </div>
      <div id="product_email_message" style="font-size: 14px;"></div>
    </div>

    <!-- Code Verification -->
    <div id="product_code_step" style="display: none; margin-top: 15px;">
      <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px;">
        <input 
          type="text" 
          id="product_verification_code" 
          placeholder="000000"
          maxlength="6"
          style="flex: 1; letter-spacing: 4px; font-size: 18px; text-align: center;"
        >
        <button 
          type="button" 
          id="product_verify_code_btn" 
          style="
            padding: 10px 20px;
            background: #c00;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
          "
        >
          Verify Code
        </button>
      </div>
      <div id="product_code_message" style="font-size: 14px;"></div>
    </div>

    <!-- Client Selection (Hidden until verified) -->
    <div id="client_selection_products" style="display: none; margin-top: 15px;">
      <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #001f54;">
        📍 Select Client for this Order:
      </label>
      <select id="select_client_products" style="width: 100%; padding: 10px;" required>
        <option value="">-- Select a client --</option>
      </select>
      <div id="client_info_display" style="margin-top: 10px; padding: 10px; background: #e3f2fd; border-radius: 5px; display: none;">
        <strong>Selected Client:</strong>
        <div id="client_details" style="margin-top: 5px; font-size: 14px;"></div>
      </div>
    </div>
  </div>

  <div id="itemsTableContainer" style="display:none; margin-top: 10px;"></div>
</div>

<button type="submit" style="margin-top:25px;" class="btn">Submit Invoice</button>

</form>



<!-- ==================================== -->
<!--               CSS                    -->
<!-- ==================================== -->
<style>
  .items-category {
    margin-top: 20px;
    border-top: 3px solid #c00;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  }

  .items-header {
    background-color: #c00;
    color: #fff;
    padding: 12px 15px;
    font-size: 16px;
    font-weight: bold;
    display: flex;
    justify-content: space-between;
    cursor: pointer;
  }

  .toggle-icon {
    font-size: 18px;
    transition: 0.3s;
  }

  .items-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 6px;
    display: none;
  }

  .expanded .items-table {
    display: table;
  }

  .expanded .toggle-icon {
    transform: rotate(180deg);
  }

  th {
    background: #c00;
    color: white;
    padding: 8px;
  }

  td {
    text-align: center;
    padding: 8px;
  }

  .readonly {
    background: #f3f3f3;
  }

  .add-row-btn {
    background: #c00;
    color: white;
    padding: 8px 15px;
    border-radius: 4px;
    margin: 12px;
    border: none;
    cursor: pointer;
  }

  .remove-btn {
    background: #007bff;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    border: none;
    cursor: pointer;
  }

  .remove-btn:hover {
    background: #0056c3;
  }

  table input {
    width: 100%;
  }
</style>

<!-- ==================================== -->
<!--             JAVASCRIPT               -->
<!-- ==================================== -->
<script>
// ===============================
// VARIABLES GLOBALES (FUERA DEL DOMContentLoaded)
// ===============================
let PRODUCTS = [];
let CLIENTS = [];
let currentProductEmail = '';
let currentProductCode = '';
let productsLoaded = false;
let selectedClientAddressId = null;
//const API_BASE_URL = 'http://127.0.0.1:8000/api';
const API_BASE_URL = 'https://primefsgroup.com/api';

console.log('Script loaded, API_BASE_URL:', API_BASE_URL);

document.addEventListener("DOMContentLoaded", function () {
  console.log('DOM Content Loaded');

  const itemContainer = document.getElementById("itemsTableContainer");
  
  // Referencias del módulo de verificación
  const productUserEmailInput = document.getElementById('product_user_email');
  const productSendCodeBtn = document.getElementById('product_send_code_btn');
  const productEmailMessage = document.getElementById('product_email_message');
  const productCodeStep = document.getElementById('product_code_step');
  const productVerificationCodeInput = document.getElementById('product_verification_code');
  const productVerifyCodeBtn = document.getElementById('product_verify_code_btn');
  const productCodeMessage = document.getElementById('product_code_message');
  const clientSelectionProducts = document.getElementById('client_selection_products');
  const selectClientProducts = document.getElementById('select_client_products');
  const clientInfoDisplay = document.getElementById('client_info_display');
  const clientDetails = document.getElementById('client_details');

  // ===============================
  // TOGGLE MODULE
  // ===============================
  window.toggleItemsTable = function () {
    console.log('toggleItemsTable called, includeItems:', includeItems.value);
    if (includeItems.value === "Yes") {
      if (!productsLoaded) {
        document.getElementById('productVerificationModule').style.display = 'block';
        itemContainer.style.display = 'none';
      } else {
        document.getElementById('productVerificationModule').style.display = 'none';
        itemContainer.style.display = 'block';
      }
    } else {
      document.getElementById('productVerificationModule').style.display = 'none';
      itemContainer.innerHTML = "";
      itemContainer.style.display = "none";
    }
  };


  // ===============================
  // 📧 STEP 1: SEND CODE
  // ===============================
  productSendCodeBtn.addEventListener('click', async function() {
    console.log('Send code button clicked');
    const email = productUserEmailInput.value.trim();
    
    if (!email) {
      showProductMessage(productEmailMessage, 'Please enter your email address', 'error');
      return;
    }

    if (!isValidEmail(email)) {
      showProductMessage(productEmailMessage, 'Please enter a valid email address', 'error');
      return;
    }

    productSendCodeBtn.disabled = true;
    productSendCodeBtn.textContent = 'Sending...';
    
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
        currentProductEmail = email;
        console.log('Code sent, email stored:', currentProductEmail);
        showProductMessage(productEmailMessage, '✓ Code sent successfully! Check your email.', 'success');
        
        setTimeout(() => {
          productCodeStep.style.display = 'block';
          productVerificationCodeInput.focus();
        }, 1000);
        
        productUserEmailInput.disabled = true;
        productSendCodeBtn.disabled = true;
        productSendCodeBtn.textContent = 'Code Sent';
        
      } else {
        showProductMessage(productEmailMessage, '✗ ' + data.message, 'error');
        productSendCodeBtn.disabled = false;
        productSendCodeBtn.textContent = 'Send Code';
      }

    } catch (error) {
      showProductMessage(productEmailMessage, '✗ Network error. Please try again.', 'error');
      productSendCodeBtn.disabled = false;
      productSendCodeBtn.textContent = 'Send Code';
      console.error('Error:', error);
    }
  });


  // ===============================
  // 🔐 STEP 2: VERIFY CODE & LOAD PRODUCTS + CLIENTS
  // ===============================
  productVerifyCodeBtn.addEventListener('click', async function() {
    console.log('Verify code button clicked');
    const code = productVerificationCodeInput.value.trim();
    
    if (!code || code.length !== 6) {
      showProductMessage(productCodeMessage, 'Please enter the 6-digit code', 'error');
      return;
    }

    productVerifyCodeBtn.disabled = true;
    productVerifyCodeBtn.textContent = 'Verifying...';
    
    try {
      const [productsResponse, clientsResponse] = await Promise.all([
        fetch(`${API_BASE_URL}/client-access/get-products`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ 
            email: currentProductEmail,
            code: code 
          })
        }),
        fetch(`${API_BASE_URL}/client-access/verify-code`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ 
            email: currentProductEmail,
            code: code 
          })
        })
      ]);

      const productsData = await productsResponse.json();
      const clientsData = await clientsResponse.json();

      if (productsData.success && clientsData.success) {
        showProductMessage(productCodeMessage, '✓ Code verified! Loading products and clients...', 'success');
        
        PRODUCTS = productsData.products.map(p => ({
          id: p.id,
          code: p.itemcode,
          desc: p.description,
          pack: p.pack,
          price: parseFloat(p.unit_price.replace(/,/g, '')) || 0
        }));
        
        CLIENTS = clientsData.clients;
        currentProductCode = code;
        productsLoaded = true;
        
        console.log('Data loaded:', {
          products: PRODUCTS.length,
          clients: CLIENTS.length,
          code: currentProductCode
        });
        
        loadClientsDropdown();
        
        setTimeout(() => {
          clientSelectionProducts.style.display = 'block';
          productVerificationCodeInput.disabled = true;
          productVerifyCodeBtn.disabled = true;
          productVerifyCodeBtn.textContent = 'Verified';
        }, 1000);
        
      } else {
        const errorMessage = productsData.message || clientsData.message || 'Verification failed';
        showProductMessage(productCodeMessage, '✗ ' + errorMessage, 'error');
        productVerifyCodeBtn.disabled = false;
        productVerifyCodeBtn.textContent = 'Verify Code';
      }

    } catch (error) {
      showProductMessage(productCodeMessage, '✗ Network error. Please try again.', 'error');
      productVerifyCodeBtn.disabled = false;
      productVerifyCodeBtn.textContent = 'Verify Code';
      console.error('Error:', error);
    }
  });


  // ===============================
  // LOAD CLIENTS DROPDOWN
  // ===============================
  function loadClientsDropdown() {
    selectClientProducts.innerHTML = '<option value="">-- Select a client --</option>';
    
    CLIENTS.forEach((client) => {
      const option = document.createElement('option');
      option.value = client.address_id;
      option.textContent = `${client.name} - ${client.company}`;
      option.dataset.client = JSON.stringify(client);
      selectClientProducts.appendChild(option);
    });
  }


  // ===============================
  // CLIENT SELECTION HANDLER
  // ===============================
  selectClientProducts.addEventListener('change', function() {
    console.log('Client selected:', this.value);
    if (this.value !== '') {
      const clientData = JSON.parse(this.options[this.selectedIndex].dataset.client);
      selectedClientAddressId = parseInt(this.value);
      
      console.log('Selected address_id:', selectedClientAddressId);
      
      clientDetails.innerHTML = `
        <strong>${clientData.name}</strong><br>
        Company: ${clientData.company}<br>
        Address: ${clientData.address}
      `;
      clientInfoDisplay.style.display = 'block';
      
      document.getElementById('productVerificationModule').style.display = 'none';
      itemContainer.style.display = 'block';
      loadItemsModule();
      
    } else {
      selectedClientAddressId = null;
      clientInfoDisplay.style.display = 'none';
      itemContainer.style.display = 'none';
    }
  });


  // ===============================
  // LOAD MODULE WITH PRODUCTS
  // ===============================
  function loadItemsModule() {
    itemContainer.innerHTML = `
      <div class="items-category expanded">
        <div class="items-header" onclick="this.parentElement.classList.toggle('expanded')">
          ORDER ITEMS <span class="toggle-icon">▼</span>
        </div>
        <button type="button" class="add-row-btn" onclick="addItemRow()">+ Add Row</button>
        <table class="items-table">
          <thead>
            <tr>
              <th>Item Code</th>
              <th>Description</th>
              <th>Pack/Case</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>Total</th>
              <th>Remove</th>
            </tr>
          </thead>
          <tbody id="itemsBody">
            ${PRODUCTS.map(p => createRowHTML(p, false)).join("")}
          </tbody>
        </table>
        ${buildTotalsSection()}
      </div>
    `;
  }

  function buildTotalsSection() {
    return `
      <div id="invoiceTotals" style="margin-top:30px; display:flex; justify-content:flex-end;">
        <table style="width:350px; border-collapse:collapse; background:white;">
          <thead>
            <tr><th colspan="2" style="background:#c00; padding:12px; color:white;">Invoice Summary</th></tr>
          </thead>
          <tr>
            <td style="padding:10px; font-weight:bold;">Subtotal</td>
            <td><input id="subtotalAmount" class="readonly" readonly style="width:100%; text-align:right;" value="$0.00"></td>
          </tr>
          <tr>
            <td style="padding:10px; font-weight:bold;">Taxes (8.25%)</td>
            <td><input id="taxAmount" class="readonly" readonly style="width:100%; text-align:right;" value="$0.00"></td>
          </tr>
          <tr>
            <td style="padding:10px; font-weight:bold;">Grand Total</td>
            <td><input id="grandTotal" class="readonly" readonly style="width:100%; text-align:right;" value="$0.00"></td>
          </tr>
        </table>
      </div>
    `;
  }

  function createRowHTML(p, editable = false) {
    const slug = slugify(p.code + "_" + Math.random());
    return `
      <tr id="${slug}" data-product-id="${p.id || ''}">
        <td><input ${editable ? "" : "readonly class='readonly'"} value="${p.code}"></td>
        <td><input ${editable ? "" : "readonly class='readonly'"} value="${p.desc}"></td>
        <td><input ${editable ? "" : "readonly class='readonly'"} value="${p.pack}"></td>
        <td><input type="number" name="qty_${slug}" min="0" value="0" oninput="updateItemTotal('${slug}')"></td>
        <td><input type="number" step="0.01" name="price_${slug}" value="${p.price}" ${editable ? "" : "readonly class='readonly'"} oninput="updateItemTotal('${slug}')"></td>
        <td><input readonly class="readonly" id="total_${slug}" value="$0.00"></td>
        <td><button type="button" class="remove-btn" onclick="removeRow('${slug}')">X</button></td>
      </tr>
    `;
  }

  window.addItemRow = function () {
    document.getElementById("itemsBody").insertAdjacentHTML("beforeend", createRowHTML({code:"",desc:"",pack:"",price:0}, true));
  };

  window.updateItemTotal = function (slug) {
    const qty = parseFloat(document.querySelector(`[name="qty_${slug}"]`).value) || 0;
    const price = parseFloat(document.querySelector(`[name="price_${slug}"]`).value) || 0;
    document.getElementById(`total_${slug}`).value = "$" + (qty * price).toFixed(2);
    updateInvoiceTotals();
  };

  window.removeRow = function (slug) {
    document.getElementById(slug).remove();
    updateInvoiceTotals();
  };

  function updateInvoiceTotals() {
    let subtotal = 0;
    document.querySelectorAll("td input[id^='total_']").forEach(rowTotal => {
      subtotal += parseFloat(rowTotal.value.replace("$","")) || 0;
    });
    document.getElementById("subtotalAmount").value = "$" + subtotal.toFixed(2);
    let tax = subtotal * 0.0825;
    document.getElementById("taxAmount").value = "$" + tax.toFixed(2);
    document.getElementById("grandTotal").value = "$" + (subtotal + tax).toFixed(2);
  }

  function slugify(text) {
    return text.toLowerCase().replace(/[^a-z0-9]+/g, "_");
  }

  function showProductMessage(element, text, type) {
    element.textContent = text;
    element.style.color = type === 'success' ? '#10b981' : '#ef4444';
    element.style.fontWeight = '600';
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  productVerificationCodeInput.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
  });

}); // END DOMContentLoaded


// ===============================
// 💾 SAVE ORDER TO DATABASE (FUERA DEL DOMContentLoaded)
// ===============================
async function saveOrderToDatabase() {
  console.log('=== SAVE ORDER TO DATABASE - START ===');
  console.log('Global variables:', {
    selectedClientAddressId,
    currentProductEmail,
    currentProductCode,
    API_BASE_URL
  });
  
  if (!selectedClientAddressId) {
    console.error('❌ No client selected');
    alert('Please select a client first');
    return false;
  }

  const items = [];
  const rows = document.querySelectorAll("#itemsBody tr");
  console.log('Total rows found:', rows.length);
  
  rows.forEach((row, index) => {
    const productId = row.dataset.productId;
    const cells = row.querySelectorAll("input");
    const itemCode = cells[0].value;
    const qty = parseFloat(cells[3].value) || 0;
    const total = parseFloat(cells[5].value.replace("$", "")) || 0;

    console.log(`Row ${index}:`, { productId, itemCode, qty, total });

    if (qty > 0 && productId) {
      items.push({
        product_id: parseInt(productId),
        qty: qty,
        total: total
      });
      console.log(`✓ Item ${index} added`);
    }
  });

  console.log('Total items to save:', items.length);

  if (items.length === 0) {
    console.error('❌ No items');
    alert('Please add at least one item with quantity greater than 0');
    return false;
  }

  const payload = {
    email: currentProductEmail,
    code: currentProductCode,
    address_id: selectedClientAddressId,
    items: items
  };

  console.log('📤 Payload:', JSON.stringify(payload, null, 2));
  console.log('📤 URL:', `${API_BASE_URL}/client-access/save-order`);

  try {
    const response = await fetch(`${API_BASE_URL}/client-access/save-order`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    console.log('📥 Response status:', response.status);
    const data = await response.json();
    console.log('📥 Response data:', data);

    if (data.success) {
      console.log('✅ SUCCESS');
      alert('Order saved successfully!');
      return true;
    } else {
      console.error('❌ FAILED:', data.message);
      alert('Failed to save order: ' + data.message);
      return false;
    }

  } catch (error) {
    console.error('❌ EXCEPTION:', error);
    alert('Network error. Failed to save order.');
    return false;
  }
}


// =================================
// HANDLE FORM SUBMIT
// =================================
async function handleFormSubmit(event) {
  console.log('=== FORM SUBMIT ===');
  event.preventDefault();
  
  const includeItems = document.getElementById('includeItems').value;
  console.log('includeItems value:', includeItems);
  
  if (includeItems === 'Yes') {
    console.log('Calling saveOrderToDatabase...');
    const saved = await saveOrderToDatabase();
    console.log('Save result:', saved);
    
    if (!saved) {
      console.log('Save failed, stopping');
      return false;
    }
    console.log('Save successful, continuing...');
  } else {
    console.log('No items to save');
  }
  
  prepareInvoiceDataForSubmit();
  console.log('Submitting form...');
  event.target.submit();
}

function prepareInvoiceDataForSubmit() {
  const form = document.querySelector("form");
  document.querySelectorAll(".dynamic-hidden").forEach(e => e.remove());

  document.querySelectorAll("#itemsBody tr").forEach(row => {
    const cells = row.querySelectorAll("input");
    addHidden(form, "item_code[]", cells[0].value);
    addHidden(form, "description[]", cells[1].value);
    addHidden(form, "packcase[]", cells[2].value);
    addHidden(form, "qty[]", cells[3].value);
    addHidden(form, "unit_price[]", cells[4].value);
    addHidden(form, "total[]", cells[5].value.replace("$",""));
  });

  addHidden(form, "invoice_total", document.getElementById("subtotalAmount").value.replace("$",""));
  addHidden(form, "invoice_tax", document.getElementById("taxAmount").value.replace("$",""));
  addHidden(form, "invoice_grand_total", document.getElementById("grandTotal").value.replace("$",""));
}

function addHidden(form, name, value) {
  const input = document.createElement("input");
  input.type = "hidden";
  input.name = name;
  input.value = value;
  input.classList.add("dynamic-hidden");
  form.appendChild(input);
}
</script>