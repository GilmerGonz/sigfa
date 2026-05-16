<?php
$paginaActual = 'patologias';
$tituloPagina = 'Patologías';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-titulo">Registro de Patologías</h2>
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

    <form method="POST" action="index.php?url=patologias/crear" class="form-grid" style="margin-bottom: 2rem;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        
        <div class="form-grupo">
            <label class="form-etiqueta">Nombre de Patología *</label>
            <input type="text" name="nombre" class="form-control" placeholder="Nombre de la patología" required>
        </div>

        <div class="form-grupo">
            <label class="form-etiqueta">Clasificación *</label>
            <select name="clasificacion" class="form-control" required onchange="toggleGrupoEtario()">
                <option value="">Seleccione...</option>
                <option value="Alto_Costo">Alto Costo</option>
                <option value="Comun">Común</option>
            </select>
        </div>

        <div class="form-grupo">
            <label class="form-etiqueta">Grupo Etario</label>
            <select name="grupo_etario" class="form-control" id="grupoEtario">
                <option value="">Seleccione...</option>
                <option value="Niños">Niños</option>
                <option value="Adultos">Adultos</option>
                <option value="Ambos">Ambos</option>
            </select>
        </div>

        <div class="form-grupo">
            <label class="form-etiqueta">Descripción</label>
            <input type="text" name="descripcion" class="form-control" placeholder="Descripción opcional">
        </div>

        <div class="form-grupo col-span-2">
            <button type="submit" class="btn btn-primario">
                <i data-lucide="plus"></i> Agregar Patología
            </button>
        </div>
    </form>

    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
        <a href="index.php?url=patologias" class="btn btn-secundario btn-sm">Todas</a>
        <a href="index.php?url=patologias&clasificacion=Alto_Costo" class="btn btn-primario btn-sm">Alto Costo</a>
        <a href="index.php?url=patologias&clasificacion=Comun" class="btn btn-secundario btn-sm">Comunes</a>
    </div>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Clasificación</th>
                    <th>Grupo Etario</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patologias)): ?>
                <tr><td colspan="5" class="estado-vacio">No hay patologías registradas.</td></tr>
                <?php else: ?>
                <?php foreach ($patologias as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><span class="badge <?= $p['clasificacion'] === 'Alto_Costo' ? 'badge-rojo' : 'badge-azul' ?>"><?= htmlspecialchars($p['clasificacion']) ?></span></td>
                    <td><?= htmlspecialchars($p['grupo_etario'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['descripcion'] ?? '-') ?></td>
                    <td>
                        <a href="index.php?url=patologias/eliminar&id=<?= $p['id'] ?>" class="btn btn-peligro btn-sm" onclick="return confirm('¿Eliminar patología?')">
                            <i data-lucide="trash-2"></i>
                        </a>
                    Modelo: Inventario.php</td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleGrupoEtario() {
    const clasif = document.querySelector('select[name="clasificacion"]').value;
    document.getElementById('grupoEtario').disabled = clasif !== 'Alto_Costo';
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>