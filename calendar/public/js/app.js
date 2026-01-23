/**
 * ============================================================
 * APP.JS - MAIN JAVASCRIPT INITIALIZER
 * Central initialization point for all calendar functionality
 * ============================================================
 */

(function() {
    'use strict';
    
    console.log('%c📅 Calendar System v2.0', 'color: #dc2626; font-size: 18px; font-weight: bold;');
    console.log('%cMVC Architecture - Initialized', 'color: #10b981; font-weight: bold;');
    
    /**
     * ============================================================
     * GLOBAL STATE
     * ============================================================
     */
    const CalendarApp = {
        initialized: false,
        currentMonth: null,
        currentYear: null,
        events: [],
        categories: [],
        
        // Initialize the app
        init: function() {
            if (this.initialized) {
                console.warn('Calendar already initialized');
                return;
            }
            
            console.log('🚀 Initializing Calendar App...');
            
            // Initialize modules
            this.initEventListeners();
            this.initModals();
            this.initTheme();
            this.initNotifications();
            
            this.initialized = true;
            console.log('✅ Calendar App initialized successfully');
        },
        
        /**
         * Initialize global event listeners
         */
        initEventListeners: function() {
            // Close modals on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeAllModals();
                }
            });
            
            // Close modals on backdrop click
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        this.closeModal(modal.id);
                    }
                });
            });
            
            console.log('✓ Event listeners initialized');
        },
        
        /**
         * Initialize modal functionality
         */
        initModals: function() {
            // Add close button handlers
            document.querySelectorAll('.modal-close').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const modal = e.target.closest('.modal');
                    if (modal) {
                        this.closeModal(modal.id);
                    }
                });
            });
            
            console.log('✓ Modals initialized');
        },
        
        /**
         * Initialize theme toggle
         */
        initTheme: function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            
            // Add theme toggle listener if button exists
            const themeToggle = document.getElementById('themeToggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    this.toggleTheme();
                });
            }
            
            console.log('✓ Theme initialized:', savedTheme);
        },
        
        /**
         * Toggle theme
         */
        toggleTheme: function() {
            const body = document.body;
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            console.log('Theme changed to:', newTheme);
        },
        
        /**
         * Initialize notifications
         */
        initNotifications: function() {
            // Auto-hide flash messages after 5 seconds
            setTimeout(() => {
                const flashMessages = document.querySelectorAll('.flash-message');
                flashMessages.forEach(msg => {
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 300);
                });
            }, 5000);
            
            console.log('✓ Notifications initialized');
        },
        
        /**
         * Open modal
         */
        openModal: function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        },
        
        /**
         * Close modal
         */
        closeModal: function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        },
        
        /**
         * Close all modals
         */
        closeAllModals: function() {
            document.querySelectorAll('.modal.active').forEach(modal => {
                modal.classList.remove('active');
            });
            document.body.style.overflow = '';
        },
        
        /**
         * Show notification
         */
        showNotification: function(message, type = 'info') {
            // Remove existing notifications
            const existing = document.querySelectorAll('.flash-message');
            existing.forEach(el => el.remove());
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `flash-message flash-${type}`;
            notification.innerHTML = `
                <span class="flash-icon">${this.getIcon(type)}</span>
                <span class="flash-text">${this.escapeHtml(message)}</span>
                <button class="flash-close" onclick="this.parentElement.remove()">×</button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        },
        
        /**
         * Get icon for notification type
         */
        getIcon: function(type) {
            const icons = {
                'success': '✓',
                'error': '✕',
                'warning': '⚠',
                'info': 'ℹ'
            };
            return icons[type] || icons['info'];
        },
        
        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        /**
         * AJAX helper
         */
        ajax: function(url, options = {}) {
            const defaults = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            };
            
            const config = { ...defaults, ...options };
            
            return fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    this.showNotification('Error connecting to server', 'error');
                    throw error;
                });
        }
    };
    
    /**
     * ============================================================
     * GLOBAL FUNCTIONS (for backward compatibility)
     * ============================================================
     */
    
    // Make key functions globally available
    window.CalendarApp = CalendarApp;
    
    window.openModal = function(modalId) {
        CalendarApp.openModal(modalId);
    };
    
    window.closeModal = function(modalId) {
        CalendarApp.closeModal(modalId);
    };
    
    window.showNotification = function(message, type) {
        CalendarApp.showNotification(message, type);
    };
    
    window.toggleTheme = function() {
        CalendarApp.toggleTheme();
    };
    
    /**
     * ============================================================
     * EVENT MODAL FUNCTIONS
     * ============================================================
     */
    
    window.openEventModal = function(eventId = null) {
        const modal = document.getElementById('eventModal');
        const form = document.getElementById('eventForm');
        const title = document.getElementById('modalTitle');
        
        if (!modal || !form) return;
        
        // Reset form
        form.reset();
        
        if (eventId) {
            // Edit mode
            title.textContent = 'Edit Event';
            document.getElementById('eventId').value = eventId;
            loadEventData(eventId);
        } else {
            // Create mode
            title.textContent = 'New Event';
            document.getElementById('eventId').value = '';
            
            // Set default dates to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('eventStartDate').value = today;
            document.getElementById('eventEndDate').value = today;
            document.getElementById('documentDate').value = today;
        }
        
        CalendarApp.openModal('eventModal');
    };
    
    window.openEventDetail = function(eventId) {
        console.log('Opening event detail:', eventId);
        // Load event details via AJAX
        CalendarApp.ajax(`api/events.php?id=${eventId}`)
            .then(data => {
                // For now, just open edit modal
                openEventModal(eventId);
            })
            .catch(error => {
                console.error('Error loading event:', error);
            });
    };
    
    window.loadEventData = function(eventId) {
        CalendarApp.ajax(`api/events.php?id=${eventId}`)
            .then(data => {
                // Populate form fields
                document.getElementById('eventTitle').value = data.title || '';
                document.getElementById('eventStartDate').value = data.start_date || '';
                document.getElementById('eventEndDate').value = data.end_date || '';
                document.getElementById('eventStartTime').value = data.start_time || '';
                document.getElementById('eventEndTime').value = data.end_time || '';
                document.getElementById('eventCategory').value = data.category_id || '';
                document.getElementById('eventLocation').value = data.location || '';
                document.getElementById('eventDescription').value = data.description || '';
                document.getElementById('eventStatus').value = data.status || 'pending';
                document.getElementById('eventPriority').value = data.priority || 'normal';
                
                if (data.is_all_day) {
                    document.getElementById('isAllDay').checked = true;
                    toggleAllDay();
                }
                
                // Scheduling fields
                if (document.getElementById('documentDate')) {
                    document.getElementById('documentDate').value = data.document_date || '';
                }
                if (document.getElementById('executionDate')) {
                    document.getElementById('executionDate').value = data.execution_date || '';
                }
                if (document.getElementById('frequencyMonths')) {
                    document.getElementById('frequencyMonths').value = data.frequency_months || '';
                }
                if (document.getElementById('frequencyYears')) {
                    document.getElementById('frequencyYears').value = data.frequency_years || 1;
                }
            })
            .catch(error => {
                console.error('Error loading event data:', error);
                CalendarApp.showNotification('Error loading event', 'error');
            });
    };
    
    window.toggleAllDay = function() {
        const isAllDay = document.getElementById('isAllDay').checked;
        const startTime = document.getElementById('eventStartTime');
        const endTime = document.getElementById('eventEndTime');
        
        if (startTime && endTime) {
            startTime.disabled = isAllDay;
            endTime.disabled = isAllDay;
            
            if (isAllDay) {
                startTime.value = '';
                endTime.value = '';
            }
        }
    };
    
    /**
     * ============================================================
     * WORK STATUS TOGGLE
     * ============================================================
     */
    
    window.toggleWorkStatus = function(eventId, element) {
        const isCompleted = element.classList.contains('on');
        const newStatus = isCompleted ? 'pending' : 'completed';
        
        CalendarApp.ajax('actions/event/toggle-status.php', {
            method: 'POST',
            body: JSON.stringify({
                event_id: eventId,
                status: newStatus
            })
        })
        .then(data => {
            if (data.success) {
                element.classList.toggle('on');
                CalendarApp.showNotification(
                    isCompleted ? 'Marked as pending' : 'Marked as completed',
                    'success'
                );
            } else {
                CalendarApp.showNotification('Error updating status', 'error');
            }
        })
        .catch(error => {
            console.error('Error toggling status:', error);
        });
    };
    
    /**
     * ============================================================
     * CLIENT FILTER FUNCTIONS
     * ============================================================
     */
    
    window.filterByClient = function(clientName) {
        console.log('Filtering by client:', clientName);
        
        // Update active state in sidebar
        document.querySelectorAll('.client-item').forEach(item => {
            item.classList.remove('active');
        });
        
        if (clientName === '') {
            document.querySelector('.client-item.all-clients')?.classList.add('active');
        } else {
            event.currentTarget?.classList.add('active');
        }
        
        // Filter event cards in calendar
        const eventCards = document.querySelectorAll('.event-card');
        eventCards.forEach(card => {
            const eventId = card.dataset.eventId;
            // You'll need to add client data attribute to cards
            const eventClient = card.dataset.client || '';
            
            if (clientName === '' || eventClient === clientName) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Store current filter
        sessionStorage.setItem('clientFilter', clientName);
    };
    
    window.clearClientFilter = function() {
        filterByClient('');
        document.getElementById('clientSearchInput').value = '';
    };
    
    window.filterClients = function(searchTerm) {
        const items = document.querySelectorAll('.client-item:not(.all-clients)');
        const search = searchTerm.toLowerCase();
        
        items.forEach(item => {
            const clientName = item.querySelector('.client-name').textContent.toLowerCase();
            if (clientName.includes(search)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    };
    
    /**
     * ============================================================
     * INITIALIZE ON DOM READY
     * ============================================================
     */
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            CalendarApp.init();
        });
    } else {
        CalendarApp.init();
    }
    
    /**
     * ============================================================
     * DEBUG INFO (Development only)
     * ============================================================
     */
    
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log('%c🛠️ Development Mode', 'color: #f59e0b; font-weight: bold;');
        console.log('Available functions:', {
            'CalendarApp': 'Main app object',
            'openModal(id)': 'Open modal',
            'closeModal(id)': 'Close modal',
            'showNotification(msg, type)': 'Show notification',
            'toggleTheme()': 'Toggle dark/light theme',
            'openEventModal(id)': 'Open event modal',
            'toggleWorkStatus(id, el)': 'Toggle work completion'
        });
    }
    
})();