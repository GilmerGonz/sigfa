<?php
/** SIGFA - Vista: Historial de Anulaciones */
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card fade-in">
    <div class="card-header">
        <h2 class="card-titulo"><i data-lucide="x-circle"></i> Historial de Anulaciones</h2>
        <span class="badge badge-rojo">Solo Administrador</span>
    </div>

    <!-- Filtros -->
    <form method="GET" action="index.php" class="form-grid" style="margin-bottom: 2rem; background: var(--gris-claro); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--gris-perla);">
        <input type="hidden" name="url" value="despachos/anular">
        
        <div class="form-grupo" style="margin-bottom:0;">
            <label class="form-etiqueta">Desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
        </div>
        <div class="form-grupo" style="margin-bottom:0;">
            <label class="form-etiqueta">Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
        </div>
        <div class="form-grupo" style="display:flex; align-items:flex-end; margin-bottom:0;">
            <button type="submit" class="btn btn-secundario"><i data-lucide="filter"></i> Filtrar Historial</button>
        </div>
    </form>

    <?php if (empty($anulaciones)): ?>
    <div class="estado-vacio">
        <i data-lucide="clipboard-x"></i>
        <h3>No hay registros de nulaciones</h3>
        <p>Aquí aparecerán todos los despachos, transferencias y entradas anuladas por el Administrador.</p>
    </div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Referencia</th>
                    <th>Fecha Original</th>
                    <th>Fecha Anulación</th>
                    <th>Usuario que Anuló</th>
                    <th>Motivo de Anulación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($anulaciones as $a): ?>
                <tr>
                    <td>
                        <span class="badge <?= $a['tipo'] === 'Despacho' ? 'badge-azul' : ($a['tipo'] === 'Transferencia' ? 'badge-ambar' : 'badge-rojo') ?>">
                            <?= htmlspecialchars($a['tipo']) ?>
                        </span>
                    </td>
                    <td><strong><?= htmlspecialchars($a['referencia']) ?></strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($a['fecha_original'])) ?></td>
                    <td><span class="badge badge-rojo"><?= date('d/m/Y H:i', strtotime($a['fecha_anulacion'])) ?></span></td>
                    <td><?= htmlspecialchars($a['usuario_anulo'] ?? 'Sistema') ?></td>
                    <td style="max-width: 300px; font-size: 0.8rem; line-height: 1.4;">
                        <em>"<?= htmlspecialchars($a['motivo_anulacion']) ?>"</em>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
