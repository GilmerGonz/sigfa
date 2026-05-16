<?php
$tituloPagina = 'Inventario Valorizado';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Inventario Valorizado</h2>
        <div class="card-acciones">
            <a href="?url=reportes/inventario&exportar=pdf" class="btn btn-secundario" target="_blank"><i data-lucide="file-down"></i> PDF</a>
            <a href="?url=reportes/inventario&exportar=excel" class="btn btn-secundario" target="_blank"><i data-lucide="file-spreadsheet"></i> Excel</a>
        </div>
    </div>

    <div class="tabla-container">
        <table>
            <thead><tr><th>Código</th><th>Medicamento</th><th>Concentración</th><th>Tipo</th><th>Stock</th><th>Precio Unit.</th><th>Valor Total (Bs)</th></tr></thead>
            <tbody>
                <?php if (empty($datos)): ?>
                <tr><td colspan="7" class="estado-vacio">Sin inventario disponible.</td></tr>
                <?php else: 
                    $totalValor = 0; $totalStock = 0;
                    foreach ($datos as $d): $totalValor += $d['valor_total']; $totalStock += $d['stock_total']; ?>
                <tr>
                    <td><code><?= htmlspecialchars($d['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($d['nombre_generico']) ?></td>
                    <td><?= htmlspecialchars($d['concentracion']) ?></td>
                    <td><span class="badge badge-azul"><?= htmlspecialchars($d['tipo']) ?></span></td>
                    <td><strong><?= $d['stock_total'] ?></strong></td>
                    <td><?= number_format($d['precio_unitario'], 2, ',', '.') ?></td>
                    <td><strong><?= number_format($d['valor_total'], 2, ',', '.') ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background: var(--glass-bg-accent); font-weight: bold;">
                    <td colspan="4">TOTALES</td>
                    <td><?= $totalStock ?></td>
                    <td></td>
                    <td><?= number_format($totalValor, 2, ',', '.') ?> Bs</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>