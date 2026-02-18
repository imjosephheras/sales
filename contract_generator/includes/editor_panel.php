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
                <button id="btn-mark-completed" class="btn btn-completed" style="display: none;">
                    <i class="fas fa-check-double"></i> Mark Completed
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
                                    <option value="">-- Select --</option>
                                    <option value="Janitorial">Janitorial</option>
                                    <option value="Hospitality">Hospitality</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Request Type</label>
                                <select name="Request_Type" id="Request_Type">
                                    <option value="">-- Select --</option>
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
                                    <option value="">-- Select --</option>
                                    <option value="Standard">Standard</option>
                                    <option value="Rush">Rush</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Requested Service</label>
                                <select name="Requested_Service" id="Requested_Service">
                                    <option value="">-- Select --</option>
                                    <!-- Janitorial Options -->
                                    <option value="Restaurant">Restaurant</option>
                                    <option value="Schools and Universities">Schools and Universities</option>
                                    <option value="Corporate Offices">Corporate Offices</option>
                                    <option value="Airports">Airports</option>
                                    <option value="Churches">Churches</option>
                                    <option value="Stadiums and Sports Arenas">Stadiums and Sports Arenas</option>
                                    <option value="Warehouses and Industrial Facilities">Warehouses and Industrial Facilities</option>
                                    <!-- Hospitality Options -->
                                    <option value="Kitchen Cleaning & Hood Vent">Kitchen Cleaning & Hood Vent</option>
                                    <option value="Staff">Staff</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Seller / Sales Person</label>
                                <select name="Seller" id="Seller">
                                    <option value="">-- Select --</option>
                                    <option value="Kenny Howe">Kenny Howe</option>
                                    <option value="Norma Bustos">Norma Bustos</option>
                                    <option value="Sandra Hernandez">Sandra Hernandez</option>
                                    <option value="Miguel Palma">Miguel Palma</option>
                                    <option value="Rafael Perez JR">Rafael Perez JR</option>
                                    <option value="Patty Perez">Patty Perez</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 SECTION: Client Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-building"></i> Client Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Company Name *</label>
                                <input type="text" name="Company_Name" id="Company_Name" required>
                            </div>
                            <div class="form-group">
                                <label>Is New Client?</label>
                                <select name="Is_New_Client" id="Is_New_Client">
                                    <option value="">-- Select --</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Contact Name</label>
                                <input type="text" name="Client_Name" id="client_name">
                            </div>
                            <div class="form-group">
                                <label>Contact Title</label>
                                <input type="text" name="Client_Title" id="Client_Title">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Contact Email</label>
                                <input type="email" name="Email" id="Email">
                            </div>
                            <div class="form-group">
                                <label>Contact Phone</label>
                                <input type="tel" name="Number_Phone" id="Number_Phone">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label>Company Address</label>
                                <input type="text" name="Company_Address" id="Company_Address">
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
                                    <option value="">-- Select --</option>
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

                    <!-- 🔹 SECTION 18: Janitorial Services (conditionally shown based on Work Order) -->
                    <div class="form-section" id="janitorial-section" style="display: none;">
                        <h3><i class="fas fa-broom"></i> 18. Janitorial Services</h3>
                        <div class="editor-services-table-wrapper">
                            <table class="editor-services-table" id="janitorial-table">
                                <thead>
                                    <tr>
                                        <th>Type of Service</th>
                                        <th>Service Time</th>
                                        <th>Frequency</th>
                                        <th>Description</th>
                                        <th>Subtotal</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="janitorial-table-body">
                                    <!-- Dynamic rows -->
                                </tbody>
                            </table>
                            <button type="button" class="btn-add-service-row" id="btn-add-janitorial" title="Add row">
                                <i class="fas fa-plus"></i> Add Service
                            </button>
                        </div>
                        <div class="form-row" style="margin-top: 1rem;">
                            <div class="form-group">
                                <label>Total</label>
                                <input type="text" name="total18" id="total18" class="totals-field" readonly>
                            </div>
                            <div class="form-group">
                                <label>Taxes (8.25%)</label>
                                <input type="text" name="taxes18" id="taxes18" class="totals-field" readonly>
                            </div>
                            <div class="form-group">
                                <label>Grand Total</label>
                                <input type="text" name="grand18" id="grand18" class="totals-field grand-total-field" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 SECTION 19: Hoodvent & Kitchen Cleaning (conditionally shown based on Work Order) -->
                    <div class="form-section" id="kitchen-section" style="display: none;">
                        <h3><i class="fas fa-utensils"></i> 19. Hoodvent & Kitchen Cleaning</h3>
                        <div class="editor-services-table-wrapper">
                            <table class="editor-services-table" id="kitchen-table">
                                <thead>
                                    <tr>
                                        <th>Type of Service</th>
                                        <th>Service Time</th>
                                        <th>Frequency</th>
                                        <th>Description</th>
                                        <th>Subtotal</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="kitchen-table-body">
                                    <!-- Dynamic rows -->
                                </tbody>
                            </table>
                            <button type="button" class="btn-add-service-row" id="btn-add-kitchen" title="Add row">
                                <i class="fas fa-plus"></i> Add Service
                            </button>
                        </div>
                        <div class="form-row" style="margin-top: 1rem;">
                            <div class="form-group">
                                <label>Total</label>
                                <input type="text" name="total19" id="total19" class="totals-field" readonly>
                            </div>
                            <div class="form-group">
                                <label>Taxes (8.25%)</label>
                                <input type="text" name="taxes19" id="taxes19" class="totals-field" readonly>
                            </div>
                            <div class="form-group">
                                <label>Grand Total</label>
                                <input type="text" name="grand19" id="grand19" class="totals-field grand-total-field" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 SECTION 20: Include Staff? (conditionally shown based on Work Order) -->
                    <div class="form-section" id="staff-section" style="display: none;">
                        <h3><i class="fas fa-users"></i> 20. Include Staff?</h3>
                        <div class="editor-services-table-wrapper">
                            <table class="editor-services-table" id="staff-table">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Base Rate ($)</th>
                                        <th>% Increase</th>
                                        <th>Bill Rate ($)</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="staff-table-body">
                                    <!-- Dynamic rows -->
                                </tbody>
                            </table>
                            <button type="button" class="btn-add-service-row" id="btn-add-staff" title="Add position">
                                <i class="fas fa-plus"></i> Add Position
                            </button>
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

                </form>
            </div>
        </div>

    </div>

</div>