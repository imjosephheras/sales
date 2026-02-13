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
    let pendingDocs = [];
    let historyDocs = [];

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
    const pdfViewer = document.getElementById('pdf-viewer');
    const btnMarkCompleted = document.getElementById('btn-mark-completed');
    const btnMarkPending = document.getElementById('btn-mark-pending');

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

        // Re-render lists to update active states
        renderPendingList();
        renderHistoryList();

        // Show viewer
        viewerEmptyState.style.display = 'none';
        viewerActiveState.style.display = 'flex';

        viewerDocTitle.textContent = doc.company_name || doc.client_name || 'Contract';
        viewerOrderNumber.textContent = doc.order_number || '';

        // Load PDF in iframe
        if (doc.pdf_path) {
            // Build absolute URL to the PDF
            const basePath = window.location.pathname.replace(/\/billing\/.*/i, '');
            pdfViewer.src = basePath + '/' + doc.pdf_path;
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
                viewerEmptyState.style.display = 'flex';
                viewerActiveState.style.display = 'none';
                pdfViewer.src = '';

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
                viewerEmptyState.style.display = 'flex';
                viewerActiveState.style.display = 'none';
                pdfViewer.src = '';

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

    // ==========================================
    // PUBLIC API
    // ==========================================

    window.BillingApp = {
        selectDocument: selectDocument
    };

    // ==========================================
    // INIT
    // ==========================================

    loadPending();
    loadHistory();

})();
