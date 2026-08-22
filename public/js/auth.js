// public/js/auth.js
// Utilidades compartidas por las vistas de autenticacion (login / recuperar contrasena)

// Mostrar / ocultar contrasena en cualquier input con boton .toggle-pass
(function() {
    function activarToggles() {
        document.querySelectorAll('.toggle-pass').forEach(function(btn) {
            if (btn.dataset.listo) return;
            btn.dataset.listo = '1';
            btn.addEventListener('click', function() {
                var input = document.getElementById(btn.dataset.objetivo);
                var icono = btn.querySelector('i');
                if (!input) return;
                var ver = input.type === 'password';
                input.type = ver ? 'text' : 'password';
                icono.className = ver ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', activarToggles);
    } else {
        activarToggles();
    }
})();
