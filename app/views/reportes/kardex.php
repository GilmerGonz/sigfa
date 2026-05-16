<?php
$tituloPagina = 'Kardex Completo';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Kardex por Medicamento</h2>
        <?php if (!empty($filtros['medicamento_id'])): ?>
        <div class="card-acciones">
            <a href="?url=reportes/kardex&medicamento_id=<?= $filtros['medicamento_id'] ?>&exportar=pdf" class="btn btn-secundario" target="_blank"><i data-lucide="file-down"></i> PDF</a>
            <a href="?url=reportes/kardex&medicamento_id=<?= $filtros['medicamento_id'] ?>&exportar=excel" class="btn btn-secundario" target="_blank"><i data-lucide="file-spreadsheet"></i> Excel</a>
        </div>
        <?php endif; ?>
    </div>
    <form method="GET" class="form-grid" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="url" value="reportes/kardex">
        <div class="form-grupo col-span-2"><label class="form-etiqueta">Seleccionar Medicamento</label>
            <select name="medicamento_id" class="form-control" onchange="this.form.submit()">
                <option value="">Seleccione...</option>
                <?php foreach ($medicamentos as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= (isset($filtros['medicamento_id']) && $filtros['medicamento_id'] == $m['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['codigo'] . ' - ' . $m['nombre_generico'] . ' (' . $m['concentracion'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if (!empty($datos)): ?>
    <div class="tabla-container">
        <table>
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Cantidad</th><th>Stock Ant.</th><th>Stock Post.</th><th>Lote</th><th>Motivo</th><th>Usuario</th></tr></thead>
            <tbody>
                <?php foreach ($datos as $d): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($d['fecha_movimiento'])) ?></td>
                    <td><span class="badge badge-<?= $d['tipo_movimiento'] === 'Entrada' ? 'verde' : ($d['tipo_movimiento'] === 'Salida' ? 'rojo' : 'ambar') ?>"><?= $d['tipo_movimiento'] ?></span></td>
                    <td><?= $d['cantidad'] > 0 ? '+' . $d['cantidad'] : $d['cantidad'] ?></td>
                    <td><?= $d['stock_anterior'] ?></td>
                    <td><?= $d['stock_posterior'] ?></td>
                    <td><?= htmlspecialchars($d['numero_lote'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['motivo'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['usuario_nombre'] ?? 'Sistema') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>