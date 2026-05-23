
const dashboard = {
    init(options = {}) {
        if (!auth.requireAuth()) {
            return;
        }

        const user = auth.getCurrentUser();
        
        if (options.requiredRole) {
            if (!auth.hasRole(options.requiredRole)) {
                alert('Access denied. You do not have permission to view this page.');
                auth.redirectToDashboard(user);
                return;
            }
        }
        
        this.displayUserInfo(user);
        
        this.setupMobileMenu();
        
        this.loadUserPhoto();
        
        return user;
    },
    
    hasRole(allowedRoles) {
        return auth.hasRole(allowedRoles);
    },

    displayUserInfo(user) {
        const userNameEl = document.getElementById('userName');
        const userRoleEl = document.getElementById('userRole');
        
        if (userNameEl) {
            userNameEl.textContent = user.name || 'User';
        }
        
        if (userRoleEl) {
            const roleNames = {
                'student': 'Student',
                'manager': 'Manager',
                'admin': 'Administrator',
                'supervisor': 'Supervisor'
            };
            userRoleEl.textContent = roleNames[user.role] || user.role;
        }
    },

    setAvatarImage(container, imageSrc) {
        if (!container || !imageSrc) return;
        const img = document.createElement('img');
        img.src = imageSrc;
        img.alt = 'Profile';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '50%';
        container.replaceChildren(img);
    },


    setupMobileMenu() {
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        
        if (!mobileMenuBtn || !sidebar || !backdrop) {
            console.warn('⚠️ Mobile menu elements not found');
            return;
        }
        
        // Remove existing event listeners to prevent duplicates
        const newMobileMenuBtn = mobileMenuBtn.cloneNode(true);
        mobileMenuBtn.parentNode.replaceChild(newMobileMenuBtn, mobileMenuBtn);
        
        const newBackdrop = backdrop.cloneNode(true);
        backdrop.parentNode.replaceChild(newBackdrop, backdrop);
        
        // Add fresh event listeners
        newMobileMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('mobile-open');
            newBackdrop.classList.toggle('active');
            console.log('📱 Mobile menu toggled');
        });
        
        newBackdrop.addEventListener('click', function() {
            sidebar.classList.remove('mobile-open');
            newBackdrop.classList.remove('active');
            console.log('📱 Mobile menu closed via backdrop');
        });
        
        // Close sidebar when clicking nav items on mobile
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-open');
                    newBackdrop.classList.remove('active');
                    console.log('📱 Mobile menu closed via nav item');
                }
            });
        });
        
        console.log('✅ Mobile menu initialized successfully');
    },

    loadUserPhoto() {
        const savedPhoto = localStorage.getItem('userPhoto');
        const topbarAvatar = document.getElementById('topbarAvatar');
        
        if (savedPhoto && topbarAvatar) {
            this.setAvatarImage(topbarAvatar, savedPhoto);
        }
        
        const user = auth.getCurrentUser();
        if (user && user.id) {
            let roleSpecificPhoto = null;
            
            if (user.role === 'student') {
                roleSpecificPhoto = localStorage.getItem(`profilePhoto_${user.id}`);
            } else if (user.role === 'manager') {
                roleSpecificPhoto = localStorage.getItem('managerPhoto');
            } else if (user.role === 'supervisor') {
                roleSpecificPhoto = localStorage.getItem('supervisorPhoto');
            } else if (user.role === 'instructor') {
                roleSpecificPhoto = localStorage.getItem(`instructorPhoto_${user.id}`);
            }
            
            if (roleSpecificPhoto && topbarAvatar) {
                this.setAvatarImage(topbarAvatar, roleSpecificPhoto);
            }
        }
    },

    checkUserStatus(user) {
        const statusAlert = document.getElementById('statusAlert');
        
        if (!statusAlert) return;
        
        if (user.status === 'pending') {
            statusAlert.style.display = 'flex';
            
            const statsGrid = document.getElementById('statsGrid');
            const progressSection = document.querySelector('.progress-section');
            const dashboardGrid = document.querySelector('.dashboard-grid');
            
            if (statsGrid) statsGrid.style.display = 'none';
            if (progressSection) progressSection.style.display = 'none';
            if (dashboardGrid) dashboardGrid.style.display = 'none';
            
            document.querySelectorAll('.nav-item').forEach(item => {
                if (!item.href.includes('profile.html') && !item.href.includes('dashboard.html')) {
                    item.style.opacity = '0.5';
                    item.style.pointerEvents = 'none';
                    item.title = 'Available after account approval';
                }
            });
        } else {
            statusAlert.style.display = 'none';
        }
    }
};

// Auto-initialize mobile menu on page load for all pages
document.addEventListener('DOMContentLoaded', function() {
    // Only setup mobile menu if not already initialized
    if (!window.mobileMenuInitialized) {
        dashboard.setupMobileMenu();
        window.mobileMenuInitialized = true;
    }
    
    // Typewriter animation cleanup
    setTimeout(() => {
        const typewriters = document.querySelectorAll('.typewriter-page, .typewriter, .typewriter-hero');
        typewriters.forEach(el => {
            el.classList.add('animation-done');
        });
    }, 2000);
});


