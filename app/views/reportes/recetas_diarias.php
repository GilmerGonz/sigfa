<?php
$tituloPagina = 'Recetas Diarias';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Recetas Diarias</h2>
<div class="card-acciones">
             <a href="?url=reportes/recetas&fecha=<?= $fecha ?>&exportar=pdf" class="btn btn-secundario"><i data-lucide="file-down"></i> PDF</a>
             <a href="?url=reportes/recetas&fecha=<?= $fecha ?>&exportar=excel" class="btn btn-secundario"><i data-lucide="file-spreadsheet"></i> Excel</a>
         </div>
    </div>
    <form method="GET" class="form-grid">
        <input type="hidden" name="url" value="reportes/recetas">
        <div class="form-grupo"><label class="form-etiqueta">Fecha</label><input type="date" name="fecha" class="form-control" value="<?= $fecha ?>"></div>
        <div class="form-grupo" style="display:flex;align-items:flex-end;"><button type="submit" class="btn btn-primario"><i data-lucide="search"></i> Buscar</button></div>
    </form>

    <div class="tabla-container">
        <table>
            <thead><tr><th>Ticket</th><th>Paciente</th><th>Cédula</th><th>Medicamento</th><th>Cant.</th><th>Servicio</th><th>Estatus</th></tr></thead>
            <tbody>
                <?php if (empty($datos)): ?>
                <tr><td colspan="7" class="estado-vacio">Sin recetas para la fecha seleccionada.</td></tr>
                <?php else: foreach ($datos as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['ticket']) ?></td>
                    <td><?= htmlspecialchars($d['paciente']) ?></td>
                    <td><?= htmlspecialchars($d['cedula']) ?></td>
                    <td><?= htmlspecialchars($d['medicamento']) ?></td>
                    <td><?= $d['cantidad'] ?></td>
                    <td><?= htmlspecialchars($d['servicio'] ?? 'N/A') ?></td>
                    <td><span class="badge badge-<?= $d['estatus'] === 'Despachado' ? 'verde' : 'amarillo' ?>"><?= $d['estatus'] ?></span></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>