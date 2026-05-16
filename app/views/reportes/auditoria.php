<?php
$tituloPagina = 'Auditoría de Movimientos';
$paginaActual = 'reportes';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Auditoría del Sistema</h2>
        <div class="card-acciones">
            <a href="?url=reportes/auditoria&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&exportar=pdf" class="btn btn-secundario" target="_blank"><i data-lucide="file-down"></i> PDF</a>
            <a href="?url=reportes/auditoria&fecha_desde=<?= $filtros['fecha_desde'] ?>&fecha_hasta=<?= $filtros['fecha_hasta'] ?>&exportar=excel" class="btn btn-secundario" target="_blank"><i data-lucide="file-spreadsheet"></i> Excel</a>
        </div>
    </div>
    <form method="GET" class="form-grid" style="margin-bottom: 1.5rem;">
        <input type="hidden" name="url" value="reportes/auditoria">
        <div class="form-grupo"><label class="form-etiqueta">Desde</label><input type="date" name="fecha_desde" class="form-control" value="<?= $filtros['fecha_desde'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Hasta</label><input type="date" name="fecha_hasta" class="form-control" value="<?= $filtros['fecha_hasta'] ?>"></div>
        <div class="form-grupo"><label class="form-etiqueta">Acción</label>
            <select name="accion" class="form-control">
                <option value="">Todas</option>
                <option value="Login">Login</option>
                <option value="Logout">Logout</option>
                <option value="Create">Crear</option>
                <option value="Update">Actualizar</option>
                <option value="Delete">Eliminar</option>
                <option value="Query">Consulta</option>
            </select>
        </div>
        <div class="form-grupo"><label class="form-etiqueta">Módulo</label>
            <select name="modulo" class="form-control">
                <option value="">Todos</option>
                <option value="despachos">Despachos</option>
                <option value="inventario">Inventario</option>
                <option value="usuarios">Usuarios</option>
                <option value="medicamentos">Medicamentos</option>
                <option value="asegurados">Asegurados</option>
            </select>
        </div>
        <div class="form-grupo" style="display:flex;align-items:flex-end;"><button type="submit" class="btn btn-primario"><i data-lucide="search"></i> Filtrar</button></div>
    </form>

    <div class="tabla-container">
        <table>
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Módulo</th><th>Detalle</th><th>IP</th></tr></thead>
            <tbody>
                <?php if (empty($datos)): ?>
                <tr><td colspan="6" class="estado-vacio">No hay registros de auditoría para el período.</td></tr>
                <?php else: foreach ($datos as $d): ?>
                <tr>
                    <td><?= date('d/m/Y H:i:s', strtotime($d['fecha_accion'])) ?></td>
                    <td><?= htmlspecialchars($d['usuario'] ?? 'Sistema') ?></td>
                    <td><span class="badge badge-<?= $d['accion'] === 'Login' ? 'verde' : ($d['accion'] === 'Logout' ? 'rojo' : 'azul') ?>"><?= $d['accion'] ?></span></td>
                    <td><?= htmlspecialchars($d['modulo']) ?></td>
                    <td><?= htmlspecialchars($d['detalle'] ?? '-') ?></td>
                    <td><code><?= htmlspecialchars($d['ip_address'] ?? '-') ?></code></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>