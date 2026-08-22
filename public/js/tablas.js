// public/js/tablas.js
// Utilidades compartidas para tablas:
// 1. Tablas responsivas (convierte filas en tarjetas en movil con etiquetas automaticas)
// 2. Buscador client-side por texto
// 3. Filtros client-side por columna (select)
// 4. Paginacion client-side
// 5. Contadores de caracteres para textareas/inputs
// 6. Validaciones de formulario client-side (correo, telefono, monto, cedula)

(function() {
    'use strict';

    // ============ 1. ETIQUETAS AUTOMATICAS PARA MODO MOVIL ============
    function prepararTablasMoviles() {
        document.querySelectorAll('table.table').forEach(function(tabla) {
            var ths = tabla.querySelectorAll('thead th');
            if (!ths.length) return;
            tabla.classList.add('tabla-movil');
            var etiquetas = Array.prototype.map.call(ths, function(th) {
                return th.textContent.trim();
            });
            tabla.querySelectorAll('tbody tr').forEach(function(fila) {
                fila.querySelectorAll('td').forEach(function(td, i) {
                    if (!td.hasAttribute('data-label') && etiquetas[i]) {
                        td.setAttribute('data-label', etiquetas[i]);
                    }
                });
            });
        });
    }

    // ============ 2 y 4. BUSCADOR + PAGINACION CLIENT-SIDE ============
    // Uso: <input data-buscar="idTabla"> y <table id="idTabla" data-por-pagina="10">
    function configurarTablasInteractivas() {
        document.querySelectorAll('table[data-por-pagina]').forEach(function(tabla) {
            var tbody = tabla.querySelector('tbody');
            if (!tbody) return;
            var todasLasFilas = Array.prototype.slice.call(tbody.querySelectorAll('tr')).filter(function(tr) {
                // Ignora la fila de "no hay registros"
                return !tr.querySelector('td[colspan]');
            });

            var porPagina = parseInt(tabla.dataset.porPagina, 10) || 10;
            var paginaActual = 1;
            var textoBusqueda = '';
            var filtrosColumna = {};
            var fechaDesde = '';
            var fechaHasta = '';

            var controles = document.createElement('div');
            controles.className = 'paginacion';
            controles.innerHTML =
                '<span class="tabla-info"></span>' +
                '<div class="paginacion-botones"></div>';
            tabla.closest('.table-responsive').after(controles);
            var info = controles.querySelector('.tabla-info');
            var botones = controles.querySelector('.paginacion-botones');

            // Buscador asociado
            document.querySelectorAll('[data-buscar="' + tabla.id + '"]').forEach(function(input) {
                input.addEventListener('input', function() {
                    textoBusqueda = input.value.toLowerCase().trim();
                    paginaActual = 1;
                    aplicar();
                });
            });

            // Filtros por columna: <select data-filtro-tabla="id" data-filtro-col="n">
            document.querySelectorAll('[data-filtro-tabla="' + tabla.id + '"]').forEach(function(sel) {
                sel.addEventListener('change', function() {
                    var col = parseInt(sel.dataset.filtroCol, 10);
                    if (sel.value === '') {
                        delete filtrosColumna[col];
                    } else {
                        filtrosColumna[col] = sel.value.toLowerCase();
                    }
                    paginaActual = 1;
                    aplicar();
                });
            });

            // Rango de fechas: <input type="date" data-fecha-desde="id"> / data-fecha-hasta
            // Requiere que la celda de fecha tenga atributo data-fecha="AAAA-MM-DD"
            document.querySelectorAll('[data-fecha-desde="' + tabla.id + '"]').forEach(function(input) {
                input.addEventListener('change', function() {
                    fechaDesde = input.value;
                    paginaActual = 1;
                    aplicar();
                });
            });
            document.querySelectorAll('[data-fecha-hasta="' + tabla.id + '"]').forEach(function(input) {
                input.addEventListener('change', function() {
                    fechaHasta = input.value;
                    paginaActual = 1;
                    aplicar();
                });
            });

            function filasVisibles() {
                return todasLasFilas.filter(function(fila) {
                    if (textoBusqueda && fila.textContent.toLowerCase().indexOf(textoBusqueda) === -1) {
                        return false;
                    }
                    for (var col in filtrosColumna) {
                        var td = fila.cells[parseInt(col, 10)];
                        if (!td || td.textContent.toLowerCase().trim() !== filtrosColumna[col]) {
                            return false;
                        }
                    }
                    if (fechaDesde || fechaHasta) {
                        var celdaFecha = fila.querySelector('td[data-fecha]');
                        var iso = celdaFecha ? celdaFecha.getAttribute('data-fecha') : '';
                        if (!iso) return false;
                        if (fechaDesde && iso < fechaDesde) return false;
                        if (fechaHasta && iso > fechaHasta) return false;
                    }
                    return true;
                });
            }

            function aplicar() {
                var visibles = filasVisibles();
                var totalPaginas = Math.max(1, Math.ceil(visibles.length / porPagina));
                if (paginaActual > totalPaginas) paginaActual = totalPaginas;

                var inicio = (paginaActual - 1) * porPagina;
                var fin = inicio + porPagina;

                // Oculta todas y muestra solo las del rango; tambien oculta fila vacia estatica
                tbody.querySelectorAll('tr').forEach(function(tr) { tr.style.display = 'none'; });
                visibles.slice(inicio, fin).forEach(function(tr) { tr.style.display = ''; });

                // Mensaje cuando no hay resultados tras filtrar
                var vacia = tbody.querySelector('tr.sin-resultados');
                if (!visibles.length && todasLasFilas.length) {
                    if (!vacia) {
                        vacia = document.createElement('tr');
                        vacia.className = 'sin-resultados';
                        vacia.innerHTML = '<td colspan="' + (tabla.querySelectorAll('thead th').length || 6) + '" class="text-center" style="display: table-cell;">No hay registros que coincidan con la búsqueda.</td>';
                        tbody.appendChild(vacia);
                    }
                    vacia.style.display = '';
                } else if (vacia) {
                    vacia.style.display = 'none';
                }

                // Info
                if (visibles.length) {
                    info.textContent = 'Mostrando ' + (inicio + 1) + ' a ' + Math.min(fin, visibles.length) + ' de ' + visibles.length + ' registro(s)';
                } else {
                    info.textContent = todasLasFilas.length ? 'Sin coincidencias' : 'Sin registros';
                }

                // Botones
                botones.innerHTML = '';
                function btn(texto, pagina, deshabilitado, activo, esPuntos) {
                    if (esPuntos) {
                        var s = document.createElement('span');
                        s.className = 'pag-puntos';
                        s.textContent = texto;
                        botones.appendChild(s);
                        return;
                    }
                    var a = document.createElement('button');
                    a.type = 'button';
                    a.className = 'pag-btn' + (activo ? ' activa' : '');
                    a.textContent = texto;
                    a.disabled = !!deshabilitado;
                    if (pagina !== null) {
                        a.addEventListener('click', function() {
                            paginaActual = pagina;
                            aplicar();
                            var card = tabla.closest('.card');
                            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    }
                    botones.appendChild(a);
                }

                btn('\u00AB', paginaActual - 1, paginaActual === 1);
                if (totalPaginas <= 7) {
                    for (var p = 1; p <= totalPaginas; p++) btn(String(p), p, false, p === paginaActual);
                } else {
                    btn('1', 1, false, paginaActual === 1);
                    if (paginaActual > 3) btn('...', null, false, false, true);
                    for (var q = Math.max(2, paginaActual - 1); q <= Math.min(totalPaginas - 1, paginaActual + 1); q++) {
                        btn(String(q), q, false, q === paginaActual);
                    }
                    if (paginaActual < totalPaginas - 2) btn('...', null, false, false, true);
                    btn(String(totalPaginas), totalPaginas, false, paginaActual === totalPaginas);
                }
                btn('\u00BB', paginaActual + 1, paginaActual === totalPaginas);
            }

            aplicar();
        });
    }

    // ============ 3. CONTADORES DE CARACTERES ============
    // Uso: <textarea data-max="1000"></textarea> -> crea contador debajo
    function configurarContadores() {
        document.querySelectorAll('[data-max]').forEach(function(campo) {
            var max = parseInt(campo.dataset.max, 10);
            var contador = campo.parentElement.querySelector('.contador-caracteres');
            if (!contador) {
                contador = document.createElement('small');
                contador.className = 'contador-caracteres';
                campo.after(contador);
            }
            function actualizar() {
                var len = campo.value.length;
                contador.textContent = len + ' / ' + max + ' caracteres';
                contador.classList.toggle('exedido', len > max);
            }
            campo.addEventListener('input', actualizar);
            actualizar();
        });
    }

    // ============ 5. SOLO DIGITOS CON MAXIMO EXACTO (cedula, telefono) ============
    // Uso: <input data-solo-digitos="10"> solo deja escribir digitos hasta el maximo.
    //      <input data-solo-digitos="13" data-permite-mas> admite un "+" inicial (ej. +593...).
    function configurarSoloDigitos() {
        document.querySelectorAll('input[data-solo-digitos]').forEach(function(campo) {
            var max = parseInt(campo.dataset.soloDigitos, 10) || 10;
            var permiteMas = campo.hasAttribute('data-permite-mas');

            function limpiar() {
                var v = campo.value;
                var conMas = permiteMas && v.charAt(0) === '+';
                var limpio = v.replace(/[^\d]/g, '');
                if (conMas) {
                    limpio = '+' + limpio;
                }
                if (limpio.length > max) {
                    limpio = limpio.slice(0, max);
                }
                if (campo.value !== limpio) {
                    campo.value = limpio;
                }
            }

            campo.addEventListener('input', limpiar);
            campo.addEventListener('change', limpiar);
        });
    }

    // ============ 6. VALIDACIONES CLIENT-SIDE ============
    var REGLAS = {
        correo: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
        telefono: /^(\+?593|0)9\d{8}$/,
        cedula: /^\d{10}$/,
        dinero: /^\d{1,10}(\.\d{1,2})?$/,
        enteropositivo: /^\d+$/
    };

    var MENSAJES = {
        correo: 'Ingresa un correo válido (ejemplo: nombre@dominio.com).',
        telefono: 'Teléfono inválido. Ejemplo válido: 0987654321 o +593987654321.',
        cedula: 'La cédula debe tener exactamente 10 dígitos numéricos.',
        dinero: 'Monto inválido: debe ser un número positivo con máximo 2 decimales (ejemplo: 150.50).',
        enteropositivo: 'Debe ser un número entero positivo.'
    };

    function validarCampo(campo) {
        var regla = campo.dataset.validar;
        var valor = campo.value.trim();

        // Quita estado previo
        campo.classList.remove('invalido', 'valido');
        var msg = campo.parentElement.querySelector('.campo-error');
        if (msg) msg.remove();

        // Campo vacio: si es requerido el propio HTML lo valida; si tiene data-validar y esta vacio pero no requerido, pasa
        if (valor === '') {
            if (campo.hasAttribute('required')) {
                marcarError(campo, 'Este campo es obligatorio.');
                return false;
            }
            return true;
        }

        if (REGLAS[regla] && !REGLAS[regla].test(valor)) {
            marcarError(campo, MENSAJES[regla] || 'Valor invalido.');
            return false;
        }

        // Montos mayores que cero
        if ((regla === 'dinero' || regla === 'enteropositivo') && parseFloat(valor) <= 0 && !campo.hasAttribute('data-permitir-cero')) {
            marcarError(campo, 'El valor debe ser mayor que $0.00.');
            return false;
        }

        campo.classList.add('valido');
        return true;
    }

    function marcarError(campo, mensaje) {
        campo.classList.add('invalido');
        var small = document.createElement('small');
        small.className = 'campo-error visible';
        small.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + mensaje;
        campo.after(small);
    }

    function configurarValidaciones() {
        var formularios = new Set();
        document.querySelectorAll('[data-validar]').forEach(function(campo) {
            formularios.add(campo.closest('form'));
            campo.addEventListener('input', function() { validarCampo(campo); });
            campo.addEventListener('blur', function() { validarCampo(campo); });
        });

        formularios.forEach(function(form) {
            if (!form) return;
            form.addEventListener('submit', function(e) {
                var todoOk = true;
                form.querySelectorAll('[data-validar]').forEach(function(campo) {
                    if (!validarCampo(campo)) todoOk = false;
                });
                if (!todoOk) {
                    e.preventDefault();
                    var primero = form.querySelector('.invalido');
                    if (primero) primero.focus();
                }
            });
        });
    }

    function init() {
        prepararTablasMoviles();
        configurarTablasInteractivas();
        configurarContadores();
        configurarSoloDigitos();
        configurarValidaciones();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
