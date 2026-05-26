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

function parseJwt(token) {
    if (!token) return null;
    const parts = token.split('.');
    if (parts.length !== 3) return null;
    try {
        const payload = parts[1].replace(/-/g, '+').replace(/_/g, '/');
        const json = decodeURIComponent(atob(payload).split('').map((c) => {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(json);
    } catch (error) {
        console.error('Failed to parse JWT payload:', error);
        return null;
    }
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
        return Boolean(localStorage.getItem('token'));
    },

    getCurrentUser() {
        const userRaw = localStorage.getItem('user');
        if (userRaw) {
            try { return JSON.parse(userRaw); } catch (e) { console.error('Failed to parse user session:', e); }
        }

        // Try to recover user from token payload if possible
        const token = this.getToken();
        if (!token) return null;
        try {
            const payload = parseJwt(token);
            if (payload) {
                const recovered = {
                    id: payload.user_id || payload.id || null,
                    role: payload.role || null,
                    email: payload.email || null,
                    name: (payload.first_name || payload.name) ? `${payload.first_name || ''} ${payload.last_name || ''}`.trim() : (payload.name || null)
                };
                localStorage.setItem('user', JSON.stringify(recovered));
                return recovered;
            }
        } catch (e) {
            return null;
        }
        return null;
    },

    // Parse JWT payload without verifying signature (client-side only)
    parseTokenPayload() {
        const token = this.getToken();
        if (!token) return null;
        return parseJwt(token);
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
        // Backwards-compatible: accept (allowedRoles) if passed
        const allowedRoles = arguments.length ? arguments[0] : null;
        // Dev helper: if running on localhost and URL contains ?mockAuth=1,
        // populate a fake token and user to ease UI testing without real login.
        try {
            const search = window.location.search || '';
            if (window.location.hostname === 'localhost' && /[?&]mockAuth=1/.test(search)) {
                if (!localStorage.getItem('token')) {
                    const header = { alg: 'none', typ: 'JWT' };
                    const payload = { user_id: 10, role: 'student', email: 'student@example.com', exp: 9999999999 };
                    const token = btoa(JSON.stringify(header)) + '.' + btoa(JSON.stringify(payload)) + '.devsig';
                    localStorage.setItem('token', token);
                    localStorage.setItem('user', JSON.stringify({ id: 10, name: 'Dev Student', email: 'student@example.com', role: 'student' }));
                    localStorage.setItem('role', 'student');
                }
            }
        } catch (e) {
            console.warn('mockAuth setup failed:', e);
        }

        const token = this.getToken();
        if (!token) {
            // Save requested path for redirect after login
            try { sessionStorage.setItem('redirectAfterLogin', window.location.pathname + window.location.search); } catch(e){}
            window.location.href = this.getLoginUrl();
            return false;
        }

        const tokenPayload = this.parseTokenPayload();
        if (tokenPayload && tokenPayload.exp && Date.now() >= tokenPayload.exp * 1000) {
            localStorage.clear();
            try { sessionStorage.setItem('redirectAfterLogin', window.location.pathname + window.location.search); } catch(e){}
            window.location.href = this.getLoginUrl();
            return false;
        }

        // Ensure user object exists
        const user = this.getCurrentUser();
        if (!user) {
            // If token exists but user cannot be recovered, force re-login
            try { sessionStorage.setItem('redirectAfterLogin', window.location.pathname + window.location.search); } catch(e){}
            window.location.href = this.getLoginUrl();
            return false;
        }

        if (allowedRoles) {
            if (!this.hasRole(allowedRoles)) {
                // Unauthorized for this route — redirect to their dashboard
                utils.showNotification('Access denied for this page. Redirecting to your dashboard.', 'error');
                this.redirectToDashboard(user.role || user);
                return false;
            }
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

        // Redirect to landing page after clearing session
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

window.auth = auth;
