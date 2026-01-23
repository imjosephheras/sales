<!-- ========================================= -->
<!-- ✏️ EDITOR PANEL - Formulario de Edición -->
<!-- ========================================= -->

<div class="editor-container">
    
    <!-- Estado inicial: sin solicitud seleccionada -->
    <div id="no-selection-state" class="empty-state">
        <i class="fas fa-hand-pointer"></i>
        <h2>Select a request to start</h2>
        <p>Choose a request from the inbox to edit and preview the document</p>
    </div>

    <!-- Estado activo: solicitud seleccionada -->
    <div id="editor-active-state" style="display: none;">
        
        <!-- 📄 Header del documento -->
        <div class="document-header">
            <div class="doc-info">
                <h2 id="doc-title">Loading...</h2>
                <span id="doc-number" class="doc-number-badge"></span>
            </div>
            <div class="doc-actions">
                <button id="btn-save" class="btn btn-secondary">
                    <i class="fas fa-save"></i> Save
                </button>
                <button id="btn-mark-ready" class="btn btn-success">
                    <i class="fas fa-check"></i> Mark Ready
                </button>
                <button id="btn-download-pdf" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download PDF
                </button>
            </div>
        </div>

        <!-- ========================================= -->
        <!-- FORMULARIO DE EDICIÓN -->
        <!-- ========================================= -->
        <div id="editor-form-section" class="editor-form-section">
            <div class="editor-form-container">
                <form id="editor-form">
                    
                    <!-- Hidden ID -->
                    <input type="hidden" id="request_id" name="request_id">

                    <!-- 🔹 SECTION: Request Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-clipboard-list"></i> Request Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Service Type</label>
                                <select name="Service_Type" id="Service_Type">
                                    <option value="Janitorial">Janitorial</option>
                                    <option value="Hospitality">Hospitality</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Request Type</label>
                                <select name="Request_Type" id="Request_Type">
                                    <option value="Quote">Quote</option>
                                    <option value="JWO">JWO</option>
                                    <option value="Proposal">Proposal</option>
                                    <option value="Contract">Contract</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="Priority" id="Priority">
                                    <option value="Standard">Standard</option>
                                    <option value="Rush">Rush</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Requested Service</label>
                                <input type="text" name="Requested_Service" id="Requested_Service">
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 SECTION: Client Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-building"></i> Client Information</h3>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label>Business Name *</label>
                                <input type="text" name="Business_Name" id="Business_Name" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Contact Name</label>
                                <input type="text" name="Contact_Name" id="Contact_Name">
                            </div>
                            <div class="form-group">
                                <label>Contact Email</label>
                                <input type="email" name="Contact_Email" id="Contact_Email">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Contact Phone</label>
                                <input type="tel" name="Contact_Phone" id="Contact_Phone">
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="Address" id="Address">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="City" id="City">
                            </div>
                            <div class="form-group">
                                <label>State</label>
                                <input type="text" name="State" id="State">
                            </div>
                            <div class="form-group">
                                <label>Zip Code</label>
                                <input type="text" name="Zip_Code" id="Zip_Code">
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 SECTION: Operational Details -->
                    <div class="form-section">
                        <h3><i class="fas fa-cogs"></i> Operational Details</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Site Visit Conducted</label>
                                <select name="Site_Visit_Conducted" id="Site_Visit_Conducted">
                                    <option value="">-- Select --</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Invoice Frequency</label>
                                <select name="Invoice_Frequency" id="Invoice_Frequency">
                                    <option value="15">Every 15 days</option>
                                    <option value="30">Every 30 days</option>
                                    <option value="50_deposit">50% Deposit / 50% Completion</option>
                                    <option value="completion">Payment Upon Completion</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Contract Duration</label>
                                <select name="Contract_Duration" id="Contract_Duration">
                                    <option value="not_applicable">Not applicable</option>
                                    <option value="6_months">6 Months</option>
                                    <option value="1_year">1 Year</option>
                                    <option value="1_5_years">1.5 Years</option>
                                    <option value="2_years">2 Years</option>
                                    <option value="3_years">3 Years</option>
                                    <option value="4_years">4 Years</option>
                                    <option value="5_years">5 Years</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 SECTION: Economic Details -->
                    <div class="form-section">
                        <h3><i class="fas fa-dollar-sign"></i> Economic Details</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Total Price</label>
                                <input type="number" step="0.01" name="Total_Price" id="Total_Price">
                            </div>
                            <div class="form-group">
                                <label>Currency</label>
                                <select name="Currency" id="Currency">
                                    <option value="USD">USD</option>
                                    <option value="MXN">MXN</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 SECTION: Contract Specific (solo para Contract) -->
                    <div class="form-section" id="contract-specific-section" style="display: none;">
                        <h3><i class="fas fa-file-contract"></i> Contract Specific Info</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Inflation Adjustment</label>
                                <input type="text" name="inflationAdjustment" id="inflationAdjustment">
                            </div>
                            <div class="form-group">
                                <label>Total Area (sq ft)</label>
                                <input type="text" name="totalArea" id="totalArea">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label>Buildings Included</label>
                                <input type="text" name="buildingsIncluded" id="buildingsIncluded">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date of Services</label>
                                <input type="date" name="startDateServices" id="startDateServices">
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 SECTION: Observations -->
                    <div class="form-section">
                        <h3><i class="fas fa-sticky-note"></i> Observations</h3>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label>Site Observation</label>
                                <textarea name="Site_Observation" id="Site_Observation" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label>Additional Comments</label>
                                <textarea name="Additional_Comments" id="Additional_Comments" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 SECTION: Scope of Work -->
                    <div class="form-section">
                        <h3><i class="fas fa-tasks"></i> Scope of Work</h3>
                        <div id="scope-display" class="scope-display">
                            <!-- Se carga dinámicamente -->
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>

</div>