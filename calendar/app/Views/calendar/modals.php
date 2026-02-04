<!-- ============================================================
     EVENT MODAL - Create/Edit Events (SIMPLIFIED)
     Uses Request Form data automatically
     ============================================================ -->
<div class="modal" id="eventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">New Event</h2>
            <button class="modal-close" onclick="closeModal('eventModal')">&times;</button>
        </div>

        <form id="eventForm" method="POST" action="actions/event/save.php">
            <input type="hidden" id="eventId" name="event_id">
            <input type="hidden" id="requestId" name="request_id">
            <input type="hidden" id="requestNumber" name="request_number">

            <div class="modal-body">
                <!-- Title / Nomenclature (from Request Form) -->
                <div class="form-group">
                    <label for="eventTitle">Title / Nomenclature *</label>
                    <input type="text"
                           id="eventTitle"
                           name="title"
                           placeholder="HJ-10000112222026"
                           required
                           readonly>
                    <small class="help-text">Identificador único del evento (viene del Request Form)</small>
                </div>

                <!-- Category / Work Type (informativo) -->
                <div class="form-group">
                    <label for="eventCategory">Work Type / Category</label>
                    <select id="eventCategory" name="category_id">
                        <option value="">Select work type...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>">
                                <?= e($cat['icon']) ?> <?= e($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="help-text">Solo informativo, no afecta el Title</small>
                </div>

                <!-- Company Name (auto desde Request Form) -->
                <div class="form-group">
                    <label for="eventCompany">Company Name</label>
                    <input type="text"
                           id="eventCompany"
                           name="company"
                           placeholder="Cargado desde Request Form"
                           readonly>
                </div>

                <!-- Client Name (auto desde Request Form) -->
                <div class="form-group">
                    <label for="eventClient">Client Name</label>
                    <input type="text"
                           id="eventClient"
                           name="client"
                           placeholder="Cargado desde Request Form"
                           readonly>
                </div>

                <!-- Business Address (auto desde Request Form) -->
                <div class="form-group">
                    <label for="eventLocation">Business Address</label>
                    <input type="text"
                           id="eventLocation"
                           name="location"
                           placeholder="Cargado desde Request Form"
                           readonly>
                </div>

                <!-- WORK DATE - Fecha principal del evento -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="workDate">Work Date (Execution Date) *</label>
                        <input type="date" id="workDate" name="work_date" required>
                        <small class="help-text">Fecha en que se agenda el evento</small>
                    </div>

                    <div class="form-group">
                        <label for="documentDate">Document Date</label>
                        <input type="date" id="documentDate" name="document_date" readonly>
                        <small class="help-text">Fecha de creación del work order (informativo)</small>
                    </div>
                </div>

                <!-- Description (ÚNICO campo manual adicional) -->
                <div class="form-group">
                    <label for="eventDescription">Description</label>
                    <textarea id="eventDescription"
                              name="description"
                              rows="3"
                              placeholder="Notas internas del evento..."></textarea>
                    <small class="help-text">Campo adicional para notas internas</small>
                </div>

                <!-- Status and Priority -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="eventStatus">Status</label>
                        <select id="eventStatus" name="status">
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="eventPriority">Priority</label>
                        <select id="eventPriority" name="priority">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>

                <!-- Frequency & Duration (manual adjustment, NO AUTO recurrence) -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="frequencyMonths">Frequency (Months)</label>
                        <select id="frequencyMonths" name="frequency_months">
                            <option value="">No recurrence</option>
                            <option value="1">1 - Monthly</option>
                            <option value="2">2 - Bimonthly</option>
                            <option value="3">3 - Quarterly</option>
                            <option value="4">4 - Every 4 months</option>
                            <option value="6">6 - Semi-annual</option>
                            <option value="12">12 - Annual</option>
                        </select>
                        <small class="help-text">Ajuste manual después de crear</small>
                    </div>

                    <div class="form-group">
                        <label for="frequencyYears">Duration (Years)</label>
                        <select id="frequencyYears" name="frequency_years">
                            <option value="1">1 year</option>
                            <option value="2">2 years</option>
                            <option value="3">3 years</option>
                            <option value="5">5 years</option>
                            <option value="10">10 years</option>
                        </select>
                        <small class="help-text">Ajuste manual después de crear</small>
                    </div>
                </div>

                <div class="form-hint-box">
                    <strong>Nota:</strong> Frequency y Duration no crean recurrencias automáticas.
                    El evento siempre se crea usando la fecha del Work Date.
                    Las recurrencias se ajustan manualmente después.
                </div>

            </div><!-- Close modal-body -->

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('eventModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal" id="eventDetailModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="detailModalTitle">Event Details</h2>
            <button class="modal-close" onclick="closeModal('eventDetailModal')">&times;</button>
        </div>

        <div class="modal-body" id="eventDetailContent">
            <!-- Event details loaded via JavaScript -->
        </div>
    </div>
</div>

<style>
.form-hint-box {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: 6px;
    padding: 12px;
    margin-top: 15px;
    font-size: 0.875rem;
    color: #92400e;
}

[data-theme="dark"] .form-hint-box {
    background: #422006;
    border-color: #d97706;
    color: #fef3c7;
}

.help-text {
    font-size: 0.75rem;
    color: #78716c;
    margin-top: 4px;
}

[data-theme="dark"] .help-text {
    color: #a8a29e;
}

input[readonly] {
    background-color: #f5f5f4;
    cursor: not-allowed;
}

[data-theme="dark"] input[readonly] {
    background-color: #292524;
}
</style>
