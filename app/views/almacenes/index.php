<?php
$tituloPagina = 'Almacenes';
$paginaActual = 'almacenes';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Registro de Almacenes</h2>
        <?php if ($esAdmin): ?>
        <button class="btn btn-primario" onclick="abrirModalCrear()">
            <i data-lucide="plus-circle"></i> Nuevo Almacén
        </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito">
        <i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="alerta alerta-error">
        <i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Ubicación</th>
                    <th>Estado</th>
                    <?php if ($esAdmin): ?><th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($almacenes)): ?>
                <tr>
                    <td colspan="<?= $esAdmin ? 6 : 5 ?>" class="estado-vacio">
                        <i data-lucide="warehouse"></i>
                        <h3>Sin almacenes registrados</h3>
                        <p>Agregue el primer almacén del sistema</p>
                    </td>
                </tr>
                <?php else: foreach ($almacenes as $a): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($a['codigo']) ?></strong></td>
                    <td><?= htmlspecialchars($a['nombre']) ?></td>
                    <td><span class="badge badge-azul"><?= htmlspecialchars($a['tipo'] ?? 'General') ?></span></td>
                    <td><?= htmlspecialchars($a['ubicacion'] ?? '-') ?></td>
                    <td>
                        <?php if ($a['activo']): ?>
                        <span class="badge badge-verde">Activo</span>
                        <?php else: ?>
                        <span class="badge badge-rojo">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <?php if ($esAdmin): ?>
                    <td>
                        <div style="display:flex; gap:4px;">
                            <button class="btn-icono" title="Editar" onclick="abrirModalEditar(<?= $a['id'] ?>, '<?= htmlspecialchars($a['codigo'], ENT_QUOTES) ?>', '<?= htmlspecialchars($a['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($a['tipo'] ?? 'General', ENT_QUOTES) ?>', '<?= htmlspecialchars($a['ubicacion'] ?? '', ENT_QUOTES) ?>')">
                                <i data-lucide="edit-3"></i>
                            </button>
                            <a href="index.php?url=almacenes/toggle&id=<?= $a['id'] ?>" class="btn-icono" title="<?= $a['activo'] ? 'Desactivar' : 'Activar' ?>">
                                <i data-lucide="toggle-<?= $a['activo'] ? 'left' : 'right' ?>"></i>
                            </a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear Almacén -->
<div id="modalCrear" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">Nuevo Almacén</h3>
            <button class="modal-cerrar" onclick="document.getElementById('modalCrear').classList.remove('activo')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form method="POST" action="index.php?url=almacenes/crear">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-grid">
                <div class="form-grupo">
                    <label class="form-etiqueta">Código <span style="color:var(--error);">*</span></label>
                    <input type="text" name="codigo" class="form-control" placeholder=" Ej: ALM-001" required style="text-transform:uppercase;">
                </div>
                <div class="form-grupo">
                    <label class="form-etiqueta">Nombre <span style="color:var(--error);">*</span></label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Farmacia Central" required>
                </div>
                <div class="form-grupo">
                    <label class="form-etiqueta">Tipo <span style="color:var(--error);">*</span></label>
                    <select name="tipo" class="form-control" required>
                        <option value="General">General</option>
                        <option value="Detal">Detal</option>
                        <option value="Alto_Costo">Alto Costo</option>
                    </select>
                </div>
                <div class="form-grupo col-span-2">
                    <label class="form-etiqueta">Ubicación</label>
                    <input type="text" name="ubicacion" class="form-control" placeholder="Ej: Planta baja, Sector A">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundario" onclick="document.getElementById('modalCrear').classList.remove('activo')">Cancelar</button>
                <button type="submit" class="btn btn-primario">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Almacén -->
<div id="modalEditar" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">Editar Almacén</h3>
            <button class="modal-cerrar" onclick="document.getElementById('modalEditar').classList.remove('activo')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form method="POST" action="index.php?url=almacenes/editar">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-grid">
                <div class="form-grupo">
                    <label class="form-etiqueta">Código <span style="color:var(--error);">*</span></label>
                    <input type="text" name="codigo" id="edit-codigo" class="form-control" required style="text-transform:uppercase;">
                </div>
                <div class="form-grupo">
                    <label class="form-etiqueta">Nombre <span style="color:var(--error);">*</span></label>
                    <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                </div>
                <div class="form-grupo">
                    <label class="form-etiqueta">Tipo <span style="color:var(--error);">*</span></label>
                    <select name="tipo" id="edit-tipo" class="form-control" required>
                        <option value="General">General</option>
                        <option value="Detal">Detal</option>
                        <option value="Alto_Costo">Alto Costo</option>
                    </select>
                </div>
                <div class="form-grupo col-span-2">
                    <label class="form-etiqueta">Ubicación</label>
                    <input type="text" name="ubicacion" id="edit-ubicacion" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundario" onclick="document.getElementById('modalEditar').classList.remove('activo')">Cancelar</button>
                <button type="submit" class="btn btn-primario">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalCrear() {
    document.getElementById('modalCrear').classList.add('activo');
}
function abrirModalEditar(id, codigo, nombre, tipo, ubicacion) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-codigo').value = codigo;
    document.getElementById('edit-nombre').value = nombre;
    document.getElementById('edit-tipo').value = tipo;
    document.getElementById('edit-ubicacion').value = ubicacion;
    document.getElementById('modalEditar').classList.add('activo');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>