// public/js/main.js - Utility functions for Vallermosso II

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });

    // Confirm delete actions
    const deleteForms = document.querySelectorAll('form[onsubmit]');
    deleteForms.forEach(function(form) {
        if (!form.getAttribute('onsubmit')) {
            form.setAttribute('onsubmit', 'return confirm("¿Está seguro de realizar esta acción?");');
        }
    });
});
