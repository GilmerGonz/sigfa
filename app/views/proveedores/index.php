<?php
/** SIGFA - Vista: Proveedores — CRUD */
$tituloPagina = 'Proveedores';
$paginaActual = 'proveedores';
require_once __DIR__ . '/../layouts/header.php';
?>

<?php if (!empty($exito)): ?><div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card card-compact fade-in" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h2 class="card-titulo">Registrar Proveedor</h2>
    </div>
    <form method="POST" action="index.php?url=proveedores/crear" class="form-compact">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="form-grid-3">
            <div class="form-grupo">
                <label class="form-etiqueta">RIF *</label>
                <input type="text" name="rif" class="form-control" required placeholder="J-12345678-9">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Razón Social *</label>
                <input type="text" name="razon_social" class="form-control" required>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Persona de Contacto</label>
                <input type="text" name="contacto_nombre" class="form-control">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Teléfono</label>
                <input type="text" name="telefono" class="form-control">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Correo</label>
                <input type="email" name="correo" class="form-control">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Dirección</label>
                <input type="text" name="direccion" class="form-control">
            </div>
        </div>
        <div style="display:flex; justify-content: flex-end; margin-top:0.5rem;">
            <button type="submit" class="btn btn-primario btn-sm"><i data-lucide="save"></i> Registrar Proveedor</button>
        </div>
    </form>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h2 class="card-titulo">Proveedores Registrados</h2>
        <span class="badge badge-azul"><?= count($proveedores ?? []) ?> Aliados</span>
    </div>
    <?php if (empty($proveedores)): ?>
    <div class="estado-vacio"><i data-lucide="truck"></i><h3>Sin proveedores registrados</h3></div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead><tr><th>RIF</th><th>Razón Social</th><th>Contacto</th><th>Teléfono</th><th>Correo</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($proveedores as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['rif']) ?></strong></td>
                    <td><?= htmlspecialchars($p['razon_social']) ?></td>
                    <td><?= htmlspecialchars($p['contacto_nombre'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['correo'] ?? '—') ?></td>
                    <td>
                        <div style="display:flex; gap:8px;">
                            <a href="index.php?url=proveedores/editar&id=<?= $p['id'] ?>" class="btn-icono" title="Editar Proveedor"><i data-lucide="edit"></i></a>
                            <?php if ($_SESSION['usuario_rol'] === 'Administrador'): ?>
                            <a href="javascript:void(0)" onclick="if(confirm('¿Desea desactivar a este proveedor?')) window.location.href='index.php?url=proveedores/eliminar&id=<?= $p['id'] ?>'" class="btn-icono btn-peligro" title="Desactivar Proveedor"><i data-lucide="trash-2"></i></a>
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


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
