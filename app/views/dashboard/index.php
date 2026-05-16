<?php
/** SIGFA - Vista: Dashboard Principal Modularizado */
$tituloPagina = 'Panel de Control';
$paginaActual = 'dashboard';
require_once __DIR__ . '/../layouts/header.php';

// Los datos vienen del DashboardController
$metrics = [
    'total_medicamentos' => $metricas['total_medicamentos'] ?? 0,
    'despachos_hoy' => $metricas['despachos_hoy'] ?? 0,
    'alertas_vencimiento' => $metricas['alertas_vencimiento'] ?? 0,
    'stock_bajo' => $metricas['lotes_stock_bajo'] ?? 0
];
// No tenemos actividad global en $metricas, usaremos los despachos recientes como actividad
$actividad = [];
foreach ($metricas['despachos_recientes'] ?? [] as $d) {
    $actividad[] = [
        'tipo_movimiento' => 'Salida',
        'nombre_generico' => 'Registro #'.$d['ticket'].' - '.($d['paciente_nombre'] ?? 'Paciente'),
        'motivo' => 'Despacho generado',
        'fecha_movimiento' => $d['fecha_despacho']
    ];
}
$proximos = $metricas['lotes_por_vencer'] ?? [];
?>

<style>
/* Estilos específicos del Dashboard */
.saludo-card { background: linear-gradient(135deg, var(--azul-marino) 0%, var(--azul-claro) 50%, var(--azul-acento) 100%); border-radius: var(--radio-lg); padding: 2rem 2.5rem; color: var(--blanco); display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; position: relative; overflow: hidden; box-shadow: 0 8px 32px rgba(0, 36, 102, 0.2); }
.saludo-card::before { content: ''; position: absolute; top: -50%; right: -20%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(255,255,255,0.08), transparent 60%); border-radius: 50%; }
.saludo-card::after { content: ''; position: absolute; bottom: -40%; left: 10%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(91, 156, 245, 0.15), transparent 60%); border-radius: 50%; }
.saludo-info { position: relative; z-index: 1; }
.saludo-label { font-size: 0.82rem; opacity: 0.7; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; }
.saludo-nombre { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.6rem; letter-spacing: -0.5px; margin-bottom: 0.35rem; }
.saludo-desc { font-size: 0.9rem; opacity: 0.8; }
.saludo-fecha { position: relative; z-index: 1; text-align: right; }
.saludo-fecha-dia { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2.8rem; line-height: 1; letter-spacing: -1px; }
.saludo-fecha-mes { font-size: 0.9rem; opacity: 0.7; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 2rem; }
.stat-card { background: var(--blanco); border: 1px solid var(--gris-perla); border-radius: var(--radio-lg); padding: 1.5rem; transition: var(--transicion); }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0, 36, 102, 0.08); border-color: transparent; }
.stat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.stat-icono { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.stat-icono svg { width: 22px; height: 22px; }
.stat-icono.azul { background: rgba(59, 111, 204, 0.1); color: var(--azul-acento); }
.stat-icono.verde { background: rgba(16, 185, 129, 0.1); color: var(--exito); }
.stat-icono.ambar { background: rgba(245, 158, 11, 0.1); color: var(--alerta); }
.stat-icono.rojo { background: rgba(239, 68, 68, 0.1); color: var(--error); }
.stat-cambio { font-size: 0.72rem; font-weight: 600; padding: 3px 8px; border-radius: 6px; }
.stat-cambio.positivo { background: rgba(16,185,129,0.1); color: var(--exito); }
.stat-valor { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2rem; color: var(--texto-primario); letter-spacing: -1px; margin-bottom: 0.2rem; }
.stat-etiqueta { font-size: 0.82rem; color: var(--gris-suave); font-weight: 500; }

.paneles-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.panel-info { background: var(--blanco); border: 1px solid var(--gris-perla); border-radius: var(--radio-lg); padding: 1.5rem; }
.panel-info-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.panel-info-titulo { font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 1.05rem; color: var(--texto-primario); letter-spacing: -0.3px; }
.panel-info-badge { font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 8px; background: var(--blanco-apagado); color: var(--texto-secundario); }

.actividad-lista { list-style: none; }
.actividad-item { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid rgba(226, 232, 240, 0.5); }
.actividad-item:last-child { border-bottom: none; }
.actividad-icono { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.actividad-icono svg { width: 16px; height: 16px; }
.actividad-icono.entrada { background: rgba(16,185,129,0.1); color: var(--exito); }
.actividad-icono.salida { background: rgba(59,111,204,0.1); color: var(--azul-acento); }

.actividad-info { flex: 1; min-width: 0; }
.actividad-titulo { font-size: 0.85rem; font-weight: 600; color: var(--texto-primario); margin-bottom: 2px; }
.actividad-desc { font-size: 0.78rem; color: var(--gris-suave); }
.actividad-hora { font-size: 0.72rem; color: var(--gris-suave); white-space: nowrap; }

/* Alerta vencimiento lista */
.alerta-vencimiento { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid rgba(226,232,240,0.5); }
.alerta-vencimiento:last-child { border-bottom: none; }
.alerta-v-punto { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.alerta-v-punto.critico { background: var(--error); box-shadow: 0 0 8px rgba(239,68,68,0.4); }
.alerta-v-punto.advertencia { background: var(--alerta); box-shadow: 0 0 8px rgba(245,158,11,0.3); }
.alerta-v-info { flex: 1; }
.alerta-v-nombre { font-size: 0.85rem; font-weight: 600; color: var(--texto-primario); }
.alerta-v-lote { font-size: 0.75rem; color: var(--gris-suave); }
.alerta-v-dias { font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 8px; }
.alerta-v-dias.critico { background: rgba(239,68,68,0.1); color: var(--error); }
.alerta-v-dias.advertencia { background: rgba(245,158,11,0.1); color: var(--alerta); }
</style>

<!-- Saludo -->
<div class="saludo-card fade-in">
    <div class="saludo-info">
        <div class="saludo-label">Bienvenido de nuevo</div>
        <div class="saludo-nombre"><?= htmlspecialchars($nombreUsuario) ?></div>
        <div class="saludo-desc">Aquí tienes un resumen de la actividad del sistema hoy.</div>
    </div>
    <div class="saludo-fecha">
        <div class="saludo-fecha-dia"><?= date('d') ?></div>
        <div class="saludo-fecha-mes"><?= strftime('%B %Y') ?: date('F Y') ?></div>
    </div>
</div>

<!-- Estadísticas -->
<div class="stats-grid">
    <div class="stat-card fade-in">
        <div class="stat-header">
            <div class="stat-icono azul"><i data-lucide="tablets"></i></div>
            <span class="stat-cambio positivo">Activo</span>
        </div>
        <div class="stat-valor"><?= $metrics['total_medicamentos'] ?></div>
        <div class="stat-etiqueta">Medicamentos registrados</div>
    </div>
    <div class="stat-card fade-in">
        <div class="stat-header">
            <div class="stat-icono verde"><i data-lucide="clipboard-check"></i></div>
            <span class="stat-cambio positivo">Hoy</span>
        </div>
        <div class="stat-valor"><?= $metrics['despachos_hoy'] ?></div>
        <div class="stat-etiqueta">Despachos del día</div>
    </div>
    <div class="stat-card fade-in" onclick="window.location.href='index.php?url=inventario/alertas'" style="cursor:pointer">
        <div class="stat-header">
            <div class="stat-icono ambar"><i data-lucide="alert-triangle"></i></div>
        </div>
        <div class="stat-valor"><?= $metrics['alertas_vencimiento'] ?></div>
        <div class="stat-etiqueta">Alertas de vencimiento</div>
    </div>
    <div class="stat-card fade-in" onclick="window.location.href='index.php?url=inventario/alertas'" style="cursor:pointer">
        <div class="stat-header">
            <div class="stat-icono rojo"><i data-lucide="package-x"></i></div>
        </div>
        <div class="stat-valor"><?= $metrics['stock_bajo'] ?></div>
        <div class="stat-etiqueta">Lotes con stock bajo</div>
    </div>
</div>

<!-- Paneles -->
<div class="paneles-grid">
    <!-- Actividad reciente (Kardex) -->
    <div class="panel-info fade-in">
        <div class="panel-info-header">
            <h3 class="panel-info-titulo">Actividad Reciente</h3>
            <span class="panel-info-badge">Hoy</span>
        </div>
        
        <?php if (empty($actividad)): ?>
            <div style="text-align: center; padding: 2rem 1rem; color: var(--gris-suave);">
                <i data-lucide="clock" style="width: 40px; height: 40px; margin-bottom: 0.75rem; opacity:0.5;"></i>
                <p style="font-size: 0.9rem; font-weight: 600; color: var(--texto-secundario);">Sin actividad reciente</p>
            </div>
        <?php else: ?>
        <ul class="actividad-lista">
            <?php foreach (array_slice($actividad, 0, 5) as $act): ?>
            <li class="actividad-item">
                <div class="actividad-icono <?= $act['tipo_movimiento'] === 'Entrada' ? 'entrada' : 'salida' ?>">
                    <i data-lucide="<?= $act['tipo_movimiento'] === 'Entrada' ? 'package-plus' : 'package-minus' ?>"></i>
                </div>
                <div class="actividad-info">
                    <div class="actividad-titulo"><?= $act['tipo_movimiento'] ?> de <?= htmlspecialchars($act['nombre_generico']) ?></div>
                    <div class="actividad-desc"><?= htmlspecialchars($act['motivo'] ?? '') ?></div>
                </div>
                <span class="actividad-hora"><?= date('H:i', strtotime($act['fecha_movimiento'])) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <div style="text-align:center; margin-top:1rem;">
            <a href="index.php?url=inventario/kardex" style="font-size:0.8rem; font-weight:600; color:var(--azul-acento); text-decoration:none;">Ver todo el Kardex &rarr;</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Alertas de vencimiento -->
    <div class="panel-info fade-in">
        <div class="panel-info-header">
            <h3 class="panel-info-titulo">Próximos a Vencer</h3>
            <span class="panel-info-badge">&lt; 30 días</span>
        </div>
        
        <?php if (empty($proximos)): ?>
        <div style="text-align: center; padding: 2rem 1rem; color: var(--gris-suave);">
            <i data-lucide="check-circle" style="width: 40px; height: 40px; margin-bottom: 0.75rem; color: var(--exito);"></i>
            <p style="font-size: 0.9rem; font-weight: 600; color: var(--texto-secundario);">Sin alertas pendientes</p>
            <p style="font-size: 0.78rem; margin-top: 0.25rem;">Los lotes se monitorean automáticamente</p>
        </div>
        <?php else: ?>
        <div>
            <?php foreach (array_slice($proximos, 0, 5) as $p): ?>
            <?php $dias = (int)$p['dias_para_vencer']; ?>
            <div class="alerta-vencimiento">
                <div class="alerta-v-punto <?= $dias <= 7 ? 'critico' : 'advertencia' ?>"></div>
                <div class="alerta-v-info">
                    <div class="alerta-v-nombre"><?= htmlspecialchars($p['nombre_generico']) ?></div>
                    <div class="alerta-v-lote">Lote: <?= htmlspecialchars($p['numero_lote']) ?></div>
                </div>
                <div class="alerta-v-dias <?= $dias <= 7 ? 'critico' : 'advertencia' ?>">
                    <?= $dias ?> días
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center; margin-top:1rem;">
            <a href="index.php?url=inventario/alertas" style="font-size:0.8rem; font-weight:600; color:var(--error); text-decoration:none;">Ver todas las alertas &rarr;</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
