<!-- ========================================= -->
<!-- 📥 INBOX PANEL - Pending Tasks -->
<!-- ========================================= -->

<div class="inbox-container">

    <!-- Header -->
    <div class="inbox-header">
        <h2><i class="fas fa-inbox"></i> Pending Tasks</h2>
        <p class="task-count">Loading tasks...</p>
    </div>

    <!-- 📋 Lista de Tareas Pendientes -->
    <div class="inbox-list" id="inbox-list">
        <!-- Las tareas pendientes se cargan aquí vía AJAX -->
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading pending tasks...</p>
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