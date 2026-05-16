<?php
/** SIGFA - Vista: Asegurados (Pacientes) — CRUD completo */
$tituloPagina = 'Asegurados';
$paginaActual = 'asegurados';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Alertas -->
<?php if (!empty($exito)): ?>
<div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card card-compact fade-in" style="margin-bottom:1.5rem; border-left: 4px solid var(--azul-acento);">
    <div class="card-header" style="cursor:pointer;" onclick="toggleRegistro()">
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="sidebar-logo-icono" style="width:32px; height:32px; border-radius:8px;"><i data-lucide="user-plus" style="width:16px; height:16px;"></i></div>
            <h2 class="card-titulo" style="font-size:1.1rem;">Nuevo Registro de Asegurado</h2>
        </div>
        <button type="button" class="btn-icono" id="btn-toggle-registro" style="border:none; background:transparent;"><i data-lucide="chevron-up"></i></button>
    </div>
    <div id="contenedor-registro">
    <form method="POST" action="index.php?url=asegurados/crear" class="form-compact">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div style="padding: 1.5rem;">
            <!-- Sección: Datos Personales -->
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--azul-acento); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="user" style="width: 14px; height: 14px;"></i> Información Personal
                </h3>
                <div class="form-grid-4">
                    <div class="form-grupo">
                        <label class="form-etiqueta">Cédula *</label>
                        <input type="text" name="cedula" class="form-control" placeholder="V-12345678" required maxlength="12">
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
                        <label class="form-etiqueta">F. Nacimiento *</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" required>
                    </div>
                </div>
            </div>

            <!-- Sección: Datos Médicos y Contacto -->
            <div class="form-grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--azul-acento); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="activity" style="width: 14px; height: 14px;"></i> Información Médica / Estatus
                    </h3>
                    <div class="form-grid-3">
                        <div class="form-grupo">
                            <label class="form-etiqueta">Historia Médica</label>
                            <input type="text" name="historia_medica" class="form-control" placeholder="No. Historia">
                        </div>
                        <div class="form-grupo">
                            <label class="form-etiqueta">Sexo *</label>
                            <select name="sexo" class="form-control" required>
                                <option value="">...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        <div class="form-grupo">
                            <label class="form-etiqueta">Grupo Sang.</label>
                            <select name="grupo_sanguineo" class="form-control">
                                <option value="">...</option>
                                <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                                <option>AB+</option><option>AB-</option><option>O+</option><option>O-</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-grupo">
                            <label class="form-etiqueta">Tipo de Asegurado</label>
                            <select name="tipo_asegurado" class="form-control">
                                <option value="Titular">Titular</option>
                                <option value="Beneficiario">Beneficiario</option>
                            </select>
                        </div>
                    <div class="form-grid-3">
                        <div class="form-grupo">
                            <label class="form-etiqueta">Estado</label>
                            <input type="text" name="estado" class="form-control" placeholder="Ej: Lara">
                        </div>
                        <div class="form-grupo">
                            <label class="form-etiqueta">Municipio</label>
                            <input type="text" name="municipio" class="form-control" placeholder="Ej: Iribarren">
                        </div>
                        <div class="form-grupo">
                            <label class="form-etiqueta">Parroquia</label>
                            <input type="text" name="parroquia" class="form-control" placeholder="Ej: Catedral">
                        </div>
                    </div>
                    <div class="form-grupo">
                            <label class="form-etiqueta">Dirección Detallada</label>
                            <textarea name="direccion" class="form-control" rows="2" placeholder="Calle, número de casa, punto de referencia..."></textarea>
                    </div>
                </div>
                <div>
                    <h3 style="font-size: 0.85rem; font-weight: 800; color: var(--azul-acento); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="phone" style="width: 14px; height: 14px;"></i> Contacto
                    </h3>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Teléfono Personal</label>
                        <input type="text" name="telefono" class="form-control" placeholder="0412-0000000">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Teléfono Familiar (Emergencia)</label>
                        <input type="text" name="telefono_familiar" class="form-control" placeholder="0424-0000000">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Correo</label>
                        <input type="email" name="correo" class="form-control" placeholder="usuario@mail.com">
                    </div>
                </div>
            </div>
        </div>
        <div style="display:flex; justify-content: flex-end; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--gris-perla);">
            <button type="reset" class="btn btn-secundario btn-sm" style="margin-right:8px;">Limpiar</button>
            <button type="submit" class="btn btn-primario btn-sm"><i data-lucide="save"></i> Guardar Asegurado</button>
        </div>
    </form>
    </div>
</div>

<script>
function toggleRegistro() {
    const cont = document.getElementById('contenedor-registro');
    const btn = document.getElementById('btn-toggle-registro');
    if (cont.style.display === 'none') {
        cont.style.display = 'block';
        btn.innerHTML = '<i data-lucide="chevron-up"></i>';
    } else {
        cont.style.display = 'none';
        btn.innerHTML = '<i data-lucide="chevron-down"></i>';
    }
    lucide.createIcons();
}

// Auto-completar cédula y expandir si viene de Despacho
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const cedula = urlParams.get('cedula');
    if (cedula) {
        const inputCedula = document.querySelector('input[name="cedula"]');
        if (inputCedula) {
            inputCedula.value = cedula;
            // Asegurar que el contenedor esté visible
            const cont = document.getElementById('contenedor-registro');
            cont.style.display = 'block';
            document.getElementById('btn-toggle-registro').innerHTML = '<i data-lucide="chevron-up"></i>';
            lucide.createIcons();
        }
    }
});
</script>

<div class="card fade-in">
    <div class="card-header">
        <h2 class="card-titulo">Listado de Asegurados</h2>
        <span class="badge badge-azul"><?= count($asegurados ?? []) ?> Registrados</span>
    </div>

    <?php if (empty($asegurados)): ?>
    <div class="estado-vacio">
        <i data-lucide="users"></i>
        <h3>Sin asegurados registrados</h3>
        <p>Agregue el primer paciente al sistema</p>
    </div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>F. Nacimiento</th>
                    <th>Sexo</th>
                    <th>Grupo Sang.</th>
                    <th>Historia M.</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asegurados as $a): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($a['cedula']) ?></strong></td>
                    <td><?= htmlspecialchars($a['nombre'] . ' ' . $a['apellido']) ?></td>
                    <td><?= date('d/m/Y', strtotime($a['fecha_nacimiento'])) ?></td>
                    <td><?= $a['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?></td>
                    <td><span class="badge badge-azul"><?= htmlspecialchars($a['grupo_sanguineo'] ?? 'N/D') ?></span></td>
                    <td><?= htmlspecialchars($a['historia_medica'] ?? '—') ?></td>
                    <td><span class="badge badge-verde"><?= htmlspecialchars($a['tipo_asegurado']) ?></span></td>
                    <td>
                        <button class="btn-icono" title="Ver" onclick="alert('Módulo en desarrollo')"><i data-lucide="eye"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
