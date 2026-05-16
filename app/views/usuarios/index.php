<?php
/** SIGFA - Vista: Gestión de Usuarios (Solo Administrador) */
$tituloPagina = 'Usuarios';
$paginaActual = 'usuarios';
require_once __DIR__ . '/../layouts/header.php';
?>

<?php if (!empty($exito)): ?><div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card card-compact fade-in" style="margin-bottom:1.5rem; border-left: 4px solid var(--azul-acento);">
    <div class="card-header" style="cursor:pointer;" onclick="toggleRegistro()">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="sidebar-logo-icono" style="width:32px; height:32px; border-radius:8px;"><i data-lucide="user-plus" style="width:16px; height:16px;"></i></div>
            <h2 class="card-titulo" style="font-size:1.1rem;">Registrar Nuevo Usuario</h2>
        </div>
        <button type="button" class="btn-icono" id="btn-toggle-registro" style="border:none; background:transparent;"><i data-lucide="chevron-up"></i></button>
    </div>
    <div id="contenedor-registro">
        <form method="POST" action="index.php?url=usuarios/crear" class="form-compact">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div style="padding: 1.5rem;">
                <!-- Datos de Identidad y Rol -->
                <div class="form-grid-4" style="margin-bottom:1rem;">
                    <div class="form-grupo">
                        <label class="form-etiqueta">Cédula *</label>
                        <input type="text" name="cedula" class="form-control" required placeholder="V-12345678">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Rol / Permisos *</label>
                        <select name="rol" class="form-control" required>
                            <option value="Auxiliar_General">Auxiliar General</option>
                            <option value="Auxiliar_Alto_Costo">Auxiliar de Alto Costo</option>
                            <option value="Almacenista">Almacenista</option>
                            <option value="Farmaceutico">Farmacéutico</option>
                            <option value="Kardista">Kardista</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Apellido *</label>
                        <input type="text" name="apellido" class="form-control" required>
                    </div>
                </div>

                <!-- Contacto y Seguridad -->
                <div class="form-grid-4">
                    <div class="form-grupo">
                        <label class="form-etiqueta">Correo Institucional *</label>
                        <input type="email" name="correo" class="form-control" required placeholder="usuario@sigfa.local">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Contraseña *</label>
                        <input type="password" name="clave" class="form-control" required minlength="6" placeholder="••••••••">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Confirmar Contraseña *</label>
                        <input type="password" name="clave_confirmar" class="form-control" required minlength="6" placeholder="••••••••">
                    </div>
                </div>

                <div style="display:flex; justify-content: flex-end; margin-top:1.5rem; padding-top:1rem; border-top:1px solid var(--gris-perla);">
                    <button type="reset" class="btn btn-secundario btn-sm" style="margin-right:8px;">Limpiar</button>
                    <button type="submit" class="btn btn-primario btn-sm"><i data-lucide="save"></i> Crear Cuenta de Usuario</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleRegistro() {
    const cont = document.getElementById('contenedor-registro');
    const btn = document.getElementById('btn-toggle-registro');
    if (cont.style.display === 'none') {
        cont.style.display = 'block';
        btn.innerHTML = '<i data-lucide="chevron-up"></i>';
    } else {
        cont.style.display = 'none';
        btn.innerHTML = '<i data-lucide="chevron-down"></i>';
    }
    lucide.createIcons();
}
</script>

<div class="card fade-in">
    <div class="card-header">
        <h2 class="card-titulo">Cuentas Registradas</h2>
        <span class="badge badge-azul"><?= count($usuarios ?? []) ?> Usuarios</span>
    </div>
    <?php if (empty($usuarios)): ?>
    <div class="estado-vacio"><i data-lucide="shield"></i><h3>Sin usuarios adicionales</h3></div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead><tr><th>Cédula</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Último Acceso</th></tr></thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['cedula']) ?></strong></td>
                    <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></td>
                    <td><?= htmlspecialchars($u['correo']) ?></td>
                    <td>
                        <?php
                        $rolClase = match($u['rol']) {
                            'Administrador' => 'badge-rojo',
                            'Auxiliar_Alto_Costo' => 'badge-ambar',
                            'Almacenista' => 'badge-verde',
                            'Farmaceutico' => 'badge-azul',
                            'Kardista' => 'badge-gris',
                            default => 'badge-azul'
                        };
                        $rolLabel = match($u['rol']) {
                            'Auxiliar_General' => 'Auxiliar General',
                            'Auxiliar_Alto_Costo' => 'Aux. Alto Costo',
                            'Farmaceutico' => 'Farmacéutico',
                            default => $u['rol']
                        };
                        ?>
                        <span class="badge <?= $rolClase ?>"><?= $rolLabel ?></span>
                    </td>
                    <td><?= $u['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) : 'Nunca' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
