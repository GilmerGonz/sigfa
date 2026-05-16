<?php
$tituloPagina = 'Alertas de Calidad de Datos';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../models/Reporte.php';
$reporteModel = new Reporte();
$alertas = $reporteModel->alertasGlobal();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Alertas de Calidad de Datos</h2>
        <div class="card-acciones">
            <a href="?url=reportes/alertas&exportar=pdf" class="btn btn-secundario" target="_blank"><i data-lucide="file-down"></i> PDF</a>
        </div>
    </div>
    
    <?php if (empty($alertas)): ?>
    <div class="alerta alerta-exito">
        <i data-lucide="check-circle"></i> No se detectaron anomalías en los datos del sistema.
    </div>
    <?php else: ?>
    <div class="alerta alerta-advertencia">
        <i data-lucide="alert-triangle"></i> Se detectaron <?= count($alertas) ?> anomalías que requieren atención.
    </div>
    
    <div class="tabla-container">
        <table>
            <thead><tr><th>#</th><th>Tipo de Alerta</th><th>Descripción</th></tr></thead>
            <tbody>
                <?php foreach ($alertas as $i => $alerta): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><span class="badge badge-rojo"><?= strpos($alerta, 'medicamento') !== false ? 'Medicamento' : (strpos($alerta, 'paciente') !== false ? 'Paciente' : (strpos($alerta, 'despacho') !== false ? 'Despacho' : 'General')) ?></span></td>
                    <td><?= htmlspecialchars($alerta) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>