<?php
/** SIGFA - Vista: Entrada de Lotes al Inventario */
$tituloPagina = 'Entrada de Medicamentos';
$paginaActual = 'inventario';
require_once __DIR__ . '/../layouts/header.php';

// Cargar almacenes para el ComboBox
require_once __DIR__ . '/../../models/Almacen.php';
$almacenModel = new Almacen();
$almacenes = $almacenModel->listarActivos();
?>

<?php if (!empty($exito)): ?>
<div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card fade-in">
    <div class="card-header">
        <h2 class="card-titulo">Registrar Entrada de Medicamentos</h2>
    </div>
    <form method="POST" action="index.php?url=inventario/entrada">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <h4 style="font-family:'Outfit'; font-weight:600; margin-bottom:1rem; color:var(--azul-acento); font-size:0.9rem;">DATOS DEL PROVEEDOR Y ALMACÉN</h4>
        <div class="form-grid">
            <div class="form-grupo">
                <label class="form-etiqueta">Almacén Destino *</label>
                <select name="almacen_id" class="form-control" required data-ajax-url="index.php?url=ajax/almacenes" placeholder="Buscar almacén...">
                    <option value="">Seleccione...</option>
                </select>
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Proveedor *</label>
                <select name="proveedor_id" class="form-control" required data-ajax-url="index.php?url=ajax/proveedores" placeholder="Buscar proveedor por razón social o RIF...">
                    <option value="">— Seleccione —</option>
                </select>
            </div>
        </div>

        <h4 style="font-family:'Outfit'; font-weight:600; margin:1.5rem 0 1rem; color:var(--azul-acento); font-size:0.9rem;">MEDICAMENTOS A INGRESAR</h4>
        <div id="medicamentos-container">
            <div class="medicamento-fila" style="background:#f8fafc; border:1px solid #e2e8f0; padding:15px; border-radius:8px; margin-bottom:15px; position:relative;">
                <div class="form-grid-3">
                    <div class="form-grupo" style="grid-column: span 2;">
                        <label class="form-etiqueta">Medicamento *</label>
                        <select name="medicamento_id[]" class="form-control" required data-ajax-url="index.php?url=ajax/medicamentos" placeholder="Buscar medicamento por nombre o código...">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Número de Lote *</label>
                        <input type="text" name="numero_lote[]" class="form-control" required placeholder="LOT-2026-001">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Cantidad *</label>
                        <input type="number" name="cantidad[]" class="form-control" required min="1" placeholder="100">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Fecha de Fabricación</label>
                        <input type="date" name="fecha_fabricacion[]" class="form-control">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Fecha de Vencimiento *</label>
                        <input type="date" name="fecha_vencimiento[]" class="form-control" required>
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Precio Unitario (Bs.)</label>
                        <input type="number" name="precio_unitario[]" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
                <button type="button" class="btn btn-peligro btn-eliminar-fila" style="position:absolute; top:15px; right:15px; display:none;"><i data-lucide="trash"></i></button>
            </div>
        </div>
        
        <button type="button" class="btn btn-secundario" id="btn-agregar-fila" style="margin-bottom:1.5rem;"><i data-lucide="plus"></i> Agregar otro medicamento</button>

        <h4 style="font-family:'Outfit'; font-weight:600; margin:1.5rem 0 1rem; color:var(--azul-acento); font-size:0.9rem;">DATOS DE TRANSPORTE</h4>
        <div class="form-grid">
            <div class="form-grupo">
                <label class="form-etiqueta">Número de Guía</label>
                <input type="text" name="numero_guia" class="form-control" placeholder="Guía de despacho/remisión">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Nombre del Chofer</label>
                <input type="text" name="chofer_nombre" class="form-control">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Cédula del Chofer</label>
                <input type="text" name="chofer_cedula" class="form-control" placeholder="V-12345678">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Teléfono del Chofer</label>
                <input type="text" name="chofer_telefono" class="form-control" placeholder="0412-0000000">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Correo del Chofer</label>
                <input type="email" name="chofer_correo" class="form-control" placeholder="chofer@mail.com">
            </div>
            <div class="form-grupo">
                <label class="form-etiqueta">Placa del Vehículo</label>
                <input type="text" name="placa_vehiculo" class="form-control" placeholder="ABC-123">
            </div>
            <div class="form-grupo col-span-2">
                <label class="form-etiqueta">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2"></textarea>
            </div>
        </div>

        <div class="modal-footer" style="border-top:none;">
            <button type="reset" class="btn btn-secundario">Limpiar</button>
            <button type="submit" class="btn btn-primario"><i data-lucide="package-plus"></i> Registrar Entrada</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('medicamentos-container');
    const btnAgregar = document.getElementById('btn-agregar-fila');

    function actualizarBotonesEliminar() {
        const filas = container.querySelectorAll('.medicamento-fila');
        filas.forEach((fila, index) => {
            const btnEliminar = fila.querySelector('.btn-eliminar-fila');
            if (filas.length > 1) {
                btnEliminar.style.display = 'block';
            } else {
                btnEliminar.style.display = 'none';
            }
        });
        if (window.lucide) window.lucide.createIcons();
    }

    btnAgregar.addEventListener('click', () => {
        const primeraFila = container.querySelector('.medicamento-fila');
        const nuevaFila = primeraFila.cloneNode(true);
        
        // Limpiar valores
        nuevaFila.querySelectorAll('input').forEach(input => input.value = '');
        nuevaFila.querySelectorAll('select').forEach(select => select.value = '');
        
        container.appendChild(nuevaFila);
        actualizarBotonesEliminar();
    });

    container.addEventListener('click', (e) => {
        const btnEliminar = e.target.closest('.btn-eliminar-fila');
        if (btnEliminar) {
            btnEliminar.closest('.medicamento-fila').remove();
            actualizarBotonesEliminar();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
