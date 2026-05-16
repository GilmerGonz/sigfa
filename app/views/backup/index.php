<?php
$tituloPagina = 'Backup y Mantenimiento';
$paginaActual = 'backup';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Gestión de Backups</h2>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 2rem;">
        <form method="POST" action="index.php?url=backup/crear">
            <button type="submit" class="btn btn-primario">
                <i data-lucide="database-backup"></i> Generar Backup Ahora
            </button>
        </form>
        <p style="margin-top: 1rem; color: var(--gris-suave); font-size: 0.85rem;">
            <i data-lucide="info"></i> Se recomienda generar backups de forma semanal. El sistema también puede configurarse para backup automático.
        </p>
    </div>

    <div class="card-header">
        <h3 class="card-titulo">Backups Existentes</h3>
    </div>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Tamaño</th>
                    <th>Fecha de Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($backups)): ?>
                <tr>
                    <td colspan="4" class="estado-vacio">
                        <i data-lucide="database"></i>
                        <h3>No hay backups disponibles</h3>
                        <p>Genere un backup para proteger su información.</p>
                    </td>
                </tr>
                <?php else: foreach ($backups as $b): ?>
                <tr>
                    <td><i data-lucide="file-archive"></i> <?= htmlspecialchars($b['nombre']) ?></td>
                    <td><?= number_format($b['tamano']/1024, 2) ?> KB</td>
                    <td><?= date('d/m/Y H:i', $b['fecha']) ?></td>
                    <td>
                        <a href="index.php?url=backup/descargar&nombre=<?= urlencode($b['nombre']) ?>" class="btn btn-secundario btn-sm" title="Descargar">
                            <i data-lucide="download"></i>
                        </a>
                        <a href="index.php?url=backup/eliminar&nombre=<?= urlencode($b['nombre']) ?>" class="btn btn-peligro btn-sm" title="Eliminar" onclick="return confirm('¿Eliminar este backup?')">
                            <i data-lucide="trash-2"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h2 class="card-titulo">Programación de Backups</h2>
    </div>
    <div class="alerta alerta-info">
        <i data-lucide="calendar"></i>
        <div>
            <strong>Backup Automático Semanal</strong>
            <p style="margin-top: 0.5rem; font-size: 0.85rem;">El sistema puede configurarse para generar backups automáticamente cada domingo a las 00:00 horas. Para activar esta función, contacte al administrador del sistema.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>