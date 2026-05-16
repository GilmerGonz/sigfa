<?php
$tituloPagina = 'Reportes por Servicio';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Reporte por Servicio</h2>
        <div class="card-acciones">
            <a href="?url=reportes/servicio&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&servicio_id=<?= $filtros['servicio_id'] ?>&exportar=pdf" class="btn btn-secundario" target="_blank"><i data-lucide="file-down"></i> PDF</a>
            <a href="?url=reportes/servicio&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&servicio_id=<?= $filtros['servicio_id'] ?>&exportar=excel" class="btn btn-secundario" target="_blank"><i data-lucide="file-spreadsheet"></i> Excel</a>
        </div>
    </div>
    <form method="GET" class="form-grid" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="url" value="reportes/servicio">
        <div class="form-grupo"><label class="form-etiqueta">Desde</label><input type="date" name="fecha_desde" class="form-control" value="<?= $filtros['fecha_desde'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Hasta</label><input type="date" name="fecha_hasta" class="form-control" value="<?= $filtros['fecha_hasta'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Servicio</label>
            <select name="servicio_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($servicios as $s): ?><option value="<?= $s['id'] ?>" <?= $filtros['servicio_id'] == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['nombre']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-grupo" style="display:flex;align-items:flex-end;"><button type="submit" class="btn btn-primario"><i data-lucide="search"></i> Generar</button></div>
    </form>

    <div class="tabla-container">
        <table>
            <thead><tr><th>Servicio</th><th>Código</th><th>Despachos</th><th>Medicamentos</th><th>Género</th><th>Monto (Bs)</th></tr></thead>
            <tbody>
                <?php if (empty($datos)): ?>
                <tr><td colspan="6" class="estado-vacio">Sin datos para el período seleccionado.</td></tr>
                <?php else: foreach ($datos as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['servicio'] ?? 'Sin servicio') ?></td>
                    <td><?= htmlspecialchars($d['codigo'] ?? '-') ?></td>
                    <td><?= $d['total_despachos'] ?></td>
                    <td><?= $d['total_medicamentos'] ?></td>
                    <td><span class="badge badge-<?= $d['sexo'] === 'M' ? 'azul' : ($d['sexo'] === 'F' ? 'rosa' : 'gris') ?>"><?= $d['sexo'] === 'M' ? 'Masculino' : ($d['sexo'] === 'F' ? 'Femenino' : '-') ?></span></td>
                    <td><?= number_format($d['monto_total'] ?? 0, 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>