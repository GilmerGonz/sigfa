<?php
/** SIGFA - Vista: Medicamentos — CRUD completo */
$tituloPagina = 'Medicamentos';
$paginaActual = 'medicamentos';
require_once __DIR__ . '/../layouts/header.php';

// Cargar grupos para el ComboBox
require_once __DIR__ . '/../../models/GrupoMedicamento.php';
$grupoModel = new GrupoMedicamento();
$grupos = $grupoModel->listarActivos();
?>

<?php if (!empty($exito)): ?>
<div class="alerta alerta-exito"><i data-lucide="check-circle"></i> <?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alerta alerta-error"><i data-lucide="alert-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="modulo-grid fade-in">
    <!-- Sidebar: Formulario de Registro -->
    <aside class="grid-sidebar">
        <div class="card card-compact">
            <div class="card-header">
                <h3 class="card-titulo">Registrar Medicamento</h3>
            </div>
            <form method="POST" action="index.php?url=medicamentos/crear" class="form-compact">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-grid">
                    <div class="form-grupo">
                        <label class="form-etiqueta">Código *</label>
                        <input type="text" name="codigo" class="form-control" required placeholder="MED-001" style="text-transform:uppercase;">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Stock Mín. *</label>
                        <input type="number" name="stock_minimo" class="form-control" value="10" min="0">
                    </div>
                </div>
                
                <div class="form-grupo">
                    <label class="form-etiqueta">Nombre Genérico *</label>
                    <input type="text" name="nombre_generico" class="form-control" required placeholder="Ej: Paracetamol">
                </div>

                <div class="form-grupo">
                    <label class="form-etiqueta">Nombre Comercial</label>
                    <input type="text" name="nombre_comercial" class="form-control" placeholder="Opcional">
                </div>

                <div class="form-grid">
                    <div class="form-grupo">
                        <label class="form-etiqueta">Concentración *</label>
                        <input type="text" name="concentracion" class="form-control" required placeholder="500mg">
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Tipo de Forma *</label>
                        <select name="tipo" class="form-control" required>
                            <option value="">...</option>
                            <option>Tableta</option>
                            <option>Cápsula</option>
                            <option>Jarabe</option>
                            <option>Inyectable</option>
                            <option>Crema</option>
                            <option>Ungüento</option>
                            <option>Gotas</option>
                            <option>Supositorio</option>
                            <option>Solución</option>
                            <option>Suspensión</option>
                            <option>Ampolla</option>
                            <option>Otro</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-grupo">
                        <label class="form-etiqueta">Presentación *</label>
                        <select name="presentacion" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option>Caja x 10</option>
                            <option>Caja x 20</option>
                            <option>Caja x 30</option>
                            <option>Caja x 50</option>
                            <option>Caja x 100</option>
                            <option>Frasco x 30ml</option>
                            <option>Frasco x 60ml</option>
                            <option>Frasco x 100ml</option>
                            <option>Frasco x 120ml</option>
                            <option>Frasco x 250ml</option>
                            <option>Blíster x 10</option>
                            <option>Sobre x 5</option>
                            <option>Sobre x 10</option>
                            <option>Tubo x 15g</option>
                            <option>Tubo x 30g</option>
                            <option>Tubo x 50g</option>
                            <option>Otro</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label class="form-etiqueta">Grupo</label>
                        <select name="grupo_id" class="form-control">
                            <option value="">Sin grupo</option>
                            <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['codigo'] . ' - ' . $g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grupo">
                    <label class="form-etiqueta">Clasificación *</label>
                    <select name="tipo_medicamento" class="form-control" required>
                        <option value="General">Medicamento General</option>
                        <option value="Alto_Costo">Medicamento de Alto Costo</option>
                    </select>
                </div>

                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primario btn-sm" style="width: 100%; justify-content: center;">
                        <i data-lucide="save"></i> Registrar Medicamento
                    </button>
                </div>
            </form>
        </div>
    </aside>

    <!-- Main: Catálogo de Medicamentos -->
    <div class="grid-main">
        <div class="card">
            <div class="card-header">
                <h2 class="card-titulo">Catálogo de Medicamentos</h2>
                <span class="badge badge-azul"><?= count($medicamentos) ?? 0 ?> registros</span>
            </div>
            
            <?php if (empty($medicamentos)): ?>
            <div class="estado-vacio">
                <i data-lucide="tablets"></i>
                <h3>Sin medicamentos registrados</h3>
                <p>Agregue medicamentos al catálogo usando el formulario lateral</p>
            </div>
            <?php else: ?>
            <div class="tabla-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre Genérico</th>
                            <th>Concentración</th>
                            <th>Tipo</th>
                            <th>Grupo</th>
                            <th>Stock</th>
                            <th>Clasificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicamentos as $m): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($m['codigo']) ?></strong></td>
                            <td title="<?= htmlspecialchars($m['nombre_comercial'] ?? '') ?>">
                                <?= htmlspecialchars($m['nombre_generico']) ?>
                                <?php if (!empty($m['nombre_comercial'])): ?>
                                <br><small style="color: var(--gris-suave)">(<?= htmlspecialchars($m['nombre_comercial']) ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($m['concentracion']) ?></td>
                            <td><span class="badge badge-azul"><?= htmlspecialchars($m['tipo']) ?></span></td>
                            <td>
                                <?php if (!empty($m['grupo_id'])): ?>
                                <span class="badge badge-gris"><?= htmlspecialchars($m['grupo_codigo'] ?? '') ?></span>
                                <?php else: ?>
                                —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $stock = (int)($m['stock_total'] ?? 0);
                                $minimo = (int)($m['stock_minimo'] ?? 10);
                                $clase = $stock <= 0 ? 'badge-rojo' : ($stock <= $minimo ? 'badge-ambar' : 'badge-verde');
                                ?>
                                <span class="badge <?= $clase ?>"><?= $stock ?></span>
                            </td>
                            <td>
                                <?php
                                $tipoMedClase = ($m['tipo_medicamento'] ?? 'General') === 'Alto_Costo' ? 'badge-ambar' : 'badge-verde';
                                $tipoMedLabel = ($m['tipo_medicamento'] ?? 'General') === 'Alto_Costo' ? 'Alto Costo' : 'General';
                                ?>
                                <span class="badge <?= $tipoMedClase ?>"><?= $tipoMedLabel ?></span>
                            </td>
                            <td>
                                <a href="index.php?url=inventario/kardex&medicamento_id=<?= $m['id'] ?>" class="btn-icono" title="Kardex">
                                    <i data-lucide="book-open"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
