// Dark mode toggle
const theme = {
    init() {
        this.toggle = document.querySelector('.theme-toggle');
        this.html = document.documentElement;
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        this.setTheme(savedTheme);
        
        // Toggle event
        this.toggle.addEventListener('click', () => {
            const newTheme = this.html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            this.setTheme(newTheme);
        });
    },
    
    setTheme(theme) {
        this.html.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    }
};

// Intersection Observer for animations
const animate = {
    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-up');
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.article-card').forEach(el => observer.observe(el));
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    theme.init();
    animate.init();
});
