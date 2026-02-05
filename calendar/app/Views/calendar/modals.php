<!-- Event Modal - BASIC -->
<!-- Simple create/edit form. Fields: title, client, location, date, description, status. -->
<div class="modal" id="eventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">New Event</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>

        <form id="eventForm" method="POST" action="actions/event/save.php">
            <input type="hidden" id="eventId" name="event_id">

            <div class="modal-body">
                <div class="form-group">
                    <label for="eventTitle">Title *</label>
                    <input type="text" id="eventTitle" name="title" required placeholder="Event title">
                </div>

                <div class="form-group">
                    <label for="eventClient">Client</label>
                    <input type="text" id="eventClient" name="client" placeholder="Client name">
                </div>

                <div class="form-group">
                    <label for="eventLocation">Location</label>
                    <input type="text" id="eventLocation" name="location" placeholder="Location / Address">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="eventStartDate">Date *</label>
                        <input type="date" id="eventStartDate" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="eventStatus">Status</label>
                        <select id="eventStatus" name="status">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="eventDescription">Description</label>
                    <textarea id="eventDescription" name="description" rows="3" placeholder="Notes..."></textarea>
                </div>

                <!-- Hidden fields with sensible defaults -->
                <input type="hidden" name="priority" value="normal">
                <input type="hidden" id="eventCategoryId" name="category_id" value="">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn-danger" id="deleteBtn" onclick="deleteEvent()" style="display:none">Delete</button>
                <button type="submit" class="btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
