// public/js/sidebar.js - Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('sidebar');

    // Mobile: cerrar sidebar al hacer click fuera
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768) {
            if (sidebar && !sidebar.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });
});
