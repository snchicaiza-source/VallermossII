<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../models/Vivienda.php';

verificarRol(['ADMINISTRADOR']);

$usuarioModel = new Usuario();

// Buscador y paginacion server-side
$buscar = trim($_GET['buscar'] ?? '');
$porPagina = 10;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$resultado = $usuarioModel->obtenerPaginado($buscar, $pagina, $porPagina);
$usuarios = $resultado['datos'];
$totalUsuarios = $resultado['total'];

// Catalogo de viviendas para el formulario
$viviendas = Vivienda::obtenerTodas(true);

// Modo edicion: si viene ?editar=ID se carga el usuario en el formulario
$usuarioEditar = null;
if (isset($_GET['editar'])) {
    $usuarioEditar = $usuarioModel->obtenerPorId((int)$_GET['editar']);
}
$esEdicion = $usuarioEditar !== false && $usuarioEditar !== null;

// Valores ingresados que se restauran tras un error de validacion/duplicado
$formOld = $_SESSION['form_old'] ?? [];
unset($_SESSION['form_old']);
$formErrors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

function valorFormulario(array $old, array $registroEdicion, string $campo, bool $edicion): string {
    if (array_key_exists($campo, $old)) return (string)$old[$campo];
    if ($edicion && isset($registroEdicion[$campo])) return (string)$registroEdicion[$campo];
    return '';
}

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Accesos - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/tablas.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="app-layout">
        <?php include_once __DIR__ . '/../sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1><i class="fa-solid fa-shield-halved"></i> Control de Accesos y Usuarios</h1>
                <p class="subtitle">Gestión de credenciales, asignación de unidades y registro de residentes.</p>
            </header>


            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success'];
                                                                                            unset($_SESSION['flash_success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error'];
                                                                                                unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <?php if ($msg === 'creado'): ?>
                <div class="alert alert-success"><i class="fa-solid fa-check"></i> Usuario registrado exitosamente.</div>
            <?php elseif ($error === 'campos_vacios'): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Todos los campos marcados son obligatorios.</div>
            <?php elseif ($error === 'db'): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Error al registrar. Verifique que la cédula o correo no esten duplicados.</div>
            <?php endif; ?>

            <!-- Formulario para Registrar / Editar Usuarios -->
            <section class="card">
                <div class="card-header">
                    <h2>
                        <i class="fa-solid <?= $esEdicion ? 'fa-user-pen' : 'fa-user-plus' ?>"></i>
                        <?= $esEdicion ? 'Editar Usuario: ' . htmlspecialchars($usuarioEditar['nombres']) : 'Registrar Nuevo Usuario' ?>
                    </h2>
                    <?php if ($esEdicion): ?>
                        <a href="usuarios.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-xmark"></i> Cancelar Edicion</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form action="../../controllers/UsuarioController.php" method="POST" class="grid-form" autocomplete="off">
                        <input type="hidden" name="action" value="<?= $esEdicion ? 'editar_usuario' : 'crear_usuario' ?>">
                        <?php if ($esEdicion): ?>
                            <input type="hidden" name="id_usuario" value="<?= (int)$usuarioEditar['id_usuario'] ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="cedula">Cédula / Identificación</label>
                            <input type="text" id="cedula" name="cedula" class="form-control <?= isset($formErrors['cedula']) ? 'invalido' : '' ?>" placeholder="Ej. 1700000001" maxlength="10" inputmode="numeric" required data-validar="cedula" data-solo-digitos="10" value="<?= htmlspecialchars(valorFormulario($formOld, $usuarioEditar ?: [], 'cedula', $esEdicion)) ?>">
                            <?php if (isset($formErrors['cedula'])): ?><small class="campo-error visible"><i class="fa-solid fa-circle-exclamation"></i> <?= $formErrors['cedula'] ?></small><?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="nombres">Nombres Completos</label>
                            <input type="text" id="nombres" name="nombres" class="form-control" placeholder="Ej. Carlos Mendoza" maxlength="120" minlength="2" required value="<?= htmlspecialchars(valorFormulario($formOld, $usuarioEditar ?: [], 'nombres', $esEdicion)) ?>">
                        </div>

                        <div class="form-group">
                            <label for="correo">Correo Electrónico</label>
                            <input type="email" id="correo" name="correo" class="form-control <?= isset($formErrors['correo']) ? 'invalido' : '' ?>" placeholder="usuario@correo.com" maxlength="100" required data-validar="correo" value="<?= htmlspecialchars(valorFormulario($formOld, $usuarioEditar ?: [], 'correo', $esEdicion)) ?>">
                            <?php if (isset($formErrors['correo'])): ?><small class="campo-error visible"><i class="fa-solid fa-circle-exclamation"></i> <?= $formErrors['correo'] ?></small><?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="telefono_whatsapp">Teléfono / WhatsApp <small style="color:var(--text-muted);">(opcional)</small></label>
                            <input type="tel" id="telefono_whatsapp" name="telefono_whatsapp" class="form-control" placeholder="Ej. 0987654321" maxlength="13" inputmode="tel" data-validar="telefono" data-solo-digitos="13" data-permite-mas value="<?= htmlspecialchars(valorFormulario($formOld, $usuarioEditar ?: [], 'telefono_whatsapp', $esEdicion)) ?>">
                        </div>

                        <?php $valorVivienda = htmlspecialchars(valorFormulario($formOld, $usuarioEditar ?: [], 'numero_vivienda', $esEdicion)); ?>
                        <div class="form-group">
                            <label for="numero_vivienda">Vivienda / Unidad <small style="color:var(--text-muted);">(catálogo)</small></label>
                            <select id="numero_vivienda" name="numero_vivienda" class="form-control">
                                <option value="">-- Sin asignar --</option>
                                <?php foreach ($viviendas as $v): ?>
                                    <option value="<?= htmlspecialchars($v['codigo']) ?>" <?= $valorVivienda === $v['codigo'] ? 'selected' : '' ?>><?= htmlspecialchars($v['codigo']) ?></option>
                                <?php endforeach; ?>
                                <?php if ($valorVivienda !== '' && !in_array($valorVivienda, array_column($viviendas, 'codigo'), true)): ?>
                                    <option value="<?= $valorVivienda ?>" selected><?= $valorVivienda ?> (fuera de catálogo)</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="password">Contraseña <?= $esEdicion ? '(dejar vacio para no cambiar)' : '' ?></label>
                            <div style="display:flex; gap:0.5rem;">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" <?= $esEdicion ? '' : 'required' ?> minlength="6" maxlength="72" autocomplete="new-password">
                                <button type="button" class="btn btn-sm btn-outline" onclick="alternarVer('password', this)" title="Mostrar / ocultar"><i class="fa-solid fa-eye"></i></button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rol">Rol en el Sistema</label>
                            <select id="rol" name="rol" class="form-control" required>
                                <option value="RESIDENTE" <?= ($esEdicion ? ($usuarioEditar['rol'] ?? '') === 'RESIDENTE' : true) && (($formOld['rol'] ?? null) === null || ($formOld['rol'] ?? 'RESIDENTE') === 'RESIDENTE') ? 'selected' : '' ?>>RESIDENTE (Copropietario)</option>
                                <option value="DIRECTIVA" <?= ($formOld['rol'] ?? ($esEdicion ? ($usuarioEditar['rol'] ?? '') : '')) === 'DIRECTIVA' ? 'selected' : '' ?>>DIRECTIVA</option>
                                <option value="ADMINISTRADOR" <?= ($formOld['rol'] ?? ($esEdicion ? ($usuarioEditar['rol'] ?? '') : '')) === 'ADMINISTRADOR' ? 'selected' : '' ?>>ADMINISTRADOR</option>
                            </select>
                        </div>

                        <div class="form-actions span-full">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid <?= $esEdicion ? 'fa-floppy-disk' : 'fa-user-check' ?>"></i>
                                <?= $esEdicion ? 'Guardar Cambios' : 'Registrar Usuario' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Catalogo de Viviendas -->
            <section class="card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-house-circle-check"></i> Catálogo de Viviendas</h2>
                    <span class="badge badge-info"><?= count($viviendas) ?> vivienda(s)</span>
                </div>
                <div class="card-body">
                    <form action="../../controllers/ViviendaController.php" method="POST" style="display:flex; flex-wrap:wrap; gap:0.6rem; margin-bottom:1rem; align-items:center;">
                        <input type="hidden" name="action" value="crear_vivienda">
                        <input type="text" name="codigo" class="form-control" placeholder="Ej. Casa 12 / Dpto 302" maxlength="30" required style="max-width:260px;">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Agregar al catálogo</button>
                    </form>
                    <?php if (empty($viviendas)): ?>
                        <p class="text-center">Aun no hay viviendas en el catálogo. Agregalas aquí o se importaran automaticamente desde los usuarios existentes.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table" data-por-pagina="8">
                                <thead>
                                    <tr><th>Codigo</th><th>Usuarios asignados</th><th>Estado</th><th>Acciones</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($viviendas as $v):
                                        $asignados = 0;
                                        try {
                                            $stmtAsig = Database::obtenerConexion()->prepare("SELECT COUNT(*) FROM usuarios WHERE TRIM(numero_vivienda) = :c");
                                            $stmtAsig->execute([':c' => $v['codigo']]);
                                            $asignados = (int)$stmtAsig->fetchColumn();
                                        } catch (Exception $ex) { $asignados = 0; }
                                        $activa = (int)$v['activa'] === 1;
                                    ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($v['codigo']) ?></strong></td>
                                            <td><?= $asignados ?> usuario(s)</td>
                                            <td><span class="badge <?= $activa ? 'badge-success' : 'badge-danger' ?>"><?= $activa ? 'DISPONIBLE' : 'INACTIVA' ?></span></td>
                                            <td>
                                                <div class="btn-group">
                                                    <form action="../../controllers/ViviendaController.php" method="POST" onsubmit="return confirm('<?= $activa ? 'Desactivar' : 'Activar' ?> esta vivienda?');">
                                                        <input type="hidden" name="action" value="<?= $activa ? 'desactivar_vivienda' : 'activar_vivienda' ?>">
                                                        <input type="hidden" name="id_vivienda" value="<?= (int)$v['id_vivienda'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline" title="<?= $activa ? 'Desactivar' : 'Activar' ?>"><i class="fa-solid fa-<?= $activa ? 'eye-slash' : 'eye' ?>"></i></button>
                                                    </form>
                                                    <?php if ($asignados === 0): ?>
                                                        <form action="../../controllers/ViviendaController.php" method="POST" onsubmit="return confirm('Eliminar esta vivienda del catálogo?');">
                                                            <input type="hidden" name="action" value="eliminar_vivienda">
                                                            <input type="hidden" name="id_vivienda" value="<?= (int)$v['id_vivienda'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Listado de Usuarios -->
            <section class="card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-users"></i> Listado de Usuarios Registrados</h2>
                </div>
                <div class="card-body">
                    <div class="tabla-toolbar">
                        <div class="filtro-grupo">
                            <span class="filtro-etiqueta">Buscar</span>
                            <form method="GET" class="buscador-tabla">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar por nombre, cédula, correo o vivienda...">
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Cédula</th>
                                    <th>Nombres</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Vivienda</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No hay usuarios registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $u):
                                        $estadoUsuario = strtoupper($u['estado'] ?? 'ACTIVO');
                                        $estaBloqueado = $estadoUsuario !== 'ACTIVO';
                                        $esPropio = (int)$u['id_usuario'] === (int)($_SESSION['id_usuario'] ?? 0);
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($u['cedula'] ?? 'N/A') ?></td>
                                            <td><strong><?= htmlspecialchars($u['nombres'] ?? 'N/A') ?></strong></td>
                                            <td><?= htmlspecialchars($u['correo'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($u['telefono_whatsapp'] ?? 'N/A') ?></td>
                                            <td><span class="badge badge-info"><?= htmlspecialchars($u['numero_vivienda'] ?? 'Sin Asignar') ?></span></td>
                                            <td><span class="badge badge-warning"><?= htmlspecialchars($u['rol'] ?? 'RESIDENTE') ?></span></td>
                                            <td>
                                                <span class="badge <?= $estaBloqueado ? 'badge-danger' : 'badge-success' ?>">
                                                    <?= $estaBloqueado ? 'BLOQUEADO' : 'ACTIVO' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="usuarios.php?editar=<?= (int)$u['id_usuario'] ?>" class="btn btn-sm btn-outline" title="Editar datos">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline" title="Cambiar contraseña" onclick="abrirModalClave(<?= (int)$u['id_usuario'] ?>, '<?= htmlspecialchars(addslashes($u['nombres'] ?? '')) ?>')">
                                                        <i class="fa-solid fa-key"></i>
                                                    </button>
                                                    <?php if (!$esPropio): ?>
                                                        <form action="../../controllers/UsuarioController.php" method="POST" style="display:inline;" onsubmit="return confirm('<?= $estaBloqueado ? 'Activar' : 'Bloquear' ?> a este usuario?');">
                                                            <input type="hidden" name="action" value="cambiar_estado_usuario">
                                                            <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                                            <input type="hidden" name="nuevo_estado" value="<?= $estaBloqueado ? 'ACTIVO' : 'BLOQUEADO' ?>">
                                                            <button type="submit" class="btn btn-sm <?= $estaBloqueado ? 'btn-success' : 'btn-danger' ?>" title="<?= $estaBloqueado ? 'Activar' : 'Bloquear' ?>">
                                                                <i class="fa-solid fa-<?= $estaBloqueado ? 'unlock' : 'lock' ?>"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    $total = $totalUsuarios;
                    include __DIR__ . '/../partials/paginacion.php';
                    ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal: Cambiar contrasena de un usuario -->
    <div id="modalClave" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:2000; align-items:center; justify-content:center;">
        <div style="background:#fff; color:#222; border-radius:8px; padding:1.5rem 2rem; width:min(420px, 92vw); box-shadow:0 10px 40px rgba(0,0,0,0.3);">
            <h3 style="margin-top:0;"><i class="fa-solid fa-key"></i> Cambiar Contraseña</h3>
            <p id="modalClaveUsuario" style="font-weight:600; margin-bottom:1rem;"></p>
            <form action="../../controllers/UsuarioController.php" method="POST" id="formClave">
                <input type="hidden" name="action" value="cambiar_clave_usuario">
                <input type="hidden" name="id_usuario" id="claveIdUsuario" value="">
                <div class="form-group" style="margin-bottom:0.75rem;">
                    <label for="nueva_clave">Nueva contraseña (min. 6 caracteres) *</label>
                    <div style="display:flex; gap:0.5rem;">
                        <input type="password" id="nueva_clave" name="nueva_clave" class="form-control" required minlength="6">
                        <button type="button" class="btn btn-sm btn-outline" onclick="alternarVer('nueva_clave', this)"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:1rem;">
                    <label for="confirmar_clave">Confirmar contraseña *</label>
                    <div style="display:flex; gap:0.5rem;">
                        <input type="password" id="confirmar_clave" name="confirmar_clave" class="form-control" required minlength="6">
                        <button type="button" class="btn btn-sm btn-outline" onclick="alternarVer('confirmar_clave', this)"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="cerrarModalClave()">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function alternarVer(idInput, btn) {
            var input = document.getElementById(idInput);
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            var icono = btn.querySelector('i');
            if (icono) icono.className = input.type === 'text' ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        }

        function abrirModalClave(id, nombre) {
            document.getElementById('claveIdUsuario').value = id;
            document.getElementById('modalClaveUsuario').textContent = nombre;
            document.getElementById('formClave').reset();
            document.getElementById('modalClave').style.display = 'flex';
        }

        function cerrarModalClave() {
            document.getElementById('modalClave').style.display = 'none';
        }
        document.getElementById('modalClave').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalClave();
        });
    </script>

    <script src="../../public/js/sidebar.js"></script>
    <script src="../../public/js/tablas.js?v=3"></script>
</body>

</html>