<?php
$tituloPagina = 'Grupos de Medicamentos';
$paginaActual = 'grupos';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Grupos de Medicamentos</h2>
        <button class="btn btn-primario" onclick="abrirModalCrear()">
            <i data-lucide="plus-circle"></i> Nuevo Grupo
        </button>
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
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($grupos)): ?>
                <tr>
                    <td colspan="4" class="estado-vacio">
                        <i data-lucide="package"></i>
                        <h3>Sin grupos registrados</h3>
                        <p>Agregue el primer grupo de medicamentos</p>
                    </td>
                </tr>
                <?php else: foreach ($grupos as $g): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($g['codigo']) ?></strong></td>
                    <td><?= htmlspecialchars($g['nombre']) ?></td>
                    <td>
                        <?php if ($g['activo']): ?>
                        <span class="badge badge-verde">Activo</span>
                        <?php else: ?>
                        <span class="badge badge-rojo">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex; gap:4px;">
                            <button class="btn-icono" title="Editar" onclick="abrirModalEditar(<?= $g['id'] ?>, '<?= htmlspecialchars($g['codigo'], ENT_QUOTES) ?>', '<?= htmlspecialchars($g['nombre'], ENT_QUOTES) ?>')">
                                <i data-lucide="edit-3"></i>
                            </button>
                            <a href="index.php?url=grupos/toggle&id=<?= $g['id'] ?>" class="btn-icono" title="<?= $g['activo'] ? 'Desactivar' : 'Activar' ?>">
                                <i data-lucide="toggle-<?= $g['activo'] ? 'left' : 'right' ?>"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear Grupo -->
<div id="modalCrear" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">Nuevo Grupo de Medicamento</h3>
            <button class="modal-cerrar" onclick="document.getElementById('modalCrear').classList.remove('activo')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form method="POST" action="index.php?url=grupos/crear">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-grupo">
                <label class="form-etiqueta">Código <span style="color:var(--error);">*</span></label>
                <input type="text" name="codigo" class="form-control" placeholder="Ej: 001" required style="text-transform:uppercase;">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Nombre <span style="color:var(--error);">*</span></label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: Inyectables" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundario" onclick="document.getElementById('modalCrear').classList.remove('activo')">Cancelar</button>
                <button type="submit" class="btn btn-primario">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Grupo -->
<div id="modalEditar" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">Editar Grupo de Medicamento</h3>
            <button class="modal-cerrar" onclick="document.getElementById('modalEditar').classList.remove('activo')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <form method="POST" action="index.php?url=grupos/editar">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-grupo">
                <label class="form-etiqueta">Código <span style="color:var(--error);">*</span></label>
                <input type="text" name="codigo" id="edit-codigo" class="form-control" required style="text-transform:uppercase;">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Nombre <span style="color:var(--error);">*</span></label>
                <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
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
function abrirModalEditar(id, codigo, nombre) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-codigo').value = codigo;
    document.getElementById('edit-nombre').value = nombre;
    document.getElementById('modalEditar').classList.add('activo');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>