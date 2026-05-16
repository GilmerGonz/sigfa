<?php
/** SIGFA - Vista: Reporte de Prescripción por Paciente */
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card fade-in" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h2 class="card-titulo">Relación de Prescripción por Paciente</h2>
        <?php if (!empty($datos)): ?>
        <div class="card-acciones">
            <a href="?url=reportes/paciente&cedula=<?= urlencode($_GET['cedula'] ?? '') ?>&fecha_desde=<?= $fecha_desde ?>&fecha_hasta=<?= $fecha_hasta ?>&exportar=pdf" class="btn btn-secundario" target="_blank"><i data-lucide="file-down"></i> PDF</a>
            <a href="?url=reportes/paciente&cedula=<?= urlencode($_GET['cedula'] ?? '') ?>&fecha_desde=<?= $fecha_desde ?>&fecha_hasta=<?= $fecha_hasta ?>&exportar=excel" class="btn btn-secundario" target="_blank"><i data-lucide="file-spreadsheet"></i> Excel</a>
        </div>
        <?php endif; ?>
    </div>
    
    <form method="GET" action="index.php" class="form-grid">
        <input type="hidden" name="url" value="reportes/paciente">
        <div class="form-grupo">
            <label class="form-etiqueta">Cédula del Paciente *</label>
            <input type="text" name="cedula" class="form-control" value="<?= htmlspecialchars($_GET['cedula'] ?? '') ?>" required>
        </div>
        <div class="form-grupo">
            <label class="form-etiqueta">Desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($fecha_desde) ?>">
        </div>
        <div class="form-grupo">
            <label class="form-etiqueta">Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($fecha_hasta) ?>">
        </div>
        <div class="form-grupo" style="display:flex; align-items:end;">
            <button type="submit" class="btn btn-primario"><i data-lucide="search"></i> Buscar</button>
        </div>
    </form>
</div>

<?php if (!empty($_GET['cedula'])): ?>
    <?php if (!empty($anomalias)): ?>
        <div class="card fade-in" style="margin-bottom:1.5rem; background:#fff5f5; border-left:4px solid var(--error);">
            <div class="card-header"><h3 class="card-titulo" style="color:var(--error);"><i data-lucide="alert-triangle"></i> Detección Proactiva de Anomalías</h3></div>
            <ul style="padding-left:20px; color:#c53030; font-weight:600;">
                <?php foreach($anomalias as $an): ?>
                    <li><?= htmlspecialchars($an) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="alerta alerta-exito"><i data-lucide="check-circle"></i> No se detectaron anomalías en el patrón de prescripción de este paciente.</div>
    <?php endif; ?>

    <div class="card fade-in">
        <div class="card-header" style="display:flex; flex-direction:column; align-items:start; gap:5px;">
            <h3 class="card-titulo">Historial de Despachos</h3>
            <?php if ($paciente): ?>
                <div style="font-size:1.2rem; font-weight:700; color:var(--primario); margin-top:5px;">
                    <i data-lucide="user"></i> Paciente: <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']) ?> 
                    <span style="font-weight:400; color:#666; font-size:1rem;">(<?= htmlspecialchars($cedula) ?>)</span>
                </div>
            <?php endif; ?>
        </div>
        <?php if (empty($datos)): ?>
            <div class="estado-vacio">
                <i data-lucide="inbox"></i>
                <h3>No hay registros</h3>
                <p>No se encontraron despachos para este paciente en el período seleccionado.</p>
            </div>
        <?php else: ?>
            <div class="tabla-container">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Ticket</th>
                            <th>Medicamento</th>
                            <th>Presentación</th>
                            <th>Cantidad</th>
                            <th>Médico Tratante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($datos as $d): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($d['fecha'])) ?></td>
                            <td><strong><?= htmlspecialchars($d['ticket']) ?></strong></td>
                            <td><?= htmlspecialchars($d['nombre_generico']) ?></td>
                            <td><?= htmlspecialchars($d['concentracion']) ?></td>
                            <td><strong><?= $d['cantidad'] ?></strong></td>
                            <td><?= htmlspecialchars($d['medico_nombre'] . ' ' . $d['medico_apellido']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
