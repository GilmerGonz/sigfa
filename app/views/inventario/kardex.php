<?php
/** SIGFA - Vista: Kardex de Auditoría */
$tituloPagina = 'Kardex';
$paginaActual = 'kardex';
require_once __DIR__ . '/../layouts/header.php';

$inventario_exito = $_SESSION['inventario_exito'] ?? null;
$inventario_error = $_SESSION['inventario_error'] ?? null;
unset($_SESSION['inventario_exito'], $_SESSION['inventario_error']);
?>

<?php if (!empty($inventario_exito)): ?><div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($inventario_exito) ?></div><?php endif; ?>
<?php if (!empty($inventario_error)): ?><div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($inventario_error) ?></div><?php endif; ?>

<div class="card fade-in" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h2 class="card-titulo">Consultar Kardex</h2>
    </div>
    <form method="GET" action="index.php" class="form-grid" style="grid-template-columns: 2fr 1fr; align-items:end;">
        <input type="hidden" name="url" value="inventario/kardex">
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

<!-- Lotes activos -->
<?php if (!empty($lotes)): ?>
<div class="card fade-in" style="margin-bottom:1.5rem;">
    <div class="card-header"><h3 class="card-titulo">Lotes Activos</h3></div>
    <div class="tabla-container">
        <table>
            <thead><tr><th>Lote</th><th>F. Vencimiento</th><th>Recibido</th><th>Disponible</th><th>Chofer / Placa</th><th>Proveedor</th><?php if (($_SESSION['usuario_rol'] ?? '') === 'Administrador'): ?><th>Acciones</th><?php endif; ?></tr></thead>
            <tbody>
                <?php foreach ($lotes as $l): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($l['numero_lote']) ?></strong></td>
                    <td>
                        <?php
                        $diasVenc = (int)((strtotime($l['fecha_vencimiento']) - time()) / 86400);
                        $claseDias = $diasVenc <= 7 ? 'badge-rojo' : ($diasVenc <= 30 ? 'badge-ambar' : 'badge-verde');
                        ?>
                        <span class="badge <?= $claseDias ?>"><?= date('d/m/Y', strtotime($l['fecha_vencimiento'])) ?></span>
                    </td>
                    <td><?= $l['cantidad_recibida'] ?></td>
                    <td><strong><?= $l['cantidad_disponible'] ?></strong></td>
                    <td style="font-size:0.8rem;">
                        <?php if (!empty($l['chofer_nombre'])): ?>
                            <i data-lucide="truck" style="width:12px; height:12px;"></i> <?= htmlspecialchars($l['chofer_nombre']) ?> 
                            <br><small class="badge badge-azul" style="padding:2px 4px;"><?= htmlspecialchars($l['placa_vehiculo'] ?? 'S/P') ?></small>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($l['proveedor_nombre'] ?? '—') ?></td>
                    <?php if (($_SESSION['usuario_rol'] ?? '') === 'Administrador'): ?>
                    <td>
                        <div style="display:flex; gap:4px;">
                            <button type="button" class="btn-icono" title="Ajustar Stock" onclick="abrirModalAjustar(<?= $l['id'] ?>, '<?= htmlspecialchars($l['numero_lote'], ENT_QUOTES) ?>', <?= $l['cantidad_disponible'] ?>, '<?= $l['fecha_vencimiento'] ?>')">
                                <i data-lucide="edit-3"></i>
                            </button>
                            <button type="button" class="btn-icono btn-peligro" title="Eliminar Lote" onclick="abrirModalEliminar(<?= $l['id'] ?>, '<?= htmlspecialchars($l['numero_lote'], ENT_QUOTES) ?>', <?= $l['cantidad_disponible'] ?>)">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Movimientos Kardex -->
