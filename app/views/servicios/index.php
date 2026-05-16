<?php
$paginaActual = 'servicios';
$tituloPagina = 'Servicios Médicos';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Catálogo de Servicios Médicos</h2>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alerta alerta-error">
        <i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($exito)): ?>
    <div class="alerta alerta-exito">
        <i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="index.php?url=servicios/crear" class="form-grid" style="margin-bottom: 2rem;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="form-grupo">
            <label class="form-etiqueta">Código *</label>
            <input type="text" name="codigo" class="form-control" placeholder="001" required>
        </div>

        <div class="form-grupo">
            <label class="form-etiqueta">Nombre *</label>
            <input type="text" name="nombre" class="form-control" placeholder="Nombre del servicio" required>
        </div>

        <div class="form-grupo col-span-2">
            <label class="form-etiqueta">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="2" placeholder="Descripción opcional..."></textarea>
        </div>

        <div class="form-grupo col-span-2">
            <button type="submit" class="btn btn-primario">
                <i data-lucide="plus"></i> Agregar Servicio
            </button>
        </div>
    </form>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($servicios)): ?>
                <tr><td colspan="4" class="estado-vacio">No hay servicios registrados.</td></tr>
                <?php else: ?>
                <?php foreach ($servicios as $s): ?>
                <tr>
                    <td><code><?= htmlspecialchars($s['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($s['nombre']) ?></td>
                    <td><?= htmlspecialchars($s['descripcion'] ?? '-') ?></td>
                    <td>
                        <a href="index.php?url=servicios/eliminar&id=<?= $s['id'] ?>" class="btn btn-peligro btn-sm" onclick="return confirm('¿Eliminar servicio?')">
                            <i data-lucide="trash-2"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>