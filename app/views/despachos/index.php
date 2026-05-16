<?php
/** SIGFA - Vista: Despachos — Nuevo + Listado */
$tituloPagina = 'Despacho de Medicamentos';
$paginaActual = 'despachos';
require_once __DIR__ . '/../layouts/header.php';

// Cargar servicios y patologías
require_once __DIR__ . '/../../models/ServicioMedico.php';
require_once __DIR__ . '/../../models/Patologia.php';
$servicioModel = new ServicioMedico();
$patologiaModel = new Patologia();
$servicios = $servicioModel->listarActivos();
$patologias = $patologiaModel->listarActivos();
?>

<?php if (!empty($exito)): ?>
<div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['alerta_identidad'])): ?>
<div class="alerta alerta-advertencia"><i data-lucide="alert-triangle"></i> <?= htmlspecialchars($_SESSION['alerta_identidad']['motivo'] ?? 'Actualizar datos del paciente') ?></div>
<?php unset($_SESSION['alerta_identidad']); endif; ?>

<div class="card fade-in" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h2 class="card-titulo">Nuevo Despacho</h2>
    </div>
    <form method="POST" action="index.php?url=despachos/crear" id="form-despacho">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div class="form-grid-3">
            <!-- 4.1.1 Buscar paciente -->
            <div class="form-grupo">
                <label class="form-etiqueta">Buscar Paciente (Cédula) *</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="buscar-cedula" class="form-control" placeholder="V-12345678">
                    <button type="button" class="btn btn-secundario" onclick="buscarPaciente()" title="Buscar Paciente"><i data-lucide="search"></i></button>
                    <button type="button" class="btn btn-primario" onclick="window.location.href='index.php?url=asegurados'" title="Registrar Nuevo Paciente"><i data-lucide="user-plus"></i></button>
                </div>
                <input type="hidden" name="asegurado_id" id="asegurado_id">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Paciente</label>
                <input type="text" id="paciente-nombre" class="form-control" readonly placeholder="El nombre aparecerá aquí...">
                <input type="hidden" id="paciente-ciclo" value="21">
            </div>
            <!-- 4.1.3 Servicio Médico -->
            <div class="form-grupo">
                <label class="form-etiqueta">Servicio Médico</label>
                <select name="servicio_id" class="form-control">
                    <option value="">Seleccione...</option>
                    <?php foreach ($servicios ?? [] as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- 4.1.5 Patología -->
            <div class="form-grupo">
                <label class="form-etiqueta">Patología</label>
                <select name="patologia_id" class="form-control">
                    <option value="">Seleccione...</option>
                    <?php foreach ($patologias ?? [] as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- 4.1.6 Médico -->
            <div class="form-grupo">
                <label class="form-etiqueta">Médico *</label>
                <select name="medico_id" class="form-control" required data-ajax-url="index.php?url=ajax/medicos" placeholder="Buscar médico por nombre, apellido o especialidad...">
                    <option value="">Seleccione médico...</option>
                </select>
            </div>
            <!-- 4.1.9 Diagnóstico -->
            <div class="form-grupo col-span-3">
                <label class="form-etiqueta">Diagnóstico</label>
                <input type="text" name="diagnostico" class="form-control" placeholder="Diagnóstico del paciente">
            </div>
        </div>

        <!-- 4.1.8 Múltiples medicamentos -->
        <div class="card-header" style="margin-top:1rem;">
            <h3 class="card-titulo" style="font-size:1rem;">Medicamentos a despachar</h3>
            <button type="button" class="btn btn-secundario btn-sm" onclick="agregarLineaMedicamento()"><i data-lucide="plus"></i> Agregar línea</button>
        </div>

        <div id="lineas-medicamento">
            <!-- Template de línea de despacho corregido -->
            <div class="form-grid despacho-linea" style="grid-template-columns: 3fr 1fr 1fr 2fr 40px; align-items:end; margin-bottom:0.75rem;" data-linea>
                <div class="form-grupo" style="margin-bottom:0;">
                    <label class="form-etiqueta">Medicamento</label>
                    <select name="medicamento_id[]" class="form-control select-medicamento" required onchange="verificarCicloItem(this)" data-ajax-url="index.php?url=ajax/medicamentos" placeholder="Buscar medicamento...">
                        <option value="">Seleccione...</option>
                    </select>
                </div>
                <div class="form-grupo" style="margin-bottom:0;">
                    <label class="form-etiqueta">C. Recetada</label>
                    <input type="number" name="cantidad_recetada[]" class="form-control" min="1" value="1" required>
                </div>
                <div class="form-grupo" style="margin-bottom:0;">
                    <label class="form-etiqueta">C. Entregada</label>
                    <input type="number" name="cantidad[]" class="form-control" min="1" value="1" required>
                </div>
                <!-- Selector Dual de Ciclo -->
                <div class="form-grupo selector-dual-cont" style="margin-bottom:0; display:none;">
                    <label class="form-etiqueta">Ciclo / Próxima Fecha</label>
                    <div style="display:flex; gap:4px;">
                        <select name="ciclo_asignado[]" class="form-control" style="padding:8px; font-size:0.8rem;" onchange="handleCicloChange(this)">
                            <option value="21">21 Días</option>
                            <option value="7">7 Días</option>
                            <option value="10">10 Días</option>
                            <option value="15">15 Días</option>
                            <option value="30">30 Días</option>
                            <option value="60">2 Meses (60d)</option>
                            <option value="90">3 Meses (90d)</option>
                            <option value="180">6 Meses (180d)</option>
                            <option value="custom">Personalizado...</option>
                        </select>
                        <input type="date" name="fecha_proxima_manual[]" class="form-control" style="display:none; padding:8px; font-size:0.8rem;" onchange="handleFechaManual(this)">
                    </div>
                </div>
                <div class="acciones-linea">
                    <!-- Botón eliminar aparecerá en clones -->
                </div>
            </div>
        </div>

        <input type="hidden" id="usuario-rol" value="<?= htmlspecialchars($_SESSION['usuario_rol'] ?? '') ?>">
        <div class="modal-footer" style="border-top:none;padding-top:1rem;">
            <button type="button" class="btn btn-primario" id="btn-finalizar" onclick="validarYEnviarDespacho()"><i data-lucide="send"></i> Finalizar Despacho</button>
        </div>
    </form>
</div>

<style>
.linea-bloqueada { background: rgba(239, 68, 68, 0.1) !important; border: 1.5px solid var(--error) !important; border-radius: 8px; padding: 5px; }
.linea-bloqueada label, .linea-bloqueada input, .linea-bloqueada select { color: var(--error) !important; }
.mensaje-bloqueo { grid-column: span 5; color: var(--error); font-size: 0.75rem; font-weight: bold; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
</style>

<!-- 4.1.9 Modal Confirmación -->
<div class="modal-overlay" id="modal-confirmar">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">Confirmar Despacho</h3>
            <button class="modal-cerrar" onclick="document.getElementById('modal-confirmar').classList.remove('activo')"><i data-lucide="x"></i></button>
        </div>
        <div class="alerta alerta-info">
            <i data-lucide="info"></i> ¿Desea seguir con el despacho del medicamento?
        </div>
        <div id="resumen-despacho" style="margin:1.5rem 0; padding:1rem; background:var(--gris-claro); border-radius:8px;"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secundario" onclick="document.getElementById('modal-confirmar').classList.remove('activo')">Cancelar</button>
            <button type="submit" class="btn btn-primario" form="form-despacho">Confirmar y Despachar</button>
        </div>
    </div>
</div>

<!-- Modal override ciclo (Admin/Farmacéutico) -->
<div class="modal-overlay" id="modal-override">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">Autorizar Excepción</h3>
            <button class="modal-cerrar" onclick="document.getElementById('modal-override').classList.remove('activo')"><i data-lucide="x"></i></button>
        </div>
        <div class="alerta alerta-advertencia">
            <i data-lucide="alert-triangle"></i> El paciente todavía está en período de traitement. ¿Autorizar excepción?
        </div>
        <div class="form-grupo">
            <label class="form-etiqueta">Clave de Autorización *</label>
            <input type="password" name="clave_override" class="form-control" placeholder="Clave del Admin/Farmacéutico">
        </div>
        <div class="form-grupo">
            <label class="form-etiqueta">Motivo *</label>
            <textarea name="motivo_override" class="form-control" placeholder="Motivo de la excepción"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secundario" onclick="document.getElementById('modal-override').classList.remove('activo')">Cancelar</button>
            <button type="button" class="btn btn-primario" onclick="guardarOverride()">Autorizar Excepci��n</button>
        </div>
    </div>
</div>

<!-- Listado de despachos del día -->
<div class="card fade-in">
    <div class="card-header">
        <h2 class="card-titulo">Despachos del Día</h2>
        <span class="badge badge-azul"><?= date('d/m/Y') ?></span>
    </div>
    <?php if (empty($despachos)): ?>
    <div class="estado-vacio">
        <i data-lucide="clipboard-list"></i>
        <h3>Sin despachos registrados hoy</h3>
    </div>
    <?php else: ?>
    <div class="tabla-container">
        <table>
            <thead><tr><th>Ticket</th><th>Paciente</th><th>Hora</th><th>Monto Total</th><th>Estatus</th><th>Despachado por</th><?php if (($_SESSION['usuario_rol'] ?? '') === 'Administrador'): ?><th>Acciones</th><?php endif; ?></tr></thead>
            <tbody>
                <?php foreach ($despachos as $d): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($d['ticket']) ?></strong></td>
                    <td><?= htmlspecialchars($d['paciente_nombre'] ?? '') ?></td>
                    <td><?= date('h:i A', strtotime($d['fecha_despacho'])) ?></td>
                    <td><strong>Bs. <?= number_format($d['monto_total'] ?? 0, 2) ?></strong></td>
                    <td><span class="badge <?= $d['estatus'] === 'Despachado' ? 'badge-verde' : ($d['estatus'] === 'Anulado' ? 'badge-rojo' : 'badge-ambar') ?>"><?= $d['estatus'] ?></span></td>
                    <td><?= htmlspecialchars($d['despachador_nombre'] ?? '') ?></td>
                    <?php if (($_SESSION['usuario_rol'] ?? '') === 'Administrador'): ?>
                    <td>
                        <?php if ($d['estatus'] === 'Despachado'): ?>
                        <button type="button" class="btn btn-peligro btn-sm" onclick="abrirModalAnular(<?= $d['id'] ?>, '<?= htmlspecialchars($d['ticket'], ENT_QUOTES) ?>')" title="Anular despacho">
                            <i data-lucide="x-circle"></i> Anular
                        </button>
                        <?php else: ?>
                        <span class="badge badge-gris">—</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>



<script>


// =====================================================
// REFACTORIZACIÓN: Validaciones por Ítem y Ciclos Dinámicos
// =====================================================

function verificarCicloItem(select) {
    const parent = select.closest('[data-linea]');
    const option = select.options[select.selectedIndex];
    const aseguradoId = document.getElementById('asegurado_id').value;
    const medicamentoId = select.value;
    
    // Limpiar estados previos
    parent.classList.remove('linea-bloqueada');
    const msgPrevio = parent.querySelector('.mensaje-bloqueo');
    if (msgPrevio) msgPrevio.remove();

    if (!aseguradoId || !medicamentoId) return;

    // Mostrar Selector Dual si el medicamento es de Alto Costo o grupo restringido
    const grupo = option.dataset.grupo || '';
    const tipo = option.dataset.tipo || '';
    const esControlado = ['003', '004', '005'].includes(grupo) || tipo === 'Alto_Costo';
    
    const selectorDual = parent.querySelector('.selector-dual-cont');
    if (esControlado) {
        selectorDual.style.display = 'block';
    } else {
        selectorDual.style.display = 'none';
    }

    // 1. Verificar Duplicidad 24h (Todos)
    fetch(`index.php?url=api/verificar-duplicidad-item&asegurado_id=${aseguradoId}&medicamento_id=${medicamentoId}`)
        .then(r => r.json())
        .then(data => {
            if (data.advertencia) {
                if (confirm(data.mensaje)) {
                    // Autorizado por el usuario (Soft Warning)
                    parent.dataset.override24h = "true";
                } else {
                    // Marcar en rojo pero permitir remover
                    parent.classList.add('linea-bloqueada');
                    insertarMensajeBloqueo(parent, data.mensaje);
                }
            }
        });

    // 2. Verificar Ciclos (Solo Controlados/Alto Costo)
    if (esControlado) {
        fetch(`index.php?url=api/verificar-ciclo-dosis&asegurado_id=${aseguradoId}&medicamento_id=${medicamentoId}`)
            .then(r => r.json())
            .then(data => {
                if (data.bloqueado) {
                    parent.classList.add('linea-bloqueada');
                    parent.dataset.bloqueado = "true";
                    insertarMensajeBloqueo(parent, data.mensaje);
                } else {
                    parent.dataset.bloqueado = "false";
                }
            });
    }
}

function insertarMensajeBloqueo(parent, msg) {
    let span = parent.querySelector('.mensaje-bloqueo');
    if (!span) {
        span = document.createElement('div');
        span.className = 'mensaje-bloqueo';
        parent.appendChild(span);
    }
    span.innerHTML = `<i data-lucide="alert-octagon" style="width:14px;height:14px;"></i> ${msg}`;
    lucide.createIcons();
}

function handleCicloChange(select) {
    const parent = select.closest('.selector-dual-cont');
    const dateInput = parent.querySelector('input[type="date"]');
    if (select.value === 'custom') {
        dateInput.style.display = 'block';
        dateInput.required = true;
    } else {
        dateInput.style.display = 'none';
        dateInput.required = false;
    }
}

function handleFechaManual(input) {
    // Podríamos calcular los días aquí si fuera necesario
}

function validarYEnviarDespacho() {
    const bloqueados = document.querySelectorAll('.linea-bloqueada');
    if (bloqueados.length > 0) {
        alert('⚠️ Por favor, elimine los ítems resaltados en rojo para procesar el resto del despacho.');
        return;
    }

    if (!document.getElementById('asegurado_id').value) {
        alert('Debe seleccionar un paciente.');
        return;
    }

    // Modal de resumen final
    mostrarModalConfirmacion();
}

function buscarPaciente() {
    const cedula = document.getElementById('buscar-cedula').value.trim();
    if (!cedula) return alert('Ingrese una cédula');
    
    fetch('index.php?url=api/buscar-paciente&cedula=' + encodeURIComponent(cedula))
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                if (confirm('Paciente no registrado. ¿Desea registrarlo?')) {
                    window.location.href = 'index.php?url=asegurados&cedula=' + encodeURIComponent(cedula);
                }
                return;
            }
            document.getElementById('asegurado_id').value = data.paciente.id;
            document.getElementById('paciente-nombre').value = data.paciente.nombre + ' ' + data.paciente.apellido;
            
            // Si tiene alertas de identidad, mostrarlas
            if (data.paciente.alerta_identidad) {
                alert(data.paciente.alerta_identidad.motivo);
            }
        })
        .catch(() => alert('Error de conexión'));
}

