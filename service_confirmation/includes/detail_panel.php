<!-- Service Detail Panel - Task Tracking -->
<div class="panel-header">
    <h2><i class="fas fa-clipboard-check"></i> Task Tracking</h2>
</div>

<div class="panel-content" id="detail-content">
    <div class="empty-state">
        <i class="fas fa-hand-pointer"></i>
        <p>Select a service to view and track tasks</p>
    </div>
</div>

<template id="detail-template">
    <div class="detail-view">
        <!-- Header with Nomenclature -->
        <div class="detail-header">
            <span class="nomenclature-badge"></span>
            <div class="progress-indicator">
                <span class="progress-text">0/9 tasks</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Task Tracking Checklist -->
        <div class="detail-section task-tracking-section">
            <h3><i class="fas fa-tasks"></i> Task Checklist</h3>
            <p class="section-subtitle">Mark tasks as they are completed</p>

            <div class="task-checklist" id="taskChecklist">
                <!-- Site Visit -->
                <label class="task-item">
                    <input type="checkbox" name="task_site_visit" data-task="site_visit">
                    <span class="task-checkbox"></span>
                    <span class="task-label">
                        <i class="fas fa-map-marker-alt"></i>
                        Site Visit Done
                    </span>
                </label>

                <!-- Quote Sent -->
                <label class="task-item">
                    <input type="checkbox" name="task_quote_sent" data-task="quote_sent">
                    <span class="task-checkbox"></span>
                    <span class="task-label">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Quote Sent
                    </span>
                </label>

                <!-- Contract Signed -->
                <label class="task-item">
                    <input type="checkbox" name="task_contract_signed" data-task="contract_signed">
                    <span class="task-checkbox"></span>
                    <span class="task-label">
                        <i class="fas fa-file-signature"></i>
                        Contract Signed
                    </span>
                </label>

                <!-- Staff Assigned -->
                <label class="task-item">
                    <input type="checkbox" name="task_staff_assigned" data-task="staff_assigned">
                    <span class="task-checkbox"></span>
                    <span class="task-label">
                        <i class="fas fa-users"></i>
                        Staff Assigned
                    </span>
                </label>

                <!-- Equipment Ready -->
                <label class="task-item">
                    <input type="checkbox" name="task_equipment_ready" data-task="equipment_ready">
                    <span class="task-checkbox"></span>
                    <span class="task-label">
                        <i class="fas fa-tools"></i>
                        Equipment Ready
                    </span>
                </label>

                <!-- Work Started -->
                <label class="task-item">
                    <input type="checkbox" name="task_work_started" data-task="work_started">
                    <span class="task-checkbox"></span>
                    <span class="task-label">
                        <i class="fas fa-play-circle"></i>
                        Work Started
                    </span>
                </label>

                <!-- Work Completed -->
                <label class="task-item">
                    <input type="checkbox" name="task_work_completed" data-task="work_completed">
                    <span class="task-checkbox"></span>
                    <span class="task-label">
                        <i class="fas fa-check-circle"></i>
                        Work Completed
                    </span>
                </label>

                <!-- Client Approved -->
                <label class="task-item">
                    <input type="checkbox" name="task_client_approved" data-task="client_approved">
                    <span class="task-checkbox"></span>
                    <span class="task-label">
                        <i class="fas fa-thumbs-up"></i>
                        Client Approved
                    </span>
                </label>

                <!-- Invoice Ready -->
                <label class="task-item">
                    <input type="checkbox" name="task_invoice_ready" data-task="invoice_ready">
                    <span class="task-checkbox"></span>
                    <span class="task-label">
                        <i class="fas fa-receipt"></i>
                        Ready to Invoice
                    </span>
                </label>
            </div>

            <!-- Auto-save indicator -->
            <div class="save-indicator" id="saveIndicator">
                <i class="fas fa-check-circle"></i> Changes saved automatically
            </div>
        </div>

        <!-- Service Info Summary -->
        <div class="detail-section">
            <h3><i class="fas fa-info-circle"></i> Service Info</h3>
            <div class="detail-grid compact">
                <div class="detail-item">
                    <label>Service Type</label>
                    <span class="service-type"></span>
                </div>
                <div class="detail-item">
                    <label>Request Type</label>
                    <span class="request-type"></span>
                </div>
                <div class="detail-item">
                    <label>Seller</label>
                    <span class="seller"></span>
                </div>
                <div class="detail-item">
                    <label>Price</label>
                    <span class="price"></span>
                </div>
            </div>
        </div>

        <!-- Client Info -->
        <div class="detail-section">
            <h3><i class="fas fa-building"></i> Client</h3>
            <div class="detail-grid compact">
                <div class="detail-item full-width">
                    <label>Company</label>
                    <span class="company-name"></span>
                </div>
                <div class="detail-item">
                    <label>Contact</label>
                    <span class="client-name"></span>
                </div>
                <div class="detail-item">
                    <label>Phone</label>
                    <span class="phone"></span>
                </div>
                <div class="detail-item full-width">
                    <label>Email</label>
                    <span class="email"></span>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="detail-section">
            <h3><i class="fas fa-calendar-alt"></i> Dates</h3>
            <div class="detail-grid compact">
                <div class="detail-item">
                    <label>Work Date</label>
                    <span class="work-date"></span>
                </div>
                <div class="detail-item">
                    <label>Created</label>
                    <span class="created-at"></span>
                </div>
            </div>
        </div>

        <!-- Admin Notes -->
        <div class="detail-section">
            <h3><i class="fas fa-sticky-note"></i> Internal Notes</h3>
            <textarea
                class="admin-notes-input"
                id="adminNotes"
                placeholder="Add internal notes here... (auto-saves)"
                rows="3"
            ></textarea>
        </div>

        <!-- Observations from Seller -->
        <div class="detail-section">
            <h3><i class="fas fa-comment-dots"></i> Seller Notes</h3>
            <div class="observations-content readonly"></div>
        </div>
    </div>
</template>
