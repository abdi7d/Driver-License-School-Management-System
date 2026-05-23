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

const auth = {
    DEMO_MODE: localStorage.getItem('DEMO_MODE') === 'true',
    API_BASE_URL: (() => {
        const path = window.location.pathname;
        const isFileProtocol = window.location.protocol === 'file:' || window.location.origin === 'null';

        if (isFileProtocol) {
            return 'http://localhost:8000/api/auth';
        }

        const projectRoot = path.includes('/client/')
            ? path.substring(0, path.lastIndexOf('/client'))
            : '';

        return `${window.location.origin}${projectRoot}/server/api/auth`;
    })(),
    
    DEMO_USERS: [
        { email: 'manager@dlsm.et', password: 'password', name: 'Manager Demo', role: 'manager', status: 'active', id: 1 },
        { email: 'student@dlsm.et', password: 'password', name: 'Student Demo', role: 'student', status: 'active', id: 2 },
        { email: 'supervisor@dlsm.et', password: 'password', name: 'Supervisor Demo', role: 'supervisor', status: 'active', id: 3 },
        { email: 'instructor@dlsm.et', password: 'password', name: 'Instructor Demo', role: 'instructor', status: 'active', id: 4 }
    ],

    isAuthenticated() {
        return localStorage.getItem('token') !== null;
    },

    getCurrentUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },

    requireAuth() {
        if (!this.isAuthenticated()) {
            const currentPath = window.location.pathname;
            if (currentPath.includes('-portal/')) {
                window.location.href = '../login.html';
            } else {
                window.location.href = 'login.html';
            }
            return false;
        }
        return true;
    },

    async login(email, password) {
        if (this.DEMO_MODE) {
            return new Promise((resolve) => {
                setTimeout(() => {
                    const user = this.DEMO_USERS.find(u => u.email === email && u.password === password);
                    
                    if (!user) {
                        resolve({ success: false, message: 'Invalid email or password' });
                        return;
                    }
                    
                    if (user.status === 'pending') {
                        resolve({ 
                            success: false, 
                            message: 'Your account is pending approval. Please wait for admin to approve your registration.' 
                        });
                        return;
                    }
                    
                    if (user.status === 'inactive') {
                        resolve({ 
                            success: false, 
                            message: 'Your account has been deactivated. Please contact administration.' 
                        });
                        return;
                    }
                    
                    const token = 'demo_token_' + Date.now();
                    const userData = { id: user.id, name: user.name, email: user.email, role: user.role, status: user.status };
                    
                    localStorage.setItem('token', token);
                    localStorage.setItem('role', user.role);
                    localStorage.setItem('user', JSON.stringify(userData));
                    
                    resolve({ success: true, user: userData });
                }, 500);
            });
        }

        try {
            const response = await fetch(`${this.API_BASE_URL}/login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            
            if (response.ok && !data.error) {
                localStorage.setItem('token', data.token);
                localStorage.setItem('role', data.role);
                localStorage.setItem('user', JSON.stringify({ 
                    id: data.id, 
                    name: data.name, 
                    email: email, 
                    role: data.role 
                }));
                return { success: true, role: data.role };
            }
            return { success: false, message: data.error || data.message || 'Login failed' };
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, message: 'Connection error. Please check if the server is running.' };
        }
    },

    async register(userData) {
        if (this.DEMO_MODE) {
            return new Promise((resolve) => {
                setTimeout(() => {
                    const exists = this.DEMO_USERS.find(u => u.email === userData.email);
                    
                    if (exists) {
                        resolve({ success: false, message: 'Email already registered' });
                    } else {
                        const newUser = {
                            id: this.DEMO_USERS.length + 1,
                            email: userData.email,
                            password: userData.password,
                            name: userData.first_name + ' ' + userData.last_name,
                            role: userData.role || 'student',
                            status: 'pending'
                        };
                        this.DEMO_USERS.push(newUser);
                        
                        resolve({ 
                            success: true, 
                            message: 'Registration successful. Please redirecting to login...',
                            needsApproval: true 
                        });
                    }
                }, 500);
            });
        }

        try {
            const response = await fetch(`${this.API_BASE_URL}/register.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });
            
            const data = await response.json();
            if (response.ok && !data.error) {
                return { success: true, message: data.message };
            }
            return { success: false, message: data.error || data.message || 'Registration failed' };
        } catch (error) {
            console.error('Registration error:', error);
            return { success: false, message: 'Connection error. Please check if the server is running.' };
        }
    },

    logout() {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        localStorage.removeItem('user');
        localStorage.removeItem('userPhoto');
        
        const path = window.location.pathname;
        if (path.includes('-portal/')) {
            window.location.href = '../login.html';
        } else {
            window.location.href = 'login.html';
        }
    },

    getToken() {
        return localStorage.getItem('token');
    },
    
    hasRole(allowedRoles) {
        const role = localStorage.getItem('role');
        if (!role) return false;
        
        if (Array.isArray(allowedRoles)) {
            return allowedRoles.includes(role);
        }
        return role === allowedRoles;
    },
    
    redirectToDashboard(role) {
        if (role && typeof role === 'object') {
            role = role.role || role.user?.role || localStorage.getItem('role');
        }

        if (!role) role = localStorage.getItem('role');
        if (!role) {
            window.location.href = 'login.html';
            return;
        }

        role = role.toLowerCase();
        let dashboard = "";
        if (role === "manager") {
            dashboard = "manager-portal/dashboard.html";
        } else if (role === "instructor") {
            dashboard = "instructor-portal/dashboard.html";
        } else if (role === "supervisor") {
            dashboard = "supervisor-portal/dashboard.html";
        } else {
            dashboard = "student-portal/dashboard.html";
        }
        
        const currentPath = window.location.pathname;
        if (currentPath.includes('-portal/')) {
            window.location.href = '../' + dashboard;
        } else {
            window.location.href = dashboard;
        }
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