function agregarLineaMedicamento() {
    const contenedor = document.getElementById('lineas-medicamento');
    const primera = contenedor.querySelector('[data-linea]');
    const nueva = primera.cloneNode(true);
    
    // Resetear valores
    nueva.classList.remove('linea-bloqueada');
    nueva.removeAttribute('data-bloqueado');
    nueva.removeAttribute('data-override24h');
    const msg = nueva.querySelector('.mensaje-bloqueo');
    if (msg) msg.remove();
    
    nueva.querySelectorAll('input').forEach(i => i.value = i.type === 'number' ? '1' : '');
    nueva.querySelector('.selector-dual-cont').style.display = 'none';
    
    // Botón eliminar
    const acciones = nueva.querySelector('.acciones-linea');
    acciones.innerHTML = '<button type="button" class="btn-icono btn-peligro" onclick="this.closest(\'[data-linea]\').remove()" style="margin-bottom:0;" title="Quitar línea"><i data-lucide="trash-2"></i></button>';
    
    contenedor.appendChild(nueva);
    lucide.createIcons();
}

function mostrarModalConfirmacion() {
    const lineas = document.querySelectorAll('[data-linea]');
    let html = '<table class="tabla-resumen" style="width:100%; font-size:0.85rem;"><thead><tr><th>Medicamento</th><th>Cant</th><th>Ciclo</th></tr></thead><tbody>';
    
    lineas.forEach(l => {
        const sel = l.querySelector('select[name="medicamento_id[]"]');
        if (sel.value) {
            const cant = l.querySelector('input[name="cantidad[]"]').value;
            const ciclo = l.querySelector('select[name="ciclo_asignado[]"]').value;
            html += `<tr><td>${sel.options[sel.selectedIndex].text}</td><td>${cant}</td><td>${ciclo}d</td></tr>`;
        }
    });
    
    html += '</tbody></table>';
    document.getElementById('resumen-despacho').innerHTML = html;
    document.getElementById('modal-confirmar').classList.add('activo');
}

// =====================================================
// CORRECCIÓN 4: Modal de Anulación (Solo Admin)
// =====================================================
function abrirModalAnular(despachoId, ticket) {
    document.getElementById('anular-despacho-id').value = despachoId;
    document.getElementById('anular-ticket-label').textContent = ticket;
    document.getElementById('anular-motivo').value = '';
    document.getElementById('modal-anular').classList.add('activo');
}
</script>

<!-- Modal de Anulación de Despacho (Solo Admin) -->
<?php if (($_SESSION['usuario_rol'] ?? '') === 'Administrador'): ?>
<div class="modal-overlay" id="modal-anular">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-titulo">⚠️ Anular Despacho</h3>
            <button type="button" class="modal-cerrar" onclick="this.closest('.modal-overlay').classList.remove('activo')"><i data-lucide="x"></i></button>
        </div>
        <form method="POST" action="index.php?url=despachos/anular">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="despacho_id" id="anular-despacho-id">
            <div class="alerta alerta-advertencia" style="margin-bottom:1.5rem;">
                <i data-lucide="alert-triangle"></i>
                <div>
                    <strong>Atención:</strong> Al anular el despacho <strong id="anular-ticket-label"></strong>, 
                    el stock será devuelto automáticamente a los lotes originales y se registrará en el Kardex.
                </div>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Motivo de Anulación *</label>
                <textarea name="motivo_anulacion" id="anular-motivo" class="form-control" required placeholder="Describa el motivo de la anulación..." style="min-height:80px;resize:vertical;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secundario" onclick="this.closest('.modal-overlay').classList.remove('activo')">Cancelar</button>
                <button type="submit" class="btn btn-peligro"><i data-lucide="x-circle"></i> Confirmar Anulación</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

