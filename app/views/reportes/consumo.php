<?php
$tituloPagina = 'Consumo en Bolivares';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Consumo en Bolivares</h2>
        <div class="card-acciones">
            <a href="?url=reportes/consumo&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&exportar=pdf" class="btn btn-secundario" target="_blank"><i data-lucide="file-down"></i> PDF</a>
            <a href="?url=reportes/consumo&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&exportar=excel" class="btn btn-secundario" target="_blank"><i data-lucide="file-spreadsheet"></i> Excel</a>
        </div>
    </div>
    <form method="GET" class="form-grid" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="url" value="reportes/consumo">
        <div class="form-grupo"><label class="form-etiqueta">Desde</label><input type="date" name="fecha_desde" class="form-control" value="<?= $filtros['fecha_desde'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Hasta</label><input type="date" name="fecha_hasta" class="form-control" value="<?= $filtros['fecha_hasta'] ?>"></div>
        <div class="form-grupo" style="display:flex;align-items:flex-end;"><button type="submit" class="btn btn-primario"><i data-lucide="search"></i> Generar</button></div>
    </form>

    <div class="tabla-container">
        <table>
            <thead><tr><th>Fecha</th><th>Servicio</th><th>Despachos</th><th>Medicamentos</th><th>Monto (Bs)</th></tr></thead>
            <tbody>
                <?php if (empty($datos)): ?>
                <tr><td colspan="5" class="estado-vacio">Sin datos para el período seleccionado.</td></tr>
                <?php else: 
                    $totalMonto = 0; $totalMedicamentos = 0;
                    foreach ($datos as $d): $totalMonto += $d['monto']; $totalMedicamentos += $d['total_medicamentos']; ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                    <td><?= htmlspecialchars($d['servicio'] ?? 'Sin servicio') ?></td>
                    <td><?= $d['despachos'] ?></td>
                    <td><?= $d['total_medicamentos'] ?></td>
                    <td><strong><?= number_format($d['monto'], 2, ',', '.') ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background: var(--glass-bg-accent); font-weight: bold;">
                    <td colspan="3">TOTALES</td>
                    <td><?= $totalMedicamentos ?></td>
                    <td><?= number_format($totalMonto, 2, ',', '.') ?> Bs</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>