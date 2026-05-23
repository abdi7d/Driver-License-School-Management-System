
(function() {
    'use strict';
    
    function checkAuth() {
        const token = localStorage.getItem('token');
        const user = localStorage.getItem('user');
        
        if (!token || !user) {
            redirectToLogin();
            return false;
        }
        
        try {
            const userData = JSON.parse(user);
            
            // Check if user has the correct role for this portal
            const currentPath = window.location.pathname;
            const requiredRole = getRequiredRole(currentPath);
            
            if (requiredRole && !isRoleAllowed(userData.role, requiredRole)) {
                redirectToCorrectDashboard(userData.role);
                return false;
            }
            
            return true;
        } catch (error) {
            console.error('Auth error:', error);
            redirectToLogin();
            return false;
        }
    }
    
    function getRequiredRole(path) {
        if (path.includes('student-portal')) return 'student';
        if (path.includes('manager-portal')) return 'manager';
        if (path.includes('supervisor-portal')) return 'supervisor';
        if (path.includes('instructor-portal')) return 'instructor';
        return null;
    }

    function isRoleAllowed(userRole, requiredRole) {
        if (!userRole || !requiredRole) return false;

        userRole = String(userRole).toLowerCase();
        requiredRole = String(requiredRole).toLowerCase();

        if (userRole === requiredRole) return true;
        if (userRole === 'admin' && requiredRole === 'manager') return true;

        return false;
    }
    
    function redirectToLogin() {
        const currentPath = window.location.pathname;

        if (window.location.protocol === 'file:' || window.location.origin === 'null') {
            const pathname = currentPath.replace(/\\/g, '/');
            const clientIndex = pathname.indexOf('/client/');

            if (clientIndex !== -1) {
                const relativePath = pathname.substring(clientIndex + '/client/'.length);
                window.location.href = `http://localhost/Driver-License-School/client/login.html`;
                return;
            }
        }
        
        if (currentPath.includes('login.html') || currentPath.includes('register.html') || currentPath.includes('index.html')) {
            return;
        }
        
        sessionStorage.setItem('redirectAfterLogin', window.location.href);
        
        if (currentPath.includes('-portal/')) {
            window.location.href = '../login.html';
        } else {
            window.location.href = 'login.html';
        }
    }
    
    function redirectToCorrectDashboard(role) {
        const dashboards = {
            'student': '../student-portal/dashboard.html',
            'manager': '../manager-portal/dashboard.html',
            'admin': '../manager-portal/dashboard.html',
            'supervisor': '../supervisor-portal/dashboard.html',
            'instructor': '../instructor-portal/dashboard.html'
        };
        
        const dashboard = dashboards[role];
        if (dashboard) {
            window.location.href = dashboard;
        } else {
            localStorage.clear();
            sessionStorage.clear();
            redirectToLogin();
        }
    }
    
    checkAuth();
    
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            checkAuth();
        }
    });
    
})();