<div class="card fade-in">
    <div class="card-header"><h3 class="card-titulo">Movimientos del Kardex</h3></div>
    <?php if (empty($kardex)): ?>
    <div class="estado-vacio"><i data-lucide="book-open"></i><h3>Sin movimientos registrados</h3></div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Cant.</th><th>Stock Ant.</th><th>Stock Post.</th><th>Motivo / Chofer</th><th>Usuario</th></tr></thead>
            <tbody>
                <?php foreach ($kardex as $k): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($k['fecha_movimiento'])) ?></td>
                    <td>
                        <?php
                        $tipoClase = match($k['tipo_movimiento']) {
                            'Entrada' => 'badge-verde', 'Salida' => 'badge-azul',
                            'Ajuste_Positivo', 'Devolucion' => 'badge-ambar',
                            default => 'badge-rojo'
                        };
                        ?>
                        <span class="badge <?= $tipoClase ?>"><?= str_replace('_', ' ', $k['tipo_movimiento']) ?></span>
                        <div style="font-size:0.75rem; color:var(--gris-suave); margin-top:2px;">
                            <?= htmlspecialchars($k['operacion'] ?? '') ?>
                        </div>
                    </td>
                    <td style="font-weight:700; color:<?= $k['cantidad'] >= 0 ? 'var(--exito)' : 'var(--error)' ?>">
                        <?= $k['cantidad'] >= 0 ? '+' . $k['cantidad'] : $k['cantidad'] ?>
                    </td>
                    <td style="color:var(--gris-suave); font-size:0.9rem;"><?= $k['stock_anterior'] ?></td>
                    <td><strong><?= $k['stock_posterior'] ?></strong></td>
                    <td>
                        <div style="font-size:0.85rem; font-weight:600;"><?= htmlspecialchars($k['motivo'] ?? '—') ?></div>
                        <?php if (!empty($k['observacion'])): ?>
                            <div style="font-size:0.8rem; color:var(--gris-suave); font-style:italic;"><?= htmlspecialchars($k['observacion']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($k['chofer_nombre'])): ?>
                            <div style="font-size:0.75rem; color:var(--azul-acento);"><i data-lucide="truck" style="width:10px; height:10px;"></i> <?= htmlspecialchars($k['chofer_nombre']) ?> [<?= htmlspecialchars($k['placa_vehiculo'] ?? 'S/P') ?>]</div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(($k['usuario_nombre'] ?? '') . ' ' . ($k['usuario_apellido'] ?? '')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="card fade-in">
    <div class="estado-vacio">
        <i data-lucide="book-open"></i>
        <h3>Seleccione un medicamento</h3>
        <p>Elija un medicamento del catálogo para ver su Kardex de movimientos</p>
    </div>
</div>
<?php endif; ?>

<!-- Modales Admin: Ajustar Stock / Eliminar Lote -->
<?php if (($_SESSION['usuario_rol'] ?? '') === 'Administrador'): ?>

<!-- Modal Ajustar Stock -->
<div class="modal-overlay" id="modal-ajustar">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">📦 Ajustar Stock del Lote</h3>
            <button type="button" class="modal-cerrar" onclick="this.closest('.modal-overlay').classList.remove('activo')"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" action="index.php?url=inventario/ajustar">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="lote_id" id="ajustar-lote-id">
            <div class="alerta alerta-info" style="margin-bottom:1.5rem;">
                <i data-lucide="info"></i>
                <div>Ajuste de inventario físico para el lote <strong id="ajustar-lote-nombre"></strong>. Stock actual: <strong id="ajustar-stock-actual"></strong> unidades.</div>
            </div>
            <div class="form-grid">
                <div class="form-grupo">
                    <label class="form-etiqueta">Nueva Cantidad *</label>
                    <input type="number" name="nueva_cantidad" id="ajustar-nueva-cantidad" class="form-control" min="0" required>
                </div>
                <div class="form-grupo">
                    <label class="form-etiqueta">Nueva Fecha de Vencimiento</label>
                    <input type="date" name="nueva_fecha_vencimiento" id="ajustar-nueva-fechavenc" class="form-control">
                </div>
                <div class="form-grupo" style="grid-column: span 2;">
                    <label class="form-etiqueta">Motivo del Ajuste *</label>
                    <input type="text" name="motivo" class="form-control" required placeholder="Ej: Conteo físico, pérdida, daño...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundario" onclick="this.closest('.modal-overlay').classList.remove('activo')">Cancelar</button>
                <button type="submit" class="btn btn-primario"><i data-lucide="save"></i> Aplicar Ajuste</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar Lote -->
<div class="modal-overlay" id="modal-eliminar">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">🗑️ Eliminar Lote</h3>
            <button type="button" class="modal-cerrar" onclick="this.closest('.modal-overlay').classList.remove('activo')"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" action="index.php?url=inventario/eliminar-lote">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="lote_id" id="eliminar-lote-id">
            <div class="alerta alerta-advertencia" style="margin-bottom:1.5rem;">
                <i data-lucide="alert-triangle"></i>
                <div>
                    <strong>Atención:</strong> Está por eliminar permanentemente el lote <strong id="eliminar-lote-nombre"></strong> 
                    con <strong id="eliminar-stock-actual"></strong> unidades. Solo se puede eliminar si no tiene despachos asociados.
                </div>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Motivo de Eliminación *</label>
                <input type="text" name="motivo" class="form-control" required placeholder="Ej: Lote registrado por error, duplicado...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundario" onclick="this.closest('.modal-overlay').classList.remove('activo')">Cancelar</button>
                <button type="submit" class="btn btn-peligro"><i data-lucide="trash-2"></i> Eliminar Lote</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalAjustar(loteId, loteNombre, stockActual, fechaVenc) {
    document.getElementById('ajustar-lote-id').value = loteId;
    document.getElementById('ajustar-lote-nombre').textContent = loteNombre;
    document.getElementById('ajustar-stock-actual').textContent = stockActual;
    document.getElementById('ajustar-nueva-cantidad').value = stockActual;
    document.getElementById('ajustar-nueva-fechavenc').value = fechaVenc;
    document.getElementById('modal-ajustar').classList.add('activo');
}

function abrirModalEliminar(loteId, loteNombre, stockActual) {
    document.getElementById('eliminar-lote-id').value = loteId;
    document.getElementById('eliminar-lote-nombre').textContent = loteNombre;
    document.getElementById('eliminar-stock-actual').textContent = stockActual;
    document.getElementById('modal-eliminar').classList.add('activo');
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
