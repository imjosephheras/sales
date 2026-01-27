/**
 * BILLING MODULE - Main JavaScript
 * Handles pending list, document viewer, history, and completion flow
 */

(function () {
    'use strict';

    // State
    let selectedDocId = null;
    let pendingDocs = [];
    let historyDocs = [];

    // DOM Elements
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
    const pdfViewer = document.getElementById('pdf-viewer');
    const btnComplete = document.getElementById('btn-complete');
    const btnDownload = document.getElementById('btn-download');

    // ==========================================
    // LOAD DATA
    // ==========================================

    function loadPending(search) {
        const url = 'controllers/get_pending.php' + (search ? '?search=' + encodeURIComponent(search) : '');
        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    pendingDocs = data.data;
                    renderPendingList();
                }
            })
            .catch(() => {
                pendingList.innerHTML = '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading documents</p></div>';
            });
    }

    function loadHistory(search) {
        const url = 'controllers/get_history.php' + (search ? '?search=' + encodeURIComponent(search) : '');
        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    historyDocs = data.data;
                    renderHistoryList();
                }
            })
            .catch(() => {
                historyList.innerHTML = '<div class="error-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading history</p></div>';
            });
    }

    // ==========================================
    // RENDER LISTS
    // ==========================================

    function renderPendingList() {
        pendingCount.textContent = pendingDocs.length;

        if (pendingDocs.length === 0) {
            pendingList.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>No pending documents</p><small>Documents ready to invoice will appear here</small></div>';
            return;
        }

        pendingList.innerHTML = pendingDocs.map(doc => `
            <div class="doc-card ${doc.id == selectedDocId ? 'active' : ''}" data-id="${doc.id}" onclick="BillingApp.selectDocument(${doc.id})">
                <div class="doc-card-header">
                    <span class="doc-type-badge">${escapeHtml(doc.document_type || 'Invoice')}</span>
                    <i class="fas fa-file-pdf doc-pdf-icon"></i>
                </div>
                <div class="doc-card-body">
                    <div class="doc-order-number">
                        <i class="fas fa-hashtag"></i> ${escapeHtml(doc.order_number)}
                    </div>
                    <h3>${escapeHtml(doc.client_name || doc.company_name || 'N/A')}</h3>
                    <p>${escapeHtml(doc.company_name || '')}</p>
                </div>
                <div class="doc-card-meta">
                    <span class="doc-date"><i class="fas fa-calendar-alt"></i> ${formatDate(doc.created_at)}</span>
                </div>
            </div>
        `).join('');
    }

    function renderHistoryList() {
        historyCount.textContent = historyDocs.length;

        if (historyDocs.length === 0) {
            historyList.innerHTML = '<div class="empty-state"><i class="fas fa-archive"></i><p>No completed documents</p><small>Completed invoices will appear here</small></div>';
            return;
        }

        historyList.innerHTML = historyDocs.map(doc => `
            <div class="history-card ${doc.id == selectedDocId ? 'active' : ''}" data-id="${doc.id}" onclick="BillingApp.selectDocument(${doc.id}, true)">
                <div class="history-card-header">
                    <div class="doc-order-number">
                        <i class="fas fa-hashtag"></i> ${escapeHtml(doc.order_number)}
                    </div>
                    <span class="completed-badge"><i class="fas fa-check"></i> Done</span>
                </div>
                <div class="history-card-body">
                    <h3>${escapeHtml(doc.client_name || doc.company_name || 'N/A')}</h3>
                    <p>${escapeHtml(doc.company_name || '')}</p>
                </div>
                <div class="history-card-meta">
                    <span class="doc-date"><i class="fas fa-calendar-alt"></i> ${formatDate(doc.created_at)}</span>
                    <span class="completed-date"><i class="fas fa-check-circle"></i> ${formatDate(doc.completed_at)}</span>
                </div>
            </div>
        `).join('');
    }

    // ==========================================
    // SELECT DOCUMENT
    // ==========================================

    function selectDocument(id, isHistory) {
        selectedDocId = id;

        const allDocs = isHistory ? historyDocs : pendingDocs;
        const doc = allDocs.find(d => d.id == id);

        if (!doc) return;

        // Update active states
        renderPendingList();
        renderHistoryList();

        // Show viewer
        viewerEmptyState.style.display = 'none';
        viewerActiveState.style.display = 'flex';

        viewerDocTitle.textContent = doc.client_name || doc.company_name || 'Document';
        viewerOrderNumber.textContent = 'Order: ' + doc.order_number;

        // Load PDF
        if (doc.pdf_path) {
            pdfViewer.src = doc.pdf_path;
        } else {
            pdfViewer.src = '';
        }

        // Show/hide complete button based on status
        btnComplete.style.display = isHistory ? 'none' : 'flex';
    }

    // ==========================================
    // MARK COMPLETE
    // ==========================================

    function markComplete() {
        if (!selectedDocId) return;

        const doc = pendingDocs.find(d => d.id == selectedDocId);
        if (!doc) return;

        // Show confirmation modal
        showConfirmModal(doc);
    }

    function showConfirmModal(doc) {
        // Create modal if not exists
        let modal = document.getElementById('confirm-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'confirm-modal';
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-box">
                    <i class="fas fa-check-circle modal-icon"></i>
                    <h3>Mark as Completed?</h3>
                    <p id="modal-message">This document will be moved to the history.</p>
                    <div class="modal-actions">
                        <button class="btn btn-cancel" onclick="BillingApp.closeModal()">Cancel</button>
                        <button class="btn btn-success" id="modal-confirm-btn">
                            <i class="fas fa-check"></i> Confirm
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        document.getElementById('modal-message').textContent =
            `"${doc.order_number}" will be moved to completed history.`;

        modal.classList.add('active');

        document.getElementById('modal-confirm-btn').onclick = function () {
            confirmComplete(doc.id);
        };
    }

    function closeModal() {
        const modal = document.getElementById('confirm-modal');
        if (modal) modal.classList.remove('active');
    }

    function confirmComplete(id) {
        closeModal();

        fetch('controllers/mark_complete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    selectedDocId = null;
                    viewerEmptyState.style.display = 'flex';
                    viewerActiveState.style.display = 'none';
                    pdfViewer.src = '';

                    // Reload both lists
                    loadPending();
                    loadHistory();
                } else {
                    alert('Error: ' + (data.error || 'Could not complete document'));
                }
            })
            .catch(() => {
                alert('Network error. Please try again.');
            });
    }

    // ==========================================
    // DOWNLOAD
    // ==========================================

    function downloadPdf() {
        if (!selectedDocId) return;

        const doc = pendingDocs.find(d => d.id == selectedDocId) ||
                    historyDocs.find(d => d.id == selectedDocId);

        if (doc && doc.pdf_path) {
            const link = document.createElement('a');
            link.href = doc.pdf_path;
            link.download = doc.order_number + '.pdf';
            link.click();
        }
    }

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

    btnComplete.addEventListener('click', markComplete);
    btnDownload.addEventListener('click', downloadPdf);

    // ==========================================
    // PUBLIC API
    // ==========================================

    window.BillingApp = {
        selectDocument: selectDocument,
        closeModal: closeModal
    };

    // ==========================================
    // INIT
    // ==========================================

    loadPending();
    loadHistory();

})();
