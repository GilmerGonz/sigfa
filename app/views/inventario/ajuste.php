<?php
/** SIGFA - Vista: Ajuste Manual de Inventario Físico */
$tituloPagina = 'Ajuste Manual de Inventario';
$paginaActual = 'ajuste';
require_once __DIR__ . '/../layouts/header.php';

$inventario_exito = $_SESSION['inventario_exito'] ?? null;
$inventario_error = $_SESSION['inventario_error'] ?? null;
unset($_SESSION['inventario_exito'], $_SESSION['inventario_error']);
?>

<?php if (!empty($inventario_exito)): ?><div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($inventario_exito) ?></div><?php endif; ?>
<?php if (!empty($inventario_error)): ?><div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($inventario_error) ?></div><?php endif; ?>

<div class="card fade-in" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h2 class="card-titulo">Ajuste Manual de Inventario Físico</h2>
        <div class="badge badge-rojo">Solo Administrador</div>
    </div>
    <p style="color:var(--texto-secundario); margin-bottom:1.5rem;">
        Esta función permite corregir el stock y/o fecha de vencimiento de un lote. Se registrará en el Kardex con el motivo proporcionado.
    </p>
    <form method="GET" action="index.php" class="form-grid" style="grid-template-columns: 2fr 1fr; align-items:end;">
        <input type="hidden" name="url" value="inventario/ajuste">
        <div class="form-grupo" style="margin-bottom:0;">
            <label class="form-etiqueta">Seleccionar Medicamento</label>
            <select name="medicamento_id" class="form-control" onchange="this.form.submit()">
                <option value="">— Seleccione un medicamento —</option>
                <?php foreach ($medicamentos ?? [] as $m): ?>
                <option value="<?= $m['id'] ?>" <?= (isset($medicamento) && $medicamento['id'] == $m['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['codigo'] . ' — ' . $m['nombre_generico'] . ' ' . $m['concentracion']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div></div>
    </form>
</div>

<?php if (!empty($medicamento)): ?>
<!-- Info del medicamento -->
<div class="card fade-in" style="margin-bottom:1.5rem;">
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; text-align:center;">
        <div>
            <div style="font-size:0.75rem; color:var(--gris-suave); text-transform:uppercase; font-weight:600;">Código</div>
            <div style="font-family:'Outfit'; font-weight:700; font-size:1.2rem; margin-top:4px;"><?= htmlspecialchars($medicamento['codigo']) ?></div>
        </div>
        <div>
            <div style="font-size:0.75rem; color:var(--gris-suave); text-transform:uppercase; font-weight:600;">Medicamento</div>
            <div style="font-family:'Outfit'; font-weight:700; font-size:1.2rem; margin-top:4px;"><?= htmlspecialchars($medicamento['nombre_generico']) ?></div>
        </div>
        <div>
            <div style="font-size:0.75rem; color:var(--gris-suave); text-transform:uppercase; font-weight:600;">Concentración</div>
            <div style="font-family:'Outfit'; font-weight:700; font-size:1.2rem; margin-top:4px;"><?= htmlspecialchars($medicamento['concentracion']) ?></div>
        </div>
        <div>
            <div style="font-size:0.75rem; color:var(--gris-suave); text-transform:uppercase; font-weight:600;">Presentación</div>
            <div style="font-family:'Outfit'; font-weight:700; font-size:1.2rem; margin-top:4px;"><?= htmlspecialchars($medicamento['presentacion']) ?></div>
        </div>
    </div>
</div>

<!-- Lotes disponibles para ajustar -->
<?php if (!empty($lotes)): ?>
<div class="card fade-in">
    <div class="card-header"><h3 class="card-titulo">Lotes Disponibles</h3></div>
    <div class="tabla-container">
        <table>
            <thead><tr><th>Lote</th><th>F. Fabricación</th><th>F. Vencimiento</th><th>Cant. Recibida</th><th>Cant. Disponible</th><th>Proveedor</th><th>Acción</th></tr></thead>
            <tbody>
                <?php foreach ($lotes as $l): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($l['numero_lote']) ?></strong></td>
                    <td><?= !empty($l['fecha_fabricacion']) ? date('d/m/Y', strtotime($l['fecha_fabricacion'])) : '—' ?></td>
                    <td>
                        <?php
                        $diasVenc = (int)((strtotime($l['fecha_vencimiento']) - time()) / 86400);
                        $claseDias = $diasVenc <= 7 ? 'badge-rojo' : ($diasVenc <= 30 ? 'badge-ambar' : 'badge-verde');
                        ?>
                        <span class="badge <?= $claseDias ?>"><?= date('d/m/Y', strtotime($l['fecha_vencimiento'])) ?></span>
                        <div style="font-size:0.7rem; color:var(--gris-suave);"><?= $diasVenc ?> días</div>
                    </td>
                    <td><?= $l['cantidad_recibida'] ?></td>
                    <td><strong><?= $l['cantidad_disponible'] ?></strong></td>
                    <td><?= htmlspecialchars($l['proveedor_nombre'] ?? '—') ?></td>
                    <td>
                        <button type="button" class="btn btn-primario btn-sm" onclick="abrirModalAjustar(<?= $l['id'] ?>, '<?= htmlspecialchars($l['numero_lote'], ENT_QUOTES) ?>', <?= $l['cantidad_disponible'] ?>, '<?= $l['fecha_vencimiento'] ?>')">
                            <i data-lucide="edit-3"></i> Ajustar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card fade-in">
    <div class="estado-vacio"><i data-lucide="package"></i><h3>Sin lotes disponibles</h3><p>No hay lotes activos para este medicamento.</p></div>
</div>
<?php endif; ?>

<!-- Modal Ajuste -->
<div class="modal-overlay" id="modal-ajustar">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">Ajuste Manual de Inventario</h3>
            <button type="button" class="modal-cerrar" onclick="document.getElementById('modal-ajustar').classList.remove('activo')"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" action="index.php?url=inventario/ajustar-post">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="lote_id" id="ajustar-lote-id">
            <div class="alerta alerta-info" style="margin-bottom:1.5rem;">
                <i data-lucide="info"></i>
                <div>Lote: <strong id="ajustar-lote-nombre"></strong>. Stock actual: <strong id="ajustar-stock-actual"></strong> unidades.</div>
            </div>
            <div class="form-grid">
                <div class="form-grupo">
                    <label class="form-etiqueta">Nueva Cantidad (Stock Físico)</label>
                    <input type="number" name="nueva_cantidad" id="ajustar-nueva-cantidad" class="form-control" min="0" required>
                </div>
                <div class="form-grupo">
                    <label class="form-etiqueta">Nueva Fecha de Vencimiento</label>
                    <input type="date" name="nueva_fecha_vencimiento" id="ajustar-nueva-fecha" class="form-control" required>
                </div>
                <div class="form-grupo col-span-2">
                    <label class="form-etiqueta">Motivo del Ajuste <span style="color:var(--error);">*</span></label>
                    <select name="motivo" class="form-control" required>
                        <option value="">— Seleccione motivo —</option>
                        <option value="Conteo físico">Conteo físico</option>
                        <option value="Error de registro">Error de registro</option>
                        <option value="Mercancía dañada">Mercancía dañada</option>
                        <option value="Mercancía vencida">Mercancía vencida</option>
                        <option value="Corrección">Corrección</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundario" onclick="document.getElementById('modal-ajustar').classList.remove('activo')">Cancelar</button>
                <button type="submit" class="btn btn-primario">Guardar Ajuste</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalAjustar(loteId, loteNumero, stockActual, fechaVenc) {
    document.getElementById('ajustar-lote-id').value = loteId;
    document.getElementById('ajustar-lote-nombre').textContent = loteNumero;
    document.getElementById('ajustar-stock-actual').textContent = stockActual;
    document.getElementById('ajustar-nueva-cantidad').value = stockActual;
    document.getElementById('ajustar-nueva-fecha').value = fechaVenc;
    document.getElementById('modal-ajustar').classList.add('activo');
}
</script>

<?php else: ?>
<div class="card fade-in">
    <div class="estado-vacio">
        <i data-lucide="package"></i>
        <h3>Seleccione un medicamento</h3>
        <p>Elija un medicamento del catálogo para ver sus lotes y realizar ajustes</p>
    </div>
</div>
<?php endif; ?>

</main></body></html>