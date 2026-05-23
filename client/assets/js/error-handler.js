// Global Error Handler
const errorHandler = {
    init() {
        // Catch unhandled JavaScript errors
        window.addEventListener('error', (event) => {
            this.logError('JavaScript Error', {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error
            });
        });

        // Catch unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.logError('Unhandled Promise Rejection', {
                reason: event.reason,
                promise: event.promise
            });
            
            // Prevent the default browser behavior
            event.preventDefault();
        });

        // Catch network errors
        window.addEventListener('offline', () => {
            this.showNetworkError('You are offline. Please check your internet connection.');
        });

        window.addEventListener('online', () => {
            this.showNetworkSuccess('Connection restored.');
        });
    },

    logError(type, details) {
        console.group(`🚨 ${type}`);
        console.error('Details:', details);
        console.trace('Stack trace:');
        console.groupEnd();

        // In production, you would send this to your error tracking service
        if (!window.location.hostname.includes('localhost')) {
            // Example: Send to error tracking service
            // this.sendToErrorService(type, details);
        }
    },

    showNetworkError(message) {
        if (typeof utils !== 'undefined') {
            utils.showNotification(message, 'error');
        } else {
            console.error('Network Error:', message);
        }
    },

    showNetworkSuccess(message) {
        if (typeof utils !== 'undefined') {
            utils.showNotification(message, 'success');
        } else {
            console.log('Network Success:', message);
        }
    },

    // Graceful error handling for API calls
    async safeApiCall(apiFunction, fallbackData = null) {
        try {
            const result = await apiFunction();
            return result;
        } catch (error) {
            this.logError('API Call Failed', {
                function: apiFunction.name,
                error: error.message
            });
            
            return {
                success: false,
                message: 'An error occurred. Please try again.',
                data: fallbackData
            };
        }
    },

    // Safe DOM manipulation
    safeQuerySelector(selector, context = document) {
        try {
            return context.querySelector(selector);
        } catch (error) {
            this.logError('DOM Query Error', {
                selector,
                error: error.message
            });
            return null;
        }
    },

    safeQuerySelectorAll(selector, context = document) {
        try {
            return context.querySelectorAll(selector);
        } catch (error) {
            this.logError('DOM Query Error', {
                selector,
                error: error.message
            });
            return [];
        }
    },

    // Safe event listener addition
    safeAddEventListener(element, event, handler, options = {}) {
        try {
            if (element && typeof element.addEventListener === 'function') {
                element.addEventListener(event, handler, options);
                return true;
            } else {
                this.logError('Event Listener Error', {
                    element,
                    event,
                    message: 'Element is null or does not support addEventListener'
                });
                return false;
            }
        } catch (error) {
            this.logError('Event Listener Error', {
                element,
                event,
                error: error.message
            });
            return false;
        }
    },

    // Safe local storage operations
    safeLocalStorageGet(key, defaultValue = null) {
        try {
            const value = localStorage.getItem(key);
            return value !== null ? JSON.parse(value) : defaultValue;
        } catch (error) {
            this.logError('LocalStorage Get Error', {
                key,
                error: error.message
            });
            return defaultValue;
        }
    },

    safeLocalStorageSet(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (error) {
            this.logError('LocalStorage Set Error', {
                key,
                value,
                error: error.message
            });
            return false;
        }
    },

    // Recovery functions
    recoverFromError(errorType, recoveryAction) {
        console.log(`🔄 Attempting recovery from ${errorType}`);
        
        try {
            recoveryAction();
            console.log(`✅ Recovery successful for ${errorType}`);
            
            if (typeof utils !== 'undefined') {
                utils.showNotification('System recovered successfully', 'success');
            }
        } catch (error) {
            console.error(`❌ Recovery failed for ${errorType}:`, error);
            
            if (typeof utils !== 'undefined') {
                utils.showNotification('Recovery failed. Please refresh the page.', 'error');
            }
        }
    }
};

// Initialize error handler when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    errorHandler.init();
    console.log('✅ Error handler initialized');
});

// Make error handler globally available
window.errorHandler = errorHandler;