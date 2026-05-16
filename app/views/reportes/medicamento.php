<?php
$tituloPagina = 'Reportes por Medicamento';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Reporte por Medicamento</h2>
        <div class="card-acciones">
            <a href="?url=reportes/medicamento&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&medicamento_id=<?= $filtros['medicamento_id'] ?>&exportar=pdf" class="btn btn-secundario" target="_blank"><i data-lucide="file-down"></i> PDF</a>
            <a href="?url=reportes/medicamento&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&medicamento_id=<?= $filtros['medicamento_id'] ?>&exportar=excel" class="btn btn-secundario" target="_blank"><i data-lucide="file-spreadsheet"></i> Excel</a>
        </div>
    </div>
    <form method="GET" class="form-grid" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="url" value="reportes/medicamento">
        <div class="form-grupo"><label class="form-etiqueta">Desde</label><input type="date" name="fecha_desde" class="form-control" value="<?= $filtros['fecha_desde'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Hasta</label><input type="date" name="fecha_hasta" class="form-control" value="<?= $filtros['fecha_hasta'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Medicamento</label>
            <select name="medicamento_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($medicamentos as $m): ?><option value="<?= $m['id'] ?>" <?= $filtros['medicamento_id'] == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['codigo'] . ' - ' . $m['nombre_generico']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-grupo" style="display:flex;align-items:flex-end;"><button type="submit" class="btn btn-primario"><i data-lucide="search"></i> Generar</button></div>
    </form>

    <div class="tabla-container">
        <table>
            <thead><tr><th>Código</th><th>Medicamento</th><th>Concentración</th><th>Servicio</th><th>Cantidad</th></tr></thead>
            <tbody>
                <?php if (empty($datos)): ?>
                <tr><td colspan="5" class="estado-vacio">Sin datos para el período seleccionado.</td></tr>
                <?php else: foreach ($datos as $d): ?>
                <tr>
                    <td><code><?= htmlspecialchars($d['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($d['nombre_generico']) ?></td>
                    <td><?= htmlspecialchars($d['concentracion']) ?></td>
                    <td><?= htmlspecialchars($d['servicio'] ?? '-') ?></td>
                    <td><strong><?= $d['cantidad_despachada'] ?></strong></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>