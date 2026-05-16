<?php
$tituloPagina = 'Consumo Masivo';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Reporte de Consumo Masivo</h2>
<div class="card-acciones">
             <a href="?url=reportes/consumo_masivo&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&grupo_id=<?= $filtros['grupo_id'] ?>&exportar=pdf" class="btn btn-secundario"><i data-lucide="file-down"></i> PDF</a>
             <a href="?url=reportes/consumo_masivo&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&grupo_id=<?= $filtros['grupo_id'] ?>&exportar=excel" class="btn btn-secundario"><i data-lucide="file-spreadsheet"></i> Excel</a>
         </div>
    </div>
    <form method="GET" class="form-grid">
        <input type="hidden" name="url" value="reportes/consumo_masivo">
        <div class="form-grupo"><label class="form-etiqueta">Desde</label><input type="date" name="fecha_desde" class="form-control" value="<?= $filtros['fecha_desde'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Hasta</label><input type="date" name="fecha_hasta" class="form-control" value="<?= $filtros['fecha_hasta'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Grupo</label>
            <select name="grupo_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($grupos as $g): ?><option value="<?= $g['id'] ?>" <?= $filtros['grupo_id'] == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['nombre']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-grupo" style="display:flex;align-items:flex-end;"><button type="submit" class="btn btn-primario"><i data-lucide="search"></i> Generar</button></div>
    </form>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Medicamento</th>
                    <th>Presentación</th>
                    <th>Cant. Despachada</th>
                    <th>Cant. Recetas</th>
                    <th>Stock Actual</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($datos)): ?>
                <tr><td colspan="6" class="estado-vacio">Sin datos para el período seleccionado.</td></tr>
                <?php else: foreach ($datos as $d): ?>
                <tr>
                    <td><code><?= htmlspecialchars($d['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($d['medicamento']) ?></td>
                    <td><?= htmlspecialchars($d['concentracion']) ?></td>
                    <td><strong><?= number_format($d['cantidad_despachada'], 0, ',', '.') ?></strong></td>
                    <td><?= number_format($d['total_recetas'], 0, ',', '.') ?></td>
                    <td>
                        <span class="badge <?= $d['stock_actual'] > 0 ? 'badge-verde' : 'badge-rojo' ?>">
                            <?= number_format($d['stock_actual'], 0, ',', '.') ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>