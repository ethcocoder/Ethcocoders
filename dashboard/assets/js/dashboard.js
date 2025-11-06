/**
 * ETHCO CODERS - Dashboard JavaScript
 * Handles client-side interactions, notifications, and form validation
 */

(function() {
    'use strict';

    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        initializeNotifications();
        initializeChatBadge();
        initializeContactsBadge();
        initializeFormValidation();
        initializeToasts();
        initializeSidebarToggle();
    });

    /**
     * Initialize notification system
     */
    function initializeNotifications() {
        const badge = document.getElementById('notificationBadge');
        const list = document.getElementById('notificationsList');
        
        if (!badge || !list) return;

        // Fetch notifications
        fetch('../app/api/notifications.php', {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications.length > 0) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'inline-block';
                    
                    // Clear existing items
                    const existingItems = list.querySelectorAll('.dropdown-item:not(.dropdown-header):not(.dropdown-divider)');
                    existingItems.forEach(item => item.remove());
                    
                    // Add notifications
                    data.notifications.forEach(notification => {
                        const item = document.createElement('li');
                        const link = document.createElement('a');
                        link.className = 'dropdown-item';
                        link.href = notification.link || '#';
                        link.innerHTML = `
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>${notification.title}</strong>
                                    <p class="mb-0 small text-muted">${notification.message}</p>
                                </div>
                                ${notification.is_read == 0 ? '<span class="badge bg-danger">New</span>' : ''}
                            </div>
                        `;
                        item.appendChild(link);
                        const divider = list.querySelector('.dropdown-divider');
                        if (divider && divider.parentNode === list) {
                            list.insertBefore(item, divider);
                        } else {
                            list.appendChild(item);
                        }
                    });
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });
    }

    /**
     * Initialize chat badge
     */
    function initializeChatBadge() {
        const badge = document.getElementById('chatBadge');
        if (!badge) return;

        fetch('messages_api.php?action=unread_count', {
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                }
            })
            .catch(error => console.error('Error fetching chat count:', error));
    }

    /**
     * Initialize contacts badge (admin only)
     */
    function initializeContactsBadge() {
        const badge = document.getElementById('contactsBadge');
        if (!badge) return;

        fetch('../app/api/contacts.php?action=unread_count', {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                }
            })
            .catch(error => console.error('Error fetching contacts count:', error));
    }

    /**
     * Initialize form validation
     */
    function initializeFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                this.classList.add('was-validated');
            }, false);
        });
    }

    /**
     * Validate form
     */
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            }
        });
        
        return isValid;
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    /**
     * Create toast container
     */
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    }

    /**
     * Initialize toast system
     */
    function initializeToasts() {
        // Check for flash messages
        const flashMessage = document.querySelector('[data-flash-message]');
        if (flashMessage) {
            const message = flashMessage.getAttribute('data-flash-message');
            const type = flashMessage.getAttribute('data-flash-type') || 'success';
            showToast(message, type);
        }
    }

    /**
     * Format date to relative time
     */
    function formatRelativeTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        
        if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
        if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        return 'just now';
    }

    /**
     * Initialize sidebar toggle for mobile
     */
    function initializeSidebarToggle() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarCollapse = document.getElementById('navbarNav');
        
        if (!sidebarToggle || !sidebar) return;
        
        // Create overlay if it doesn't exist
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }
        
        // Close sidebar function
        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
        
        // Open sidebar function
        function openSidebar() {
            // Close navbar menu if open
            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            }
            sidebar.classList.add('show');
            overlay.classList.add('show');
        }
        
        // Toggle sidebar
        function toggleSidebar() {
            if (sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }
        
        // Toggle on button click
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            toggleSidebar();
        });
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            closeSidebar();
        });
        
        // Close sidebar when navbar menu opens
        if (navbarToggle && navbarCollapse) {
            navbarCollapse.addEventListener('show.bs.collapse', function() {
                if (window.innerWidth <= 992) {
                    closeSidebar();
                }
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                const isClickInsideSidebar = sidebar.contains(e.target);
                const isClickOnToggle = sidebarToggle.contains(e.target);
                const isClickOnNavbar = navbarCollapse && navbarCollapse.contains(e.target);
                const isClickOnNavbarToggle = navbarToggle && navbarToggle.contains(e.target);
                
                if (!isClickInsideSidebar && !isClickOnToggle && !isClickOnNavbar && !isClickOnNavbarToggle) {
                    closeSidebar();
                }
            }
        });
        
        // Close sidebar on window resize if switching to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                closeSidebar();
            }
        });
        
        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });
    }

    // Export functions for global use
    window.dashboardUtils = {
        showToast: showToast,
        formatRelativeTime: formatRelativeTime
    };
})();

