<?php
$paginaActual = 'transferencias';
$tituloPagina = 'Transferencias';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Nueva Transferencia</h2>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alerta alerta-error">
        <i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito">
        <i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="index.php?url=transferencias/crear" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="form-grupo">
            <label class="form-etiqueta">Tipo de Transferencia *</label>
            <select name="tipo" id="tipoTransferencia" class="form-control" required onchange="toggleCampos()">
                <option value="">Seleccione...</option>
                <option value="Almacen_Almacen">Almacén a Almacén</option>
                <option value="Almacen_Servicio">Almacén a Servicio Médico</option>
            </select>
        </div>

        <div class="form-grupo">
            <label class="form-etiqueta">Almacén Origen *</label>
            <select name="almacen_origen" class="form-control" required>
                <option value="">Seleccione...</option>
                <?php foreach ($almacenes as $a): ?>
                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?> (<?= $a['tipo'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-grupo">
            <label class="form-etiqueta">Almacén Destino *</label>
            <select name="almacen_destino" class="form-control" required>
                <option value="">Seleccione...</option>
                <?php foreach ($almacenes as $a): ?>
                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?> (<?= $a['tipo'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-grupo" id="servicioDestinoGroup" style="display: none;">
            <label class="form-etiqueta">Servicio Destino</label>
            <select name="servicio_destino" class="form-control">
                <option value="">Seleccione...</option>
                <?php foreach ($servicios as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['codigo'] . ' - ' . $s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-grupo col-span-2">
            <label class="form-etiqueta">Medicamentos a Transferir</label>
            <div id="medicamentosContainer">
                <div class="medicamento-row form-grid" style="margin-bottom: 1rem;">
                    <select class="form-control medication-select" name="medicamento_id[]" onchange="cargarLotes(this)">
                        <option value="">Seleccione medicamento...</option>
                        <?php foreach ($medicamentos as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['codigo'] . ' - ' . $m['nombre_generico'] . ' (' . $m['concentracion'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-control lote-select" name="lote_id[]">
                        <option value="">Seleccione lote...</option>
                    </select>
                    <input type="number" class="form-control" name="cantidad[]" min="1" placeholder="Cantidad">
                    <button type="button" class="btn btn-peligro btn-sm" onclick="eliminarFila(this)"><i data-lucide="trash-2"></i></button>
                </div>
            </div>
            <button type="button" class="btn btn-secundario" onclick="agregarMedicamento()">
                <i data-lucide="plus"></i> Agregar Medicamento
            </button>
        </div>

        <div class="form-grupo col-span-2">
            <label class="form-etiqueta">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="3" placeholder="Observaciones adicionales..."></textarea>
        </div>

        <div class="form-grupo col-span-2">
            <button type="submit" class="btn btn-primario">
                <i data-lucide="send"></i> Registrar Transferencia
            </button>
        </div>
    </form>
</div>

<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h2 class="card-titulo">Historial de Transferencias</h2>
    </div>

    <form method="GET" class="form-grid" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="url" value="transferencias">
        <div class="form-grupo">
            <label class="form-etiqueta">Desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="<?= $_GET['fecha_desde'] ?? '' ?>">
        </div>
        <div class="form-grupo">
            <label class="form-etiqueta">Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="<?= $_GET['fecha_hasta'] ?? '' ?>">
        </div>
        <div class="form-grupo">
            <label class="form-etiqueta">Tipo</label>
            <select name="tipo" class="form-control">
                <option value="">Todos</option>
                <option value="Almacen_Almacen">Almacén a Almacén</option>
                <option value="Almacen_Servicio">Almacén a Servicio</option>
            </select>
        </div>
        <div class="form-grupo" style="display: flex; align-items: flex-end;">
            <button type="submit" class="btn btn-secundario"><i data-lucide="search"></i> Filtrar</button>
        </div>
    </form>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Servicio</th>
                    <th>Fecha</th>
                    <th>Estatus</th>
                    <?php if (($_SESSION['usuario_rol'] ?? '') === 'Administrador'): ?>
                    <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transferencias)): ?>
                <tr><td colspan="7" class="estado-vacio">No hay transferencias registradas.</td></tr>
                <?php else: ?>
                <?php foreach ($transferencias as $t): ?>
                <tr>
                    <td><code><?= htmlspecialchars($t['codigo_transaccion']) ?></code></td>
                    <td>
                        <?php 
                        $tipo = $t['tipo'] ?? 'Almacen_Almacen';
                        $labelTipo = ($tipo === 'Almacen_Almacen') ? 'Almacén a Almacén' : 'Almacén a Servicio';
                        ?>
                        <span class="badge <?= ($tipo === 'Almacen_Almacen') ? 'badge-azul' : 'badge-ambar' ?>">
                            <?= htmlspecialchars($labelTipo) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($t['almacen_origen_nombre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($t['almacen_destino_nombre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($t['servicio_nombre'] ?? '-') ?></td>
                    <td><?= isset($t['fecha_registro']) ? date('d/m/Y H:i', strtotime($t['fecha_registro'])) : '-' ?></td>
                    <td>
                        <?php 
                        $estatus = $t['estatus'] ?? 'Pendiente';
                        $claseBadge = 'badge-gris';
                        switch($estatus) {
                            case 'Pendiente': $claseBadge = 'badge-ambar'; break;
                            case 'En_Transito': $claseBadge = 'badge-azul'; break;
                            case 'Completada': $claseBadge = 'badge-verde'; break;
                            case 'Anulada': $claseBadge = 'badge-rojo'; break;
                        }
                        ?>
                        <span class="badge <?= $claseBadge ?>"><?= htmlspecialchars(str_replace('_', ' ', $estatus)) ?></span>
                    </td>
                    <?php if (($_SESSION['usuario_rol'] ?? '') === 'Administrador'): ?>
                    <td>
                        <?php if ($t['estatus'] !== 'Anulada'): ?>
                        <button type="button" class="btn btn-peligro btn-sm" onclick="btnAnularTransferencia(<?= $t['id'] ?>, '<?= htmlspecialchars($t['codigo_transaccion'], ENT_QUOTES) ?>')">
                            <i data-lucide="x-circle"></i> Anular
                        </button>
                        <?php else: ?>
                        <span class="badge badge-gris">Anulado</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($_SESSION['usuario_rol'] ?? '') === 'Administrador'): ?>
<!-- Modal de Anulación de Transferencia -->
<div class="modal-overlay" id="modal-anular-trf">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">⚠️ Anular Transferencia</h3>
            <button type="button" class="modal-cerrar" onclick="this.closest('.modal-overlay').classList.remove('activo')"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" action="index.php?url=transferencias/anular">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="transferencia_id" id="anular-trf-id">
            <div class="alerta alerta-advertencia" style="margin-bottom:1.5rem;">
                <i data-lucide="alert-triangle"></i>
                <div>
                    <strong>Atención:</strong> Al anular la transferencia <strong id="anular-trf-label"></strong>, 
                    el stock será devuelto automáticamente al almacén/lote de origen.
                </div>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Motivo de Anulación *</label>
                <textarea name="motivo_anulacion" id="anular-trf-motivo" class="form-control" required placeholder="Describa el motivo de la anulación..." style="min-height:80px;resize:vertical;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundario" onclick="this.closest('.modal-overlay').classList.remove('activo')">Cancelar</button>
                <button type="submit" class="btn btn-peligro"><i data-lucide="x-circle"></i> Confirmar Anulación</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function btnAnularTransferencia(id, codigo) {
    const modal = document.getElementById('modal-anular-trf');
    if (modal) {
        document.getElementById('anular-trf-id').value = id;
        document.getElementById('anular-trf-label').textContent = codigo;
        document.getElementById('anular-trf-motivo').value = '';
        modal.classList.add('activo');
    }
}
</script>

<script>
function toggleCampos() {
    const tipo = document.getElementById('tipoTransferencia').value;
    const servicioGroup = document.getElementById('servicioDestinoGroup');
    servicioGroup.style.display = tipo === 'Almacen_Servicio' ? 'block' : 'none';
}

function agregarMedicamento() {
    const container = document.getElementById('medicamentosContainer');
    const row = document.createElement('div');
    row.className = 'medicamento-row form-grid';
    row.style.marginBottom = '1rem';
    row.innerHTML = `
        <select class="form-control medication-select" name="medicamento_id[]" onchange="cargarLotes(this)">
            <option value="">Seleccione medicamento...</option>
            <?php foreach ($medicamentos as $m): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['codigo'] . ' - ' . $m['nombre_generico'] . ' (' . $m['concentracion'] . ')') ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-control lote-select" name="lote_id[]">
            <option value="">Seleccione lote...</option>
        </select>
        <input type="number" class="form-control" name="cantidad[]" min="1" placeholder="Cantidad">
        <button type="button" class="btn btn-peligro btn-sm" onclick="eliminarFila(this)"><i data-lucide="trash-2"></i></button>
    `;
    container.appendChild(row);
    lucide.createIcons();
}

function eliminarFila(btn) {
    const container = document.getElementById('medicamentosContainer');
    if (container.children.length > 1) {
        btn.closest('.medicamento-row').remove();
    }
}

function cargarLotes(select) {
    const row = select.closest('.medicamento-row');
    const loteSelect = row.querySelector('.lote-select');
    const medId = select.value;

    if (!medId) {
        loteSelect.innerHTML = '<option value="">Seleccione lote...</option>';
        return;
    }

    fetch(`index.php?url=transferencias/ajaxLotes&medicamento_id=${medId}`)
        .then(r => r.json())
        .then(data => {
            loteSelect.innerHTML = '<option value="">Seleccione lote...</option>';
            if (data.lotes) {
                data.lotes.forEach(l => {
                    const opt = document.createElement('option');
                    opt.value = l.id;
                    opt.textContent = `Lote: ${l.numero_lote} - Ven: ${l.fecha_vencimiento} - Disp: ${l.cantidad_disponible}`;
                    loteSelect.appendChild(opt);
                });
            }
        });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>