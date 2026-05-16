<?php
$tituloPagina = 'Devoluciones a Proveedores';
$paginaActual = 'devoluciones';
require_once __DIR__ . '/../layouts/header.php';

$medicamentos = $medicamentos ?? [];
$proveedores = $proveedores ?? [];
$devoluciones = $devoluciones ?? [];
?>

<?php if (!empty($exito)): ?>
<div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card fade-in">
    <div class="card-header">
        <h2 class="card-titulo">Registrar Devolución a Proveedor</h2>
    </div>
    <form method="POST" action="index.php?url=devoluciones/crear">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div class="form-grid-2">
            <div class="form-grupo">
                <label class="form-etiqueta">Proveedor *</label>
                <select name="proveedor_id" id="proveedor_id" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($proveedores ?? [] as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['razon_social'] . ' (' . $p['rif'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Medicamento *</label>
                <select name="medicamento_id" id="medicamento_id" class="form-control" required onchange="cargarLotes()">
                    <option value="">Seleccione...</option>
                    <?php foreach ($medicamentos ?? [] as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre_generico'] . ' ' . $m['concentracion']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Lote *</label>
                <select name="lote_id" id="lote_id" class="form-control" required>
                    <option value="">Seleccione un medicamento primero...</option>
                </select>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Cantidad *</label>
                <input type="number" name="cantidad" id="cantidad" class="form-control" required min="1" placeholder="Cantidad a devolver">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Motivo *</label>
                <select name="motivo" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <option value="Vencido">Vencido</option>
                    <option value="Dañado">Dañado</option>
                    <option value="Recall">Recall</option>
                    <option value="Error suministro">Error de suministro</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Número de Comprobante</label>
                <input type="text" name="numero_comprobante" class="form-control" placeholder="DEV-2026-0001">
            </div>
            <div class="form-grupo col-span-2">
                <label class="form-etiqueta">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2" placeholder="Detalles adicionales..."></textarea>
            </div>
        </div>

        <div class="modal-footer" style="border-top:none;padding-top:1rem;">
            <button type="submit" class="btn btn-primario"><i data-lucide="rotate-ccw"></i> Registrar Devolución</button>
        </div>
    </form>
</div>

<div class="card fade-in" style="margin-top:1.5rem;">
    <div class="card-header">
        <h2 class="card-titulo">Historial de Devoluciones</h2>
    </div>
    <?php if (empty($devoluciones)): ?>
    <div class="estado-vacio">
        <i data-lucide="package-x"></i>
        <h3>No hay devoluciones registradas</h3>
    </div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Medicamento</th>
                    <th>Lote</th>
                    <th>Cantidad</th>
                    <th>Motivo</th>
                    <th>Comprobante</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devoluciones as $dev): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($dev['fecha_devolucion'])) ?></td>
                    <td><?= htmlspecialchars($dev['proveedor_nombre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($dev['nombre_generico'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($dev['numero_lote'] ?? '-') ?></td>
                    <td><?= (int) $dev['cantidad'] ?></td>
                    <td><?= htmlspecialchars($dev['motivo']) ?></td>
                    <td><?= htmlspecialchars($dev['numero_comprobante'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function cargarLotes() {
    const medicamentoId = document.getElementById('medicamento_id').value;
    const loteSelect = document.getElementById('lote_id');
    
    if (!medicamentoId) {
        loteSelect.innerHTML = '<option value="">Seleccione un medicamento primero...</option>';
        return;
    }

    fetch(`index.php?url=devoluciones/ajaxLotes&medicamento_id=${medicamentoId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            let html = '<option value="">Seleccione...</option>';
            data.lotes.forEach(l => {
                html += `<option value="${l.id}" data-stock="${l.cantidad_disponible}">${l.numero_lote} — Vence: ${l.fecha_vencimiento} — Stock: ${l.cantidad_disponible}</option>`;
            });
            loteSelect.innerHTML = html;
            if (window.lucide) window.lucide.createIcons();
        });
}

document.getElementById('cantidad').addEventListener('input', function() {
    const loteSelect = document.getElementById('lote_id');
    const option = loteSelect.options[loteSelect.selectedIndex];
    const stockMax = parseInt(option?.dataset?.stock || 0);
    const cantidad = parseInt(this.value);
    
    if (cantidad > stockMax) {
        this.setCustomValidity(`La cantidad no puede exceder el stock disponible (${stockMax})`);
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>