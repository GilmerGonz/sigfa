<?php
$tituloPagina = 'Reportes por Patología';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Reportes por Patología</h2>
        <div class="card-acciones">
            <a href="?url=reportes/patologia&patologia_id=<?= $filtros['patologia_id'] ?>&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&exportar=pdf" class="btn btn-secundario" target="_blank"><i data-lucide="file-down"></i> PDF</a>
            <a href="?url=reportes/patologia&patologia_id=<?= $filtros['patologia_id'] ?>&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&exportar=excel" class="btn btn-secundario" target="_blank"><i data-lucide="file-spreadsheet"></i> Excel</a>
        </div>
    </div>
    <form method="GET" class="form-grid" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="url" value="reportes/patologia">
        <div class="form-grupo"><label class="form-etiqueta">Patología</label>
            <select name="patologia_id" class="form-control">
                <option value="">Todas</option>
                <?php foreach ($patologias as $p): ?><option value="<?= $p['id'] ?>" <?= $filtros['patologia_id'] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nombre'] . ' (' . $p['clasificacion'] . ')') ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-grupo"><label class="form-etiqueta">Desde</label><input type="date" name="fecha_desde" class="form-control" value="<?= $filtros['fecha_desde'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Hasta</label><input type="date" name="fecha_hasta" class="form-control" value="<?= $filtros['fecha_hasta'] ?>"></div>
        <div class="form-grupo" style="display:flex;align-items:flex-end;"><button type="submit" class="btn btn-primario"><i data-lucide="search"></i> Generar</button></div>
    </form>

    <div class="tabla-container">
        <table>
            <thead><tr><th>Patología</th><th>Clasificación</th><th>Grupo Etario</th><th>Género</th><th>Despachos</th><th>Medicamentos</th></tr></thead>
            <tbody>
                <?php if (empty($datos)): ?>
                <tr><td colspan="6" class="estado-vacio">Sin datos para el período seleccionado.</td></tr>
                <?php else: foreach ($datos as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['patologia'] ?? 'Sin patología') ?></td>
                    <td><span class="badge badge-<?= $d['clasificacion'] === 'Alto_Costo' ? 'rojo' : 'azul' ?>"><?= $d['clasificacion'] === 'Alto_Costo' ? 'Alto Costo' : 'Común' ?></span></td>
                    <td><?= htmlspecialchars($d['grupo_etario'] ?? '-') ?></td>
                    <td><span class="badge badge-<?= $d['sexo'] === 'M' ? 'azul' : ($d['sexo'] === 'F' ? 'rosa' : 'gris') ?>"><?= $d['sexo'] === 'M' ? 'Masculino' : ($d['sexo'] === 'F' ? 'Femenino' : '-') ?></span></td>
                    <td><?= $d['total_despachos'] ?></td>
                    <td><strong><?= $d['total_medicamentos'] ?></strong></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>