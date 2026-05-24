if (window.location.protocol === 'file:' || window.location.origin === 'null') {
    const pathname = window.location.pathname.replace(/\\/g, '/');
    const clientIndex = pathname.indexOf('/client/');

    if (clientIndex !== -1) {
        const relativePath = pathname.substring(clientIndex + '/client/'.length);
        const targetUrl = `http://localhost/Driver-License-School/client/${relativePath}${window.location.search}${window.location.hash}`;

        if (window.location.href !== targetUrl) {
            window.location.replace(targetUrl);
        }
    }
}

function getProjectRoot() {
    const path = window.location.pathname.replace(/\\/g, '/');

    if (window.location.protocol === 'file:' || window.location.origin === 'null') {
        return 'http://localhost/Driver-License-School';
    }

    if (path.includes('/client/')) {
        return path.substring(0, path.lastIndexOf('/client'));
    }

    return '';
}

function buildClientUrl(relativePath) {
    const projectRoot = getProjectRoot();
    const cleanPath = String(relativePath || '').replace(/^\/+/, '');

    if (window.location.protocol === 'file:' || window.location.origin === 'null') {
        return `${projectRoot}/client/${cleanPath}`;
    }

    return `${window.location.origin}${projectRoot}/client/${cleanPath}`;
}

const auth = {
    API_BASE_URL: (() => {
        const path = window.location.pathname.replace(/\\/g, '/');
        const isFileProtocol = window.location.protocol === 'file:' || window.location.origin === 'null';

        if (isFileProtocol) {
            return 'http://localhost/Driver-License-School/server/api/auth';
        }

        const projectRoot = path.includes('/client/')
            ? path.substring(0, path.lastIndexOf('/client'))
            : '';

        return `${window.location.origin}${projectRoot}/server/api/auth`;
    })(),
    
    isAuthenticated() {
        return Boolean(localStorage.getItem('token') && localStorage.getItem('user'));
    },

    getCurrentUser() {
        const user = localStorage.getItem('user');
        if (!user) {
            return null;
        }

        try {
            return JSON.parse(user);
        } catch (error) {
            console.error('Failed to parse user session:', error);
            return null;
        }
    },

    getLoginUrl() {
        return buildClientUrl('login.html');
    },

    getHomeUrl() {
        return buildClientUrl('index.html');
    },

    getDashboardUrl(role) {
        const normalizedRole = String(role || '').toLowerCase();
        const dashboards = {
            manager: 'manager-portal/dashboard.html',
            admin: 'manager-portal/dashboard.html',
            instructor: 'instructor-portal/dashboard.html',
            supervisor: 'supervisor-portal/dashboard.html',
            student: 'student-portal/dashboard.html',
            finance: 'finance-portal/dashboard.html'
        };

        return dashboards[normalizedRole] || null;
    },

    requireAuth() {
        if (!this.isAuthenticated()) {
            window.location.href = this.getLoginUrl();
            return false;
        }
        return true;
    },

    async login(email, password) {
        try {
            const response = await fetch(`${this.API_BASE_URL}/login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            if (response.ok && data.success !== false && data.token) {
                const user = data.user || {
                    id: data.id,
                    name: data.name,
                    email: data.email || email,
                    role: data.role,
                    status: data.status
                };

                localStorage.setItem('token', data.token);
                localStorage.setItem('role', user.role || data.role || 'student');
                localStorage.setItem('user', JSON.stringify(user));
                sessionStorage.removeItem('redirectAfterLogin');

                return { success: true, role: user.role || data.role, user };
            }

            return { success: false, message: data.message || data.error || 'Login failed' };
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, message: 'Connection error. Please check if the server is running.' };
        }
    },

    async register(userData) {
        try {
            const response = await fetch(`${this.API_BASE_URL}/register.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });
            
            const data = await response.json();
            if (response.ok && data.success !== false) {
                return { success: true, message: data.message, data };
            }

            return { success: false, message: data.message || data.error || 'Registration failed', data };
        } catch (error) {
            console.error('Registration error:', error);
            return { success: false, message: 'Connection error. Please check if the server is running.' };
        }
    },

    logout() {
        localStorage.clear();
        sessionStorage.clear();

        window.location.href = this.getHomeUrl();
    },

    getToken() {
        return localStorage.getItem('token');
    },
    
    hasRole(allowedRoles) {
        const role = localStorage.getItem('role');
        if (!role) return false;

        const normalizedRole = role.toLowerCase();
        const roleMatches = (expectedRole) => {
            const normalizedExpectedRole = String(expectedRole).toLowerCase();
            return normalizedRole === normalizedExpectedRole || (
                normalizedRole === 'admin' && normalizedExpectedRole === 'manager'
            );
        };
        
        if (Array.isArray(allowedRoles)) {
            return allowedRoles.some(roleMatches);
        }
        return roleMatches(allowedRoles);
    },
    
    redirectToDashboard(role) {
        if (role && typeof role === 'object') {
            role = role.role || role.user?.role || localStorage.getItem('role');
        }

        if (!role) role = localStorage.getItem('role');
        if (!role) {
            window.location.href = this.getLoginUrl();
            return;
        }

        const dashboard = this.getDashboardUrl(role);
        if (!dashboard) {
            window.location.href = this.getLoginUrl();
            return;
        }

        window.location.href = buildClientUrl(dashboard);
    }
};

// Global Logout Handler
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        // Clone button to remove any existing event listeners (from inline scripts)
        // to prevent double-firing of confirm dialogs.
        const newLogoutBtn = logoutBtn.cloneNode(true);
        logoutBtn.parentNode.replaceChild(newLogoutBtn, logoutBtn);
        
        newLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                auth.logout();
            }
        });
    }
});
