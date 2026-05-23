const theme = {
    init() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        this.setTheme(savedTheme);
        this.createToggleButton();
    },

    setTheme(themeName) {
        if (themeName === 'dark') {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        localStorage.setItem('theme', themeName);
        this.updateToggleButton(themeName);
    },

    toggleTheme() {
        const currentTheme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    },

    createToggleButton() {
        const topbar = document.querySelector('.topbar-right');
        if (!topbar) return;

        const toggleBtn = document.createElement('button');
        toggleBtn.id = 'themeToggle';
        toggleBtn.className = 'theme-toggle';
        toggleBtn.setAttribute('aria-label', 'Toggle dark mode');
        toggleBtn.innerHTML = '<span class="theme-icon">🌙</span>';
        
        toggleBtn.addEventListener('click', () => this.toggleTheme());
        
        topbar.insertBefore(toggleBtn, topbar.firstChild);
    },

    updateToggleButton(themeName) {
        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            const icon = themeName === 'dark' ? '☀️' : '🌙';
            toggleBtn.innerHTML = `<span class="theme-icon">${icon}</span>`;
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    theme.init();
});
