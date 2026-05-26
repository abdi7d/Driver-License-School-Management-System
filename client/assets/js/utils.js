// Utility functions
const utils = {
    // Format date
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    // Format time
    formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Show notification
    showNotification(message, type = 'info') {
        const toast = document.getElementById('toast');
        if (!toast) {
            // Create toast if it doesn't exist
            const newToast = document.createElement('div');
            newToast.id = 'toast';
            newToast.className = 'toast';
            document.body.appendChild(newToast);
        }
        
        const toastEl = document.getElementById('toast');
        toastEl.textContent = message;
        toastEl.className = `toast ${type}`;
        
        // Show toast
        setTimeout(() => toastEl.classList.add('show'), 100);
        
        // Hide toast after 3 seconds
        setTimeout(() => {
            toastEl.classList.remove('show');
        }, 3000);
    },

    // Validate email
    isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },

    // Validate phone
    isValidPhone(phone) {
        const regex = /^[0-9]{10}$/;
        return regex.test(phone);
    },

    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

window.utils = utils;
