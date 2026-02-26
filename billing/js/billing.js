/**
 * BILLING / ACCOUNTING MODULE - Main JavaScript
 *
 * New flow:
 * - Left panel: Pending contracts (auto-sent from Contract Generator when marked Completed)
 * - Center panel: PDF viewer with Mark as Completed / Mark as Pending buttons
 * - Right panel: Completed contracts by Accounting
 *
 * No forms, no editors. Only PDF review and status toggle.
 * Tracks who marked and when.
 */

(function () {
    'use strict';

    // ==========================================
    // STATE
    // ==========================================

    let selectedDocId = null;
    let selectedDocSource = null; // 'pending' or 'history'
    let selectedDocType = null;
    let pendingDocs = [];
    let historyDocs = [];
    let attachmentsExpanded = true;

    // ==========================================
    // DOM ELEMENTS
    // ==========================================

    const pendingList = document.getElementById('pending-list');
    const historyList = document.getElementById('history-list');
    const pendingCount = document.getElementById('pending-count');
    const historyCount = document.getElementById('history-count');
    const pendingSearch = document.getElementById('pending-search');
    const historySearch = document.getElementById('history-search');

    const viewerEmptyState = document.getElementById('viewer-empty-state');
    const viewerActiveState = document.getElementById('viewer-active-state');
    const viewerDocTitle = document.getElementById('viewer-doc-title');
    const viewerOrderNumber = document.getElementById('viewer-order-number');
    const viewerClientName = document.getElementById('viewer-client-name');
    const viewerServiceName = document.getElementById('viewer-service-name');
    const pdfViewer = document.getElementById('pdf-viewer');
    const btnMarkCompleted = document.getElementById('btn-mark-completed');
    const btnMarkPending = document.getElementById('btn-mark-pending');

    // Attachment elements
    const docTypeButtons = document.querySelectorAll('.btn-doc-type');
    const attachmentsSection = document.getElementById('attachments-section');
    const attachmentsList = document.getElementById('attachments-list');
    const attachmentsCount = document.getElementById('attachments-count');
    const btnToggleAttachments = document.getElementById('btn-toggle-attachments');

    // Modal elements
    const attachmentModal = document.getElementById('attachment-modal');
    const attachmentForm = document.getElementById('attachment-upload-form');
    const attachmentModalTitle = document.getElementById('attachment-modal-title');
    const attachmentFileType = document.getElementById('attachment-file-type');
    const btnCloseModal = document.getElementById('btn-close-attachment-modal');
    const btnCancelUpload = document.getElementById('btn-cancel-upload');
    const btnSubmitUpload = document.getElementById('btn-submit-upload');
    const fileInput = document.getElementById('attachment-file');
    const fileUploadArea = document.getElementById('file-upload-area');
    const fileSelected = document.getElementById('file-selected');
    const fileSelectedName = document.getElementById('file-selected-name');
    const btnRemoveFile = document.getElementById('btn-remove-file');
    const uploadProgress = document.getElementById('upload-progress');
    const progressFill = document.getElementById('progress-fill');
    const progressText = document.getElementById('progress-text');

    // ==========================================
    // LOAD DATA
    // ==========================================

    function loadPending(search) {
        const url = 'controllers/get_pending.php' + (search ? '?search=' + encodeURIComponent(search) : '');

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    pendingDocs = data.data || [];
                } else {
                    pendingDocs = [];
                }
                renderPendingList();
            })
            .catch(() => {
                pendingList.innerHTML = '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading contracts</p></div>';
            });
    }

    function loadHistory(search) {
        const url = 'controllers/get_history.php' + (search ? '?search=' + encodeURIComponent(search) : '');

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    historyDocs = data.data || [];
                } else {
                    historyDocs = [];
                }
                renderHistoryList();
            })
            .catch(() => {
                historyList.innerHTML = '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading history</p></div>';
            });
    }

    // ==========================================
    // RENDER PENDING LIST (Left Panel)
    // ==========================================

    function renderPendingList() {
        pendingCount.textContent = pendingDocs.length;

        if (pendingDocs.length === 0) {
            pendingList.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>No pending contracts</p><small>Contracts completed in the Generator will appear here automatically</small></div>';
            return;
        }

        pendingList.innerHTML = pendingDocs.map(doc => `
            <div class="doc-card ${doc.id == selectedDocId && selectedDocSource === 'pending' ? 'active' : ''}"
                 data-id="${doc.id}"
                 onclick="BillingApp.selectDocument(${doc.id}, 'pending')">
                <div class="doc-card-header">
                    <span class="doc-type-badge">${escapeHtml(doc.document_type || 'Contract')}</span>
                    <i class="fas fa-file-pdf doc-pdf-icon"></i>
                </div>
                <div class="doc-card-body">
                    <div class="doc-order-number">
                        <i class="fas fa-hashtag"></i> ${escapeHtml(doc.order_number || 'N/A')}
                    </div>
                    <h3>${escapeHtml(doc.company_name || doc.client_name || 'N/A')}</h3>
                    ${doc.client_name && doc.company_name ? `<p class="doc-client"><i class="fas fa-user"></i> ${escapeHtml(doc.client_name)}</p>` : ''}
                    ${doc.service_name ? `<p class="doc-service"><i class="fas fa-tools"></i> ${escapeHtml(doc.service_name)}</p>` : ''}
                </div>
                <div class="doc-card-meta">
                    <span class="doc-date"><i class="fas fa-calendar-alt"></i> ${formatDate(doc.created_at)}</span>
                    ${doc.total_amount ? `<span class="doc-price"><i class="fas fa-dollar-sign"></i> ${escapeHtml(doc.total_amount)}</span>` : ''}
                </div>
            </div>
        `).join('');
    }

    // ==========================================
    // RENDER HISTORY LIST (Right Panel)
    // ==========================================

    function renderHistoryList() {
        historyCount.textContent = historyDocs.length;

        if (historyDocs.length === 0) {
            historyList.innerHTML = '<div class="empty-state"><i class="fas fa-archive"></i><p>No completed contracts</p><small>Contracts marked as completed will appear here</small></div>';
            return;
        }

        historyList.innerHTML = historyDocs.map(doc => `
            <div class="history-card ${doc.id == selectedDocId && selectedDocSource === 'history' ? 'active' : ''}"
                 data-id="${doc.id}"
                 onclick="BillingApp.selectDocument(${doc.id}, 'history')">
                <div class="history-card-header">
                    <div class="doc-order-number">
                        <i class="fas fa-hashtag"></i> ${escapeHtml(doc.order_number || 'N/A')}
                    </div>
                    <span class="completed-badge"><i class="fas fa-check"></i> Completed</span>
                </div>
                <div class="history-card-body">
                    <h3>${escapeHtml(doc.company_name || doc.client_name || 'N/A')}</h3>
                    ${doc.client_name ? `<p class="doc-client"><i class="fas fa-user"></i> ${escapeHtml(doc.client_name)}</p>` : ''}
                    ${doc.service_name ? `<p class="doc-service"><i class="fas fa-tools"></i> ${escapeHtml(doc.service_name)}</p>` : ''}
                </div>
                <div class="history-card-meta">
                    <span class="doc-date"><i class="fas fa-calendar-alt"></i> ${formatDate(doc.created_at)}</span>
                    <span class="completed-date"><i class="fas fa-check-circle"></i> ${formatDate(doc.completed_at)}</span>
                </div>
                ${doc.completed_by_name ? `
                <div class="history-card-audit">
                    <span class="audit-info"><i class="fas fa-user-check"></i> ${escapeHtml(doc.completed_by_name)}</span>
                </div>
                ` : ''}
            </div>
        `).join('');
    }

    // ==========================================
    // SELECT DOCUMENT
    // ==========================================

    function selectDocument(id, source) {
        selectedDocId = id;
        selectedDocSource = source;

        const allDocs = source === 'history' ? historyDocs : pendingDocs;
        const doc = allDocs.find(d => d.id == id);

        if (!doc) return;

        selectedDocType = doc.document_type || 'Contract';

        // Re-render lists to update active states
        renderPendingList();
        renderHistoryList();

        // Show viewer
        viewerEmptyState.style.display = 'none';
        viewerActiveState.style.display = 'flex';

        viewerDocTitle.textContent = doc.company_name || doc.client_name || 'Contract';
        viewerOrderNumber.textContent = doc.order_number || '';
        viewerClientName.textContent = doc.client_name && doc.company_name ? doc.client_name : '';
        viewerServiceName.textContent = doc.service_name || '';

        // Load PDF in iframe via secure serve.php endpoint
        if (doc.pdf_path) {
            const basePath = window.location.pathname.replace(/\/billing\/.*/i, '');
            // Strip leading 'storage/' prefix since serve.php resolves paths relative to the storage directory
            const fileParam = doc.pdf_path.replace(/^storage\//, '');
            pdfViewer.src = basePath + '/storage/serve.php?file=' + encodeURIComponent(fileParam);
        } else {
            pdfViewer.src = '';
        }

        // Toggle buttons based on source
        if (source === 'pending') {
            btnMarkCompleted.style.display = 'flex';
            btnMarkPending.style.display = 'none';
        } else {
            // From history: show Mark as Pending to allow reverting
            btnMarkCompleted.style.display = 'none';
            btnMarkPending.style.display = 'flex';
        }

        // Load attachments for this document
        loadAttachments();
    }

    // ==========================================
    // MARK AS COMPLETED
    // ==========================================

    function markAsCompleted() {
        if (!selectedDocId) return;

        const doc = pendingDocs.find(d => d.id == selectedDocId);
        if (!doc) return;

        // Disable button during request
        btnMarkCompleted.disabled = true;
        btnMarkCompleted.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        fetch('controllers/mark_complete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: doc.id, action: 'complete' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Reset viewer
                selectedDocId = null;
                selectedDocSource = null;
                selectedDocType = null;
                viewerEmptyState.style.display = 'flex';
                viewerActiveState.style.display = 'none';
                pdfViewer.src = '';
                attachmentsSection.style.display = 'none';

                // Reload both panels
                loadPending();
                loadHistory();
            } else {
                alert('Error: ' + (data.error || 'Could not mark as completed'));
            }

            // Restore button
            btnMarkCompleted.disabled = false;
            btnMarkCompleted.innerHTML = '<i class="fas fa-check-circle"></i> Mark as Completed';
        })
        .catch(() => {
            alert('Network error. Please try again.');
            btnMarkCompleted.disabled = false;
            btnMarkCompleted.innerHTML = '<i class="fas fa-check-circle"></i> Mark as Completed';
        });
    }

    // ==========================================
    // MARK AS PENDING (revert from completed)
    // ==========================================

    function markAsPending() {
        if (!selectedDocId) return;

        const doc = historyDocs.find(d => d.id == selectedDocId);
        if (!doc) return;

        btnMarkPending.disabled = true;
        btnMarkPending.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        fetch('controllers/mark_complete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: doc.id, action: 'pending' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Reset viewer
                selectedDocId = null;
                selectedDocSource = null;
                selectedDocType = null;
                viewerEmptyState.style.display = 'flex';
                viewerActiveState.style.display = 'none';
                pdfViewer.src = '';
                attachmentsSection.style.display = 'none';

                // Reload both panels
                loadPending();
                loadHistory();
            } else {
                alert('Error: ' + (data.error || 'Could not mark as pending'));
            }

            btnMarkPending.disabled = false;
            btnMarkPending.innerHTML = '<i class="fas fa-clock"></i> Mark as Pending';
        })
        .catch(() => {
            alert('Network error. Please try again.');
            btnMarkPending.disabled = false;
            btnMarkPending.innerHTML = '<i class="fas fa-clock"></i> Mark as Pending';
        });
    }

    // ==========================================
    // ATTACHMENTS: LOAD & RENDER
    // ==========================================

    function loadAttachments() {
        if (!selectedDocId) return;

        const docType = selectedDocType || 'Contract';
        const url = 'controllers/get_attachments.php?document_id=' + selectedDocId + '&document_type=' + encodeURIComponent(docType);

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    attachmentsSection.style.display = 'block';
                    attachmentsCount.textContent = data.data.length;
                    renderAttachments(data.data);
                } else {
                    attachmentsSection.style.display = 'none';
                    attachmentsCount.textContent = '0';
                    attachmentsList.innerHTML = '';
                }
            })
            .catch(() => {
                attachmentsSection.style.display = 'none';
            });
    }

    function renderAttachments(attachments) {
        attachmentsList.innerHTML = attachments.map(att => {
            const icon = getFileIcon(att.file_name);
            const typeBadge = getFileTypeBadge(att.file_type);
            return `
                <div class="attachment-item" data-id="${att.id}">
                    <div class="attachment-icon">
                        <i class="${icon}"></i>
                    </div>
                    <div class="attachment-info">
                        <span class="attachment-name" title="${escapeHtml(att.file_name)}">${escapeHtml(att.file_name)}</span>
                        <div class="attachment-meta">
                            <span class="attachment-type-badge ${typeBadge.cls}">${typeBadge.label}</span>
                            <span class="attachment-date">${att.created_at_formatted || formatDate(att.created_at)}</span>
                            ${att.uploaded_by ? `<span class="attachment-uploader"><i class="fas fa-user"></i> ${escapeHtml(att.uploaded_by)}</span>` : ''}
                        </div>
                    </div>
                    <div class="attachment-actions">
                        <button class="btn-att-download" onclick="BillingApp.downloadAttachment(${att.id})" title="Download">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="btn-att-delete" onclick="BillingApp.deleteAttachment(${att.id})" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    function getFileIcon(filename) {
        if (!filename) return 'fas fa-file';
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            'pdf': 'fas fa-file-pdf',
            'jpg': 'fas fa-file-image', 'jpeg': 'fas fa-file-image', 'png': 'fas fa-file-image',
            'doc': 'fas fa-file-word', 'docx': 'fas fa-file-word',
            'xls': 'fas fa-file-excel', 'xlsx': 'fas fa-file-excel', 'csv': 'fas fa-file-csv',
            'txt': 'fas fa-file-alt',
        };
        return icons[ext] || 'fas fa-file';
    }

    function getFileTypeBadge(fileType) {
        const types = {
            'timesheet': { label: 'Timesheet', cls: 'badge-timesheet' },
            'invoice':   { label: 'Invoice', cls: 'badge-invoice' },
            'po':        { label: 'PO', cls: 'badge-po' },
            'jwo_pdf':   { label: 'JWO PDF', cls: 'badge-jwo' },
            'other':     { label: 'Other', cls: 'badge-other' },
        };
        return types[fileType] || { label: fileType || 'Other', cls: 'badge-other' };
    }

    // ==========================================
    // ATTACHMENTS: UPLOAD
    // ==========================================

    const fileTypeLabels = {
        'timesheet': 'Timesheet',
        'invoice': 'Invoice',
        'po': 'PO (Purchase Order)',
        'other': 'Other'
    };

    function openUploadModal(fileType) {
        if (!selectedDocId) {
            alert('Please select a document first.');
            return;
        }
        attachmentModal.classList.add('active');
        attachmentForm.reset();
        // Set the file type from the button clicked
        attachmentFileType.value = fileType;
        attachmentModalTitle.textContent = 'Attach ' + (fileTypeLabels[fileType] || 'Document');
        fileSelected.style.display = 'none';
        fileUploadArea.style.display = 'flex';
        uploadProgress.style.display = 'none';
        btnSubmitUpload.disabled = false;
        btnSubmitUpload.innerHTML = '<i class="fas fa-upload"></i> Upload';
    }

    function closeUploadModal() {
        attachmentModal.classList.remove('active');
        attachmentForm.reset();
    }

    function handleFileSelect() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileSelectedName.textContent = file.name;
            fileSelected.style.display = 'flex';
            fileUploadArea.style.display = 'none';
        }
    }

    function removeSelectedFile() {
        fileInput.value = '';
        fileSelected.style.display = 'none';
        fileUploadArea.style.display = 'flex';
    }

    function submitUpload(e) {
        e.preventDefault();

        if (!selectedDocId) return;

        const fileType = attachmentFileType.value;
        if (!fileType) {
            alert('Please select a document type.');
            return;
        }
        if (!fileInput.files.length) {
            alert('Please select a file.');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('document_id', selectedDocId);
        formData.append('document_type', selectedDocType || 'Contract');
        formData.append('file_type', fileType);

        // Show progress
        btnSubmitUpload.disabled = true;
        btnSubmitUpload.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        uploadProgress.style.display = 'block';
        progressFill.style.width = '0%';
        progressText.textContent = 'Uploading...';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'controllers/upload_attachment.php', true);

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                progressFill.style.width = pct + '%';
                progressText.textContent = pct + '% uploaded';
            }
        };

        xhr.onload = function () {
            try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    progressFill.style.width = '100%';
                    progressText.textContent = 'Upload complete!';
                    setTimeout(() => {
                        closeUploadModal();
                        loadAttachments();
                    }, 500);
                } else {
                    alert('Upload failed: ' + (data.error || 'Unknown error'));
                    uploadProgress.style.display = 'none';
                    btnSubmitUpload.disabled = false;
                    btnSubmitUpload.innerHTML = '<i class="fas fa-upload"></i> Upload';
                }
            } catch (err) {
                alert('Upload failed: Invalid server response');
                uploadProgress.style.display = 'none';
                btnSubmitUpload.disabled = false;
                btnSubmitUpload.innerHTML = '<i class="fas fa-upload"></i> Upload';
            }
        };

        xhr.onerror = function () {
            alert('Network error. Please try again.');
            uploadProgress.style.display = 'none';
            btnSubmitUpload.disabled = false;
            btnSubmitUpload.innerHTML = '<i class="fas fa-upload"></i> Upload';
        };

        xhr.send(formData);
    }

    // ==========================================
    // ATTACHMENTS: DOWNLOAD
    // ==========================================

    function downloadAttachment(id) {
        window.open('controllers/download_attachment.php?id=' + id, '_blank');
    }

    // ==========================================
    // ATTACHMENTS: DELETE
    // ==========================================

    function deleteAttachment(id) {
        if (!confirm('Are you sure you want to delete this attachment?')) return;

        fetch('controllers/delete_attachment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                loadAttachments();
            } else {
                alert('Error: ' + (data.error || 'Could not delete attachment'));
            }
        })
        .catch(() => {
            alert('Network error. Please try again.');
        });
    }

    // ==========================================
    // ATTACHMENTS: TOGGLE EXPAND/COLLAPSE
    // ==========================================

    function toggleAttachments() {
        attachmentsExpanded = !attachmentsExpanded;
        if (attachmentsExpanded) {
            attachmentsList.style.display = 'block';
            btnToggleAttachments.innerHTML = '<i class="fas fa-chevron-down"></i>';
        } else {
            attachmentsList.style.display = 'none';
            btnToggleAttachments.innerHTML = '<i class="fas fa-chevron-right"></i>';
        }
    }

    // ==========================================
    // DRAG & DROP
    // ==========================================

    if (fileUploadArea) {
        fileUploadArea.addEventListener('click', function () {
            fileInput.click();
        });

        fileUploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            fileUploadArea.classList.add('drag-over');
        });

        fileUploadArea.addEventListener('dragleave', function () {
            fileUploadArea.classList.remove('drag-over');
        });

        fileUploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            fileUploadArea.classList.remove('drag-over');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect();
            }
        });
    }

    // ==========================================
    // PASTE IMAGE FROM CLIPBOARD (Ctrl+V / Cmd+V)
    // ==========================================

    document.addEventListener('paste', function (e) {
        // Only activate when the attachment modal is open
        if (!attachmentModal || !attachmentModal.classList.contains('active')) return;

        const clipboardItems = e.clipboardData && e.clipboardData.items;
        if (!clipboardItems) return;

        for (let i = 0; i < clipboardItems.length; i++) {
            const item = clipboardItems[i];

            // Only accept image types (PNG, JPEG)
            if (item.type === 'image/png' || item.type === 'image/jpeg') {
                e.preventDefault();

                const blob = item.getAsFile();
                if (!blob) return;

                // Validate file size (20MB max)
                const maxSize = 20 * 1024 * 1024;
                if (blob.size > maxSize) {
                    alert('The pasted image exceeds the 20MB size limit.');
                    return;
                }

                // Generate a filename based on type and timestamp
                const ext = item.type === 'image/png' ? 'png' : 'jpg';
                const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
                const fileName = 'pasted-image-' + timestamp + '.' + ext;

                // Create a proper File from the blob
                const file = new File([blob], fileName, { type: item.type });

                // Use DataTransfer to set the file on the input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;

                // Show the file as selected
                handleFileSelect();
                return;
            }
        }
    });

    // ==========================================
    // UTILITIES
    // ==========================================

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    // ==========================================
    // SEARCH
    // ==========================================

    let pendingTimer, historyTimer;

    pendingSearch.addEventListener('input', function () {
        clearTimeout(pendingTimer);
        pendingTimer = setTimeout(() => loadPending(this.value), 300);
    });

    historySearch.addEventListener('input', function () {
        clearTimeout(historyTimer);
        historyTimer = setTimeout(() => loadHistory(this.value), 300);
    });

    // ==========================================
    // EVENT LISTENERS
    // ==========================================

    btnMarkCompleted.addEventListener('click', markAsCompleted);
    btnMarkPending.addEventListener('click', markAsPending);

    // Attachment events - 4 document type buttons
    docTypeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            openUploadModal(this.dataset.fileType);
        });
    });
    btnCloseModal.addEventListener('click', closeUploadModal);
    btnCancelUpload.addEventListener('click', closeUploadModal);
    attachmentForm.addEventListener('submit', submitUpload);
    fileInput.addEventListener('change', handleFileSelect);
    btnRemoveFile.addEventListener('click', removeSelectedFile);
    btnToggleAttachments.addEventListener('click', toggleAttachments);

    // Close modal on overlay click
    attachmentModal.addEventListener('click', function (e) {
        if (e.target === attachmentModal) closeUploadModal();
    });

    // ==========================================
    // PUBLIC API
    // ==========================================

    window.BillingApp = {
        selectDocument: selectDocument,
        downloadAttachment: downloadAttachment,
        deleteAttachment: deleteAttachment
    };

    // ==========================================
    // INIT
    // ==========================================

    loadPending();
    loadHistory();

})();
