<?php
/** SIGFA - Vista: Médicos — CRUD */
$tituloPagina = 'Médicos';
$paginaActual = 'medicos';
require_once __DIR__ . '/../layouts/header.php';
?>

<?php if (!empty($exito)): ?><div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card card-compact fade-in" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h2 class="card-titulo">Registrar Nuevo Médico</h2>
    </div>
    <form method="POST" action="index.php?url=medicos/crear" class="form-compact">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="form-grid-4">
            <div class="form-grupo">
                <label class="form-etiqueta">Cédula *</label>
                <input type="text" name="cedula" class="form-control" required placeholder="V-12345678">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">MPPS *</label>
                <input type="text" name="codigo_mpps" class="form-control" required placeholder="12345">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Nombre *</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Apellido *</label>
                <input type="text" name="apellido" class="form-control" required>
            </div>

            <div class="form-grupo">
                <label class="form-etiqueta">Especialidad</label>
                <input type="text" name="especialidad" class="form-control" placeholder="Ej: Medicina General">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Teléfono</label>
                <input type="text" name="telefono" class="form-control">
            </div>
            <div class="form-grupo col-span-2">
                <label class="form-etiqueta">Correo Electrónico</label>
                <input type="email" name="correo" class="form-control">
            </div>
        </div>
        <div style="display:flex; justify-content: flex-end; margin-top:0.5rem;">
            <button type="submit" class="btn btn-primario btn-sm"><i data-lucide="save"></i> Registrar Médico</button>
        </div>
    </form>
</div>

<div class="card fade-in">
    <div class="card-header">
        <h2 class="card-titulo">Médicos Registrados</h2>
        <span class="badge badge-azul"><?= count($medicos ?? []) ?> Profesionales</span>
    </div>
    <?php if (empty($medicos)): ?>
    <div class="estado-vacio"><i data-lucide="stethoscope"></i><h3>Sin médicos registrados</h3><p>Registre médicos con su código MPPS</p></div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead><tr><th>Cédula</th><th>Nombre</th><th>Código MPPS</th><th>Especialidad</th><th>Teléfono</th><th>Correo</th></tr></thead>
            <tbody>
                <?php foreach ($medicos as $m): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($m['cedula']) ?></strong></td>
                    <td><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></td>
                    <td><span class="badge badge-azul"><?= htmlspecialchars($m['codigo_mpps']) ?></span></td>
                    <td><?= htmlspecialchars($m['especialidad'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($m['telefono'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($m['correo'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
