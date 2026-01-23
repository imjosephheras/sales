<!-- ============================================================
     EVENT MODAL - Create/Edit Events
     ============================================================ -->
<div class="modal" id="eventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">New Event</h2>
            <button class="modal-close" onclick="closeModal('eventModal')">&times;</button>
        </div>
        
        <form id="eventForm" method="POST" action="actions/event/save.php">
            <input type="hidden" id="eventId" name="event_id">
            
            <div class="form-group">
        <label for="eventTitle">Título / Nomenclatura *</label>
        <input type="text" 
               id="eventTitle" 
               name="title" 
               placeholder="Ej: JWO-H1800112302025-03-01"
               required
               class="form-control">
        <small class="form-hint">
            Este será el identificador único del evento
        </small>
          </div>
            <div class="modal-body">
                <!-- Title -->
                <div class="form-group">
                    <label for="eventTitle">Title / Nomenclature *</label>
                    <input type="text" 
                           id="eventTitle" 
                           name="title" 
                           placeholder="JWO-H100012302025-03-01"
                           required>
                    <small class="help-text">Format: TYPE-CODE(MMDDYYYY)-FREQ-DUR</small>
                </div>
                
                <!-- Category -->
                <div class="form-group">
                    <label for="eventCategory">Work Type / Category *</label>
                    <select id="eventCategory" name="category_id" required>
                        <option value="">Select work type...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>">
                                <?= e($cat['icon']) ?> <?= e($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Client -->
                <div class="form-group">
                    <label for="eventClient">Client</label>
                    <input type="text" 
                           id="eventClient" 
                           name="client" 
                           placeholder="Client name"
                           list="clientList">
                    <datalist id="clientList">
                        <!-- Will be populated dynamically -->
                    </datalist>
                </div>

                <!-- Location -->
                <div class="form-group">
                    <label for="eventLocation">Location</label>
                    <input type="text" 
                           id="eventLocation" 
                           name="location" 
                           placeholder="Address or place">
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="eventDescription">Description</label>
                    <textarea id="eventDescription" 
                              name="description" 
                              rows="3" 
                              placeholder="Add details..."></textarea>
                </div>

                <!-- Date & Time -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="eventStartDate">Start Date *</label>
                        <input type="date" id="eventStartDate" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="eventEndDate">End Date *</label>
                        <input type="date" id="eventEndDate" name="end_date" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="eventStartTime">Start Time</label>
                        <input type="time" id="eventStartTime" name="start_time">
                    </div>
                    
                    <div class="form-group">
                        <label for="eventEndTime">End Time</label>
                        <input type="time" id="eventEndTime" name="end_time">
                    </div>
                </div>
                
                <!-- All Day Checkbox -->
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="isAllDay" name="is_all_day" onchange="toggleAllDay()">
                        <span>All day event</span>
                    </label>
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
                
                <!-- Scheduling Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="documentDate">Document Date</label>
                        <input type="date" id="documentDate" name="document_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="executionDate">Execution Date</label>
                        <input type="date" id="executionDate" name="execution_date">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="frequencyMonths">Frequency (Months)</label>
                        <select id="frequencyMonths" name="frequency_months">
                            <option value="">No recurrence</option>
                            <option value="1">1 - Monthly</option>
                            <option value="2">2 - Bimonthly</option>
                            <option value="3">3 - Quarterly</option>
                            <option value="6">6 - Semi-annual</option>
                            <option value="12">12 - Annual</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="frequencyYears">Duration (years)</label>
                        <select id="frequencyYears" name="frequency_years">
                            <option value="1">1 year</option>
                            <option value="2">2 years</option>
                            <option value="3">3 years</option>
                            <option value="5">5 years</option>
                            <option value="10">10 years</option>
                        </select>
                    </div>
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