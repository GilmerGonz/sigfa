<?php
/** SIGFA - Vista: Alertas de Vencimiento y Stock Bajo */
$tituloPagina = 'Alertas';
$paginaActual = 'alertas';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Alertas de Vencimiento -->
<div class="card fade-in" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h2 class="card-titulo">🔴 Lotes Próximos a Vencer (&lt; 30 días)</h2>
        <span class="badge badge-rojo"><?= count($lotesPorVencer ?? []) ?> alertas</span>
    </div>
    <?php if (empty($lotesPorVencer)): ?>
    <div class="estado-vacio">
        <i data-lucide="check-circle" style="color:var(--exito);"></i>
        <h3>Sin alertas de vencimiento</h3>
        <p>Todos los lotes tienen fecha de vencimiento superior a 30 días</p>
    </div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead><tr><th>Medicamento</th><th>Lote</th><th>Vencimiento</th><th>Días Restantes</th><th>Stock Disp.</th></tr></thead>
            <tbody>
                <?php foreach ($lotesPorVencer as $l): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($l['nombre_generico'] . ' ' . $l['concentracion']) ?></strong></td>
                    <td><?= htmlspecialchars($l['numero_lote']) ?></td>
                    <td><?= date('d/m/Y', strtotime($l['fecha_vencimiento'])) ?></td>
                    <td>
                        <?php $dias = (int)$l['dias_para_vencer']; ?>
                        <span class="badge <?= $dias <= 7 ? 'badge-rojo' : 'badge-ambar' ?>">
                            <?= $dias ?> día<?= $dias !== 1 ? 's' : '' ?>
                        </span>
                    </td>
                    <td><?= $l['cantidad_disponible'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Stock Bajo -->
<div class="card fade-in">
    <div class="card-header">
        <h2 class="card-titulo">🟡 Medicamentos con Stock Bajo</h2>
        <span class="badge badge-ambar"><?= count($stockBajo ?? []) ?></span>
    </div>
    <?php if (empty($stockBajo)): ?>
    <div class="estado-vacio">
        <i data-lucide="check-circle" style="color:var(--exito);"></i>
        <h3>Stock normal</h3>
        <p>Todos los medicamentos están por encima del stock mínimo</p>
    </div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead><tr><th>Código</th><th>Medicamento</th><th>Stock Actual</th><th>Stock Mínimo</th><th>Estado</th></tr></thead>
            <tbody>
                <?php foreach ($stockBajo as $m): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($m['codigo']) ?></strong></td>
                    <td><?= htmlspecialchars($m['nombre_generico'] . ' ' . $m['concentracion']) ?></td>
                    <td style="font-weight:700; color:var(--error);"><?= $m['stock_total'] ?></td>
                    <td><?= $m['stock_minimo'] ?></td>
                    <td><span class="badge <?= $m['stock_total'] <= 0 ? 'badge-rojo' : 'badge-ambar' ?>"><?= $m['stock_total'] <= 0 ? 'Agotado' : 'Bajo' ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
