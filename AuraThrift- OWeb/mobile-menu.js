document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navContainer = document.querySelector('.nav-container');

    if (hamburger && navContainer) {
        hamburger.addEventListener('click', function() {
            navContainer.classList.toggle('active');
            this.classList.toggle('active');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navContainer.contains(e.target)) {
                navContainer.classList.remove('active');
                hamburger.classList.remove('active');
            }
        });

        // Close mobile menu when clicking a link
        navContainer.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                navContainer.classList.remove('active');
                hamburger.classList.remove('active');
            });
        });
    }
});
