<?php
/** SIGFA - Vista: Editar Proveedor */
$tituloPagina = 'Editar Proveedor';
$paginaActual = 'proveedores';
require_once __DIR__ . '/../layouts/header.php';
?>

<?php if (!empty($error)): ?><div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card fade-in" style="max-width: 800px; margin: 2rem auto;">
    <div class="card-header">
        <h2 class="card-titulo">Editar Información del Proveedor</h2>
        <a href="index.php?url=proveedores" class="btn btn-secundario btn-sm"><i data-lucide="arrow-left"></i> Volver</a>
    </div>
    
    <form method="POST" action="index.php?url=proveedores/actualizar" class="form-compact">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="id" value="<?= $proveedor['id'] ?>">
        
        <div class="form-grid">
            <div class="form-grupo">
                <label class="form-etiqueta">RIF *</label>
                <input type="text" name="rif" class="form-control" required value="<?= htmlspecialchars($proveedor['rif']) ?>">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Razón Social *</label>
                <input type="text" name="razon_social" class="form-control" required value="<?= htmlspecialchars($proveedor['razon_social']) ?>">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Persona de Contacto</label>
                <input type="text" name="contacto_nombre" class="form-control" value="<?= htmlspecialchars($proveedor['contacto_nombre'] ?? '') ?>">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($proveedor['telefono'] ?? '') ?>">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Correo Electrónico</label>
                <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($proveedor['correo'] ?? '') ?>">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Estatus</label>
                <select name="activo" class="form-control">
                    <option value="1" <?= $proveedor['activo'] == 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= $proveedor['activo'] == 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="form-grupo col-span-2">
                <label class="form-etiqueta">Dirección</label>
                <textarea name="direccion" class="form-control" rows="2"><?= htmlspecialchars($proveedor['direccion'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="modal-footer" style="border-top: 1px solid var(--gris-perla); padding-top: 1.5rem;">
            <button type="submit" class="btn btn-primario"><i data-lucide="refresh-cw"></i> Actualizar Proveedor</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
