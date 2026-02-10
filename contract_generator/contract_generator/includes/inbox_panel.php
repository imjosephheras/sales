<!-- ========================================= -->
<!-- INBOX PANEL - Pending Tasks & Generated Contracts -->
<!-- ========================================= -->

<div class="inbox-container">

    <!-- ========================================= -->
    <!-- SECTION 1: PENDING TASKS -->
    <!-- ========================================= -->
    <div class="inbox-section" id="pending-section">
        <div class="inbox-header" id="pending-header">
            <div class="header-toggle" onclick="toggleSection('pending')">
                <i class="fas fa-chevron-down section-arrow" id="pending-arrow"></i>
                <h2><i class="fas fa-inbox"></i> Pending Tasks</h2>
            </div>
            <p class="task-count" id="pending-count">Loading...</p>
        </div>

        <!-- Lista de Tareas Pendientes -->
        <div class="inbox-list" id="inbox-list">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading pending tasks...</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper" id="pagination-container" style="display: none;"></div>
    </div>

    <!-- ========================================= -->
    <!-- SECTION 2: GENERATED CONTRACTS -->
    <!-- ========================================= -->
    <div class="inbox-section" id="completed-section">
        <div class="inbox-header completed-header" id="completed-header">
            <div class="header-toggle" onclick="toggleSection('completed')">
                <i class="fas fa-chevron-down section-arrow" id="completed-arrow"></i>
                <h2><i class="fas fa-file-contract"></i> Generated Contracts</h2>
            </div>
            <p class="task-count" id="completed-count">Loading...</p>
        </div>

        <!-- Lista de Contratos Generados -->
        <div class="inbox-list" id="completed-list">
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading generated contracts...</p>
            </div>
        </div>
    </div>

</div>

<!-- Template para items del inbox (tareas pendientes) -->
<template id="task-item-template">
    <div class="task-item" data-id="" data-event-id="">
        <div class="task-header">
            <span class="task-priority-badge"></span>
            <span class="task-category-badge"></span>
        </div>
        <div class="task-body">
            <h3 class="task-title"></h3>
            <p class="task-description"></p>
            <div class="task-meta">
                <span class="due-date">
                    <i class="far fa-calendar"></i>
                    <span class="date-text"></span>
                </span>
            </div>
        </div>
    </div>
</template>

<!-- Template para items completados (contratos generados) -->
<template id="completed-item-template">
    <div class="task-item completed-item" data-id="">
        <div class="task-header">
            <span class="task-type-badge"></span>
            <span class="task-category-badge"></span>
        </div>
        <div class="task-body">
            <h3 class="task-title"></h3>
            <p class="task-docnum"></p>
            <p class="task-description"></p>
            <div class="task-meta">
                <span class="completed-date">
                    <i class="fas fa-check-circle"></i>
                    <span class="date-text"></span>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
// Toggle section collapse/expand
function toggleSection(section) {
    const list = document.getElementById(section === 'pending' ? 'inbox-list' : 'completed-list');
    const arrow = document.getElementById(section + '-arrow');

    if (list.style.display === 'none') {
        list.style.display = 'block';
        arrow.classList.remove('fa-chevron-right');
        arrow.classList.add('fa-chevron-down');
    } else {
        list.style.display = 'none';
        arrow.classList.remove('fa-chevron-down');
        arrow.classList.add('fa-chevron-right');
    }
}
</script>
