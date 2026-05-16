<?php
/**
 * SIGFA - Layout compartido: Header + Sidebar
 * Variables esperadas: $tituloPagina, $paginaActual
 */

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario    = $_SESSION['usuario_rol'] ?? 'Auxiliar';
$paginaActual  = $paginaActual ?? 'dashboard';

// Token CSRF para formularios
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Cargar middleware de permisos
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Permisos por rol
$esAdmin = ($rolUsuario === 'Administrador');
$esAuxGeneral = ($rolUsuario === 'Auxiliar_General');
$esAuxAltoCosto = ($rolUsuario === 'Auxiliar_Alto_Costo');
$esAlmacenista = ($rolUsuario === 'Almacenista');
$esFarmaceutico = ($rolUsuario === 'Farmaceutico');
$esKardista = ($rolUsuario === 'Kardista');

// Módulos permitidos
$puedeDespachar = in_array($rolUsuario, ['Administrador', 'Auxiliar_General', 'Auxiliar_Alto_Costo', 'Farmaceutico']);
$puedeEntrada = in_array($rolUsuario, ['Administrador', 'Almacenista', 'Farmaceutico']);
$puedeTransferencia = in_array($rolUsuario, ['Administrador', 'Almacenista', 'Farmaceutico']);
$puedeDevolucion = in_array($rolUsuario, ['Administrador', 'Almacenista', 'Farmaceutico']);
$puedeMedicamentos = in_array($rolUsuario, ['Administrador', 'Farmaceutico']);
$puedeProveedores = in_array($rolUsuario, ['Administrador', 'Almacenista', 'Farmaceutico']);
$puedeUsuarios = ($rolUsuario === 'Administrador');
$puedeAjustar = ($rolUsuario === 'Administrador');
$puedeReportes = in_array($rolUsuario, ['Administrador', 'Auxiliar_General', 'Auxiliar_Alto_Costo', 'Almacenista', 'Farmaceutico', 'Kardista']);
$puedeVerPacientes = in_array($rolUsuario, ['Administrador', 'Auxiliar_General', 'Auxiliar_Alto_Costo', 'Farmaceutico', 'Kardista']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SIGFA | <?= htmlspecialchars($tituloPagina ?? 'Sistema') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            /* Glassmorphism Palette */
            --glass-bg: rgba(255, 255, 255, 0.45);
            --glass-bg-accent: rgba(59, 111, 204, 0.08);
            --glass-border: rgba(255, 255, 255, 0.65);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
            
            --azul-marino: #002466;
            --azul-profundo: #111827;
            --azul-medianoche: #030712;
            --azul-claro: #1e3a8a;
            --azul-acento: #3b82f6;
            --azul-celeste: #60a5fa;
            --azul-hielo: #dbeafe;
            --blanco: #ffffff;
            --blanco-apagado: #fdfdfd;
            --gris-perla: rgba(226, 232, 240, 0.5);
            --gris-suave: #64748b;
            --gris-claro: #f8fafc;
            --texto-primario: #111827;
            --texto-secundario: #4b5563;
            --exito: #10b981;
            --error: #ef4444;
            --alerta: #f59e0b;
            --info: #3b82f6;
            --radio-sm: 10px;
            --radio-md: 16px;
            --radio-lg: 24px;
            --radio-xl: 32px;
            --transicion: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-ancho: 280px;
        }
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; max-width: 100%; }
        html { font-size: 16px; -webkit-font-smoothing: antialiased; zoom: 75%; }
        body { 
            font-family: 'DM Sans', sans-serif; 
            background: linear-gradient(135deg, #f0f4f8 0%, #dbeafe 50%, #f0f9ff 100%);
            background-attachment: fixed;
            color: var(--texto-primario); 
            min-height: 100vh; 
            display: block;
            overflow-x: hidden;
        }

        /* NAVBAR HORIZONTAL */
        /* NAVBAR HORIZONTAL PREMIUM */
        .navbar { 
            background: rgba(255, 255, 255, 0.88); 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px); 
            border-bottom: 1px solid var(--glass-border); 
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            z-index: 9999; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 0 2rem; 
            box-shadow: 0 4px 25px rgba(0,0,0,0.03); 
            height: 75px;
        }

        .navbar-left-group {
            display: flex;
            align-items: center;
            gap: 2rem;
            height: 100%;
        }

        .navbar-logo { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            text-decoration: none; 
        }

        .navbar-logo-icono { 
            width: 42px; 
            height: 42px; 
            background: linear-gradient(135deg, var(--azul-acento), var(--azul-celeste)); 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            box-shadow: 0 4px 12px rgba(59,130,246,0.3); 
        }

        .navbar-logo-texto { 
            font-family: 'Outfit', sans-serif; 
            font-weight: 800; 
            font-size: 1.5rem; 
            color: var(--azul-marino); 
            letter-spacing: -1px; 
        }
        
        .navbar-menu { 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            background: rgba(255,255,255,0.4);
            padding: 6px;
            border-radius: 16px;
            border: 1px solid var(--glass-border);
        }

        .nav-item { position: relative; }

        .nav-link { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            padding: 10px 18px; 
            border-radius: 12px; 
            color: var(--texto-secundario); 
            text-decoration: none; 
            font-size: 0.95rem; 
            font-weight: 700; 
            transition: var(--transicion); 
        }

        .nav-link:hover { 
            background: var(--blanco); 
            color: var(--azul-acento); 
            box-shadow: 0 6px 15px rgba(0,0,0,0.05);
            transform: translateY(-1px);
        }

        /* INDICADOR ACTIVO (SUBRAYADO) */
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 6px;
            left: 16px;
            right: 16px;
            height: 2.5px;
            background: var(--azul-acento);
            border-radius: 2px;
            transform: scaleX(0);
            transition: var(--transicion);
            transform-origin: center;
        }

        .nav-link.activo { 
            color: var(--azul-acento);
        }

        .nav-link.activo::after {
            transform: scaleX(1);
        }

        .nav-link svg { width: 18px; height: 18px; }
        .nav-arrow { width: 12px !important; height: 12px !important; opacity: 0.6; transition: var(--transicion); margin-left: 2px; }
        .nav-item:hover .nav-arrow { transform: rotate(180deg); color: var(--azul-acento); }

        /* Icono de Logout Especial */
        .btn-logout {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: var(--error);
            transition: var(--transicion);
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.1);
        }
        .btn-logout:hover {
            background: var(--error);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        
        /* DROPDOWN */
        .nav-dropdown { 
            position: absolute; 
            top: 100%; 
            left: 0; 
            min-width: 260px; 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 16px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.12); 
            padding: 10px; 
            opacity: 0; 
            visibility: hidden; 
            transform: translateY(10px);
            transition: var(--transicion); 
            margin-top: 12px; 
            z-index: 10000; 
        }
        .nav-item:hover .nav-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
        
        .nav-dropdown-title { 
            font-size: 0.7rem; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            color: var(--gris-suave); 
            padding: 12px 14px 8px; 
            border-bottom: 1px solid var(--gris-perla);
            margin-bottom: 8px;
        }

        .nav-dropdown-link { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            padding: 12px 14px; 
            border-radius: 12px; 
            color: var(--texto-secundario); 
            text-decoration: none; 
            font-size: 0.9rem; 
            font-weight: 600; 
            transition: var(--transicion); 
        }

        .nav-dropdown-link:hover { 
            background: var(--azul-hielo); 
            color: var(--azul-acento); 
            transform: translateX(6px);
        }

        .nav-dropdown-link.activo { 
            background: var(--glass-bg-accent); 
            color: var(--azul-acento); 
            font-weight: 800; 
        }

        .nav-dropdown-link svg { width: 18px; height: 18px; }
        
        /* DERECHA (USUARIO SIMPLE) */
        .navbar-right { 
            display: flex; 
            align-items: center; 
            gap: 1.25rem; 
            flex-shrink: 0;
        }

        .navbar-user { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            padding: 6px 16px 6px 6px; 
            background: rgba(255,255,255,0.6); 
            border: 1px solid var(--glass-border); 
            border-radius: 14px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .user-avatar { 
            width: 42px; 
            height: 42px; 
            border-radius: 12px; 
            background: linear-gradient(135deg, var(--azul-marino), var(--azul-acento)); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: white; 
            font-weight: 800; 
            font-size: 1.1rem; 
            flex-shrink: 0;
        }

        .user-info { text-align: left; }
        .user-name { font-weight: 800; font-size: 0.85rem; color: var(--texto-primario); line-height: 1.1; }
        .user-role { font-size: 0.7rem; color: var(--gris-suave); font-weight: 600; }

        .navbar-notification {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            background: var(--blanco);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transicion);
            color: var(--texto-secundario);
            text-decoration: none;
            position: relative;
        }

        .navbar-notification:hover {
            background: var(--azul-hielo);
            color: var(--azul-acento);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .notification-dot {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 10px;
            height: 10px;
            background: var(--error);
            border-radius: 50%;
            border: 2px solid var(--blanco);
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }


        /* CONTENIDO PRINCIPAL */
        .contenido-principal { margin-left: 0; min-height: 100vh; position: relative; padding-top: 70px; }
        .topbar { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid var(--glass-border); padding: 1rem 2.5rem; display: flex; align-items: center; justify-content: space-between; }
        .topbar-titulo { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--azul-marino); letter-spacing: -1px; }
        .topbar-acciones { display: flex; align-items: center; gap: 14px; }
        .topbar-btn-icono { width: 44px; height: 44px; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.6); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transicion); color: var(--texto-secundario); position: relative; text-decoration: none; }
        .topbar-btn-icono:hover { background: var(--blanco); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); color: var(--azul-acento); }
        .topbar-btn-icono svg { width: 20px; height: 20px; }

        /* CONTENIDO PÁGINA */
        .pagina-contenido { padding: 2rem; }

        /* TARJETAS Y TABLAS GLOBALES */
        .card { background: var(--glass-bg); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid var(--glass-border); border-radius: var(--radio-lg); padding: 2rem; box-shadow: var(--glass-shadow); transition: var(--transicion); }
        .card:hover { transform: translateY(-4px); box-shadow: 0 15px 45px rgba(31, 38, 135, 0.15); border-color: rgba(255,255,255,0.8); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; }
        .card-titulo { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.25rem; color: var(--azul-marino); letter-spacing: -0.5px; }

        /* TABLAS */
        .tabla-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th { padding: 12px 16px; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gris-suave); border-bottom: 2px solid var(--gris-perla); background: var(--gris-claro); }
        tbody td { padding: 14px 16px; font-size: 0.88rem; color: var(--texto-primario); border-bottom: 1px solid rgba(226,232,240,0.6); }
        tbody tr { transition: var(--transicion); }
        tbody tr:hover { background: rgba(59,111,204,0.03); }
        tbody tr:last-child td { border-bottom: none; }

        /* BADGES */
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 8px; font-size: 0.73rem; font-weight: 600; }
        .badge-azul { background: rgba(59,111,204,0.1); color: var(--azul-acento); }
        .badge-verde { background: rgba(16,185,129,0.1); color: var(--exito); }
        .badge-rojo { background: rgba(239,68,68,0.1); color: var(--error); }
        .badge-ambar { background: rgba(245,158,11,0.1); color: var(--alerta); }
        .badge-gris { background: rgba(148,163,184,0.15); color: var(--gris-suave); }

        /* BOTONES */
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: var(--radio-md); font-family: 'DM Sans', sans-serif; font-size: 0.88rem; font-weight: 600; cursor: pointer; transition: var(--transicion); border: none; text-decoration: none; }
        .btn svg { width: 16px; height: 16px; }
        .btn-primario { background: linear-gradient(135deg, var(--azul-acento), var(--azul-celeste)); color: var(--blanco); box-shadow: 0 4px 12px rgba(59,111,204,0.25); }
        .btn-primario:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59,111,204,0.35); }
        .btn-secundario { background: var(--blanco); color: var(--texto-secundario); border: 1px solid var(--gris-perla); }
        .btn-secundario:hover { border-color: var(--azul-acento); color: var(--azul-acento); }
        .btn-peligro { background: rgba(239,68,68,0.1); color: var(--error); }
        .btn-peligro:hover { background: var(--error); color: white; }
        .btn-sm { padding: 6px 12px; font-size: 0.78rem; border-radius: var(--radio-sm); }
        .btn-icono { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid var(--gris-perla); background: var(--blanco); color: var(--texto-secundario); cursor: pointer; transition: var(--transicion); }
        .btn-icono:hover { border-color: var(--azul-acento); color: var(--azul-acento); }

        /* FORMULARIOS */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.75rem; }
        .form-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.75rem; }
        .form-grupo { margin-bottom: 1.75rem; }
        .form-grupo.col-span-2 { grid-column: span 2; }
        .form-grupo.col-span-3 { grid-column: span 3; }
        .form-etiqueta { display: block; font-size: 0.85rem; font-weight: 700; color: var(--azul-marino); margin-bottom: 0.6rem; letter-spacing: 0.2px; }
        .form-control { width: 100%; padding: 14px 18px; background: rgba(255, 255, 255, 0.6); border: 1px solid var(--glass-border); border-radius: 12px; color: var(--texto-primario); font-family: inherit; font-size: 0.95rem; outline: none; transition: var(--transicion); }
        .form-control:focus { background: var(--blanco); border-color: var(--azul-acento); box-shadow: 0 8px 16px rgba(59, 130, 246, 0.15); }
        .form-control::placeholder { color: var(--gris-suave); opacity: 0.7; }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%233b82f6' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 18px center; padding-right: 46px; }
        textarea.form-control { resize: vertical; min-height: 120px; }
        
        /* compact styles */
        .form-compact .form-grupo { margin-bottom: 0.75rem; }
        .form-compact .form-control { padding: 8px 12px; font-size: 0.88rem; border-radius: 8px; }
        .form-compact .form-etiqueta { margin-bottom: 0.3rem; font-size: 0.75rem; }
        .form-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
        .card.card-compact { padding: 1.25rem 1.5rem; }
        .card-compact .card-header { margin-bottom: 1rem; }
        .card-compact .card-titulo { font-size: 1.1rem; }

        /* ALERTAS */
        .alerta { padding: 14px 18px; border-radius: var(--radio-md); display: flex; align-items: center; gap: 12px; margin-bottom: 1.25rem; font-size: 0.88rem; font-weight: 500; }
        .alerta svg { width: 18px; height: 18px; flex-shrink: 0; }
        .alerta-exito { background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); color: var(--exito); }
        .alerta-error { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: var(--error); }
        .alerta-info { background: rgba(59,111,204,0.08); border: 1px solid rgba(59,111,204,0.2); color: var(--azul-acento); }
        .alerta-advertencia { background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); color: var(--alerta); }

        /* MODAL */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(3, 7, 18, 0.4); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 1000; align-items: flex-start; justify-content: center; padding: 2rem 1.5rem; overflow-y: auto; scroll-behavior: smooth; }
        .modal-overlay.activo { display: flex; }
        .modal { background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.8); border-radius: var(--radio-xl); padding: 3rem; width: 100%; max-width: 800px; margin: auto; box-shadow: 0 40px 100px rgba(0,0,0,0.15); animation: modalEntrada 0.5s cubic-bezier(0.16, 1, 0.3, 1); position: relative; }
        .modal-lg { max-width: 1050px; }
        .modal-xl { max-width: 1250px; }
        @keyframes modalEntrada { from { opacity: 0; transform: scale(0.9) translateY(40px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .modal-titulo { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.4rem; color: var(--azul-marino); }
        .modal-cerrar { background: rgba(255,255,255,0.5); border: 1px solid var(--glass-border); color: var(--gris-suave); cursor: pointer; padding: 6px; border-radius: 10px; transition: var(--transicion); display: flex; }
        .modal-cerrar:hover { color: var(--error); background: white; transform: rotate(90deg); }
        .modal-cerrar svg { width: 20px; height: 20px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--glass-border); }

        /* GRID DASHBOARD LAYOUTS */
        .modulo-grid { display: grid; grid-template-columns: 400px 1fr; gap: 2rem; align-items: flex-start; }
        .grid-sidebar { position: sticky; top: 100px; }
        .grid-main { min-width: 0; }
        
        @media (max-width: 1300px) { .modulo-grid { grid-template-columns: 350px 1fr; gap: 1.5rem; } }
        @media (max-width: 1100px) { .modulo-grid { grid-template-columns: 1fr; } .grid-sidebar { position: static; } }

        /* VACÍO */
        .estado-vacio { text-align: center; padding: 3rem 1rem; color: var(--gris-suave); }
        .estado-vacio svg { width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5; }
        .estado-vacio h3 { font-family: 'Outfit', sans-serif; font-weight: 600; color: var(--texto-secundario); margin-bottom: 0.25rem; }
        .estado-vacio p { font-size: 0.85rem; }

        /* RESPONSIVE */
        @media (max-width: 1200px) { .form-grid { grid-template-columns: 1fr; } .form-grid-3 { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .contenido-principal { margin-left: 0; } }

        /* ANIMACIÓN CARGA (Silent Luxury) */
        .loading-bar { position: fixed; top: 0; left: 0; height: 3px; background: linear-gradient(90deg, var(--azul-acento), var(--azul-celeste)); z-index: 1000; width: 0; transition: width 0.4s ease; opacity: 0; }
        .loading-bar.active { opacity: 1; width: 30%; }
        .loading-bar.complete { opacity: 0; width: 100%; transition: width 0.3s ease, opacity 0.3s 0.2s ease; }

        .fade-in { opacity: 0; animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes fadeIn { to { opacity: 1; } }

        /* Estilos Premium para Tom Select (Reemplazo de Select2) */
        .ts-control {
            background: rgba(255, 255, 255, 0.6) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 12px !important;
            padding: 12px 18px !important;
            font-family: 'DM Sans', sans-serif !important;
            font-size: 0.95rem !important;
            color: var(--texto-primario) !important;
            box-shadow: none !important;
            transition: var(--transicion) !important;
            min-height: 48px;
            display: flex;
            align-items: center;
        }
        .ts-wrapper.focus .ts-control {
            background: var(--blanco) !important;
            border-color: var(--azul-acento) !important;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.12) !important;
        }
        .ts-dropdown {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 14px !important;
            box-shadow: 0 15px 40px rgba(0,0,0,0.12) !important;
            margin-top: 8px !important;
            padding: 8px !important;
            z-index: 10001 !important;
        }
        .ts-dropdown .active {
            background: var(--azul-acento) !important;
            color: white !important;
            border-radius: 8px;
        }
        .ts-dropdown .option {
            padding: 10px 14px !important;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }
        .ts-dropdown .option:hover:not(.active) {
            background: var(--azul-hielo) !important;
            color: var(--azul-acento) !important;
        }
        .ts-wrapper.single .ts-control::after {
            border-color: var(--azul-acento) transparent transparent transparent !important;
            right: 18px !important;
        }
        .ts-wrapper.single.input-active .ts-control::after {
            border-color: transparent transparent var(--azul-acento) transparent !important;
        }
        .ts-control input { font-family: inherit !important; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar Lucide Icons
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
            
            // Efecto de carga suave al hacer click en enlaces internos
            const links = document.querySelectorAll('a[href^="index.php"]');
            const loader = document.createElement('div');
            loader.className = 'loading-bar';
            document.body.appendChild(loader);

            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    if (e.ctrlKey || e.shiftKey || e.metaKey || link.target === '_blank') return;
                    loader.classList.add('active');
                });
            });

            // Animación de entrada para el contenido principal
            const mainContent = document.querySelector('.pagina-contenido');
            if (mainContent) mainContent.classList.add('fade-in');
        });

        // =====================================================
        // 6.2 Modal de Confirmación Universal
        // =====================================================
        function confirmarAccion(formId, mensaje) {
            const form = document.getElementById(formId);
            if (!form) return true;
            
            // Validar primero
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            
            document.getElementById('modal-confirmar-msg').textContent = mensaje || '¿Desea guardar los cambios y finalizar la operación?';
            document.getElementById('modal-confirmar-form').value = formId;
            document.getElementById('modal-confirmar-overlay').classList.add('activo');
            return false;
        }

        function ejecutarConfirmacion() {
            const formId = document.getElementById('modal-confirmar-form').value;
            document.getElementById('modal-confirmar-overlay').classList.remove('activo');
            document.getElementById(formId).submit();
        }

        // =====================================================
        // 6.1 ComboBox Dinámico Universal (AJAX)
        // =====================================================
        function dynamicSelect(selectId, endpoint, placeholder) {
            const select = document.getElementById(selectId);
            if (!select) return;
            
            let cache = {};
            let debounceTimer;
            
            const inputHandler = function() {
                clearTimeout(debounceTimer);
                const search = this.value.toLowerCase();
                
                if (search.length < 2) return;
                if (cache[search]) {
                    renderOptions(cache[search]);
                    return;
                }
                
                debounceTimer = setTimeout(() => {
                    fetch('index.php?url=' + endpoint + '&q=' + encodeURIComponent(search))
                        .then(r => r.json())
                        .then(data => {
                            cache[search] = data;
                            renderOptions(data);
                        });
                }, 300);
            };
            
            function renderOptions(data) {
                select.innerHTML = '<option value="">' + (placeholder || 'Seleccione...') + '</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.nombre_generico || item.nombre || item.cedula || item.titulo || JSON.stringify(item);
                    select.appendChild(opt);
                });
            }
            
            select.addEventListener('input', inputHandler);
            select.addEventListener('change', inputHandler);
        }

        // DESACTIVAR ZOOM (Mouse Wheel + Keyboard)
        window.addEventListener('wheel', (e) => {
            if (e.ctrlKey) e.preventDefault();
        }, { passive: false });

        window.addEventListener('keydown', (e) => {
            if (e.ctrlKey && (e.key === '+' || e.key === '-' || e.key === '0' || e.key === '=' || e.keyCode === 187 || e.keyCode === 189 || e.keyCode === 48)) {
                e.preventDefault();
            }
        });
    </script>
</head>
<body>
    <?php
    // Obtener alertas para el punto rojo de la campana
    require_once __DIR__ . '/../../models/Reporte.php';
    $headerReporteModel = new Reporte();
    $totalAlertasHeader = count($headerReporteModel->alertasGlobal());
    ?>
    <nav class="navbar">
        <div class="navbar-left-group">
            <!-- BLOQUE IZQUIERDA: Marca -->
            <a href="index.php?url=dashboard" class="navbar-logo">
                <span class="navbar-logo-texto">SIGFA</span>
            </a>

            <!-- BLOQUE NAVEGACIÓN (Pegado a la izquierda) -->
            <div class="navbar-menu">
                <!-- Gestión de Pacientes -->
                <?php if ($puedeVerPacientes): ?>
                <div class="nav-item">
                    <a class="nav-link" href="#"><i data-lucide="users"></i> Pacientes <i data-lucide="chevron-down" class="nav-arrow"></i></a>
                    <div class="nav-dropdown">
                        <div class="nav-dropdown-title">Gestión de Pacientes</div>
                        <a href="index.php?url=asegurados" class="nav-dropdown-link <?= $paginaActual === 'asegurados' ? 'activo' : '' ?>"><i data-lucide="user-check"></i> Registro de Asegurados</a>
                        <a href="index.php?url=medicos" class="nav-dropdown-link <?= $paginaActual === 'medicos' ? 'activo' : '' ?>"><i data-lucide="stethoscope"></i> Registro de Médicos</a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Inventario y Almacén -->
                <?php if ($puedeEntrada): ?>
                <div class="nav-item">
                    <a class="nav-link" href="#"><i data-lucide="package"></i> Inventario <i data-lucide="chevron-down" class="nav-arrow"></i></a>
                    <div class="nav-dropdown">
                        <div class="nav-dropdown-title">Inventario y Almacén</div>
                        <a href="index.php?url=inventario/entrada" class="nav-dropdown-link <?= $paginaActual === 'inventario' ? 'activo' : '' ?>"><i data-lucide="plus-circle"></i> Entrada de Medicamentos</a>
                        <a href="index.php?url=inventario/kardex" class="nav-dropdown-link <?= $paginaActual === 'kardex' ? 'activo' : '' ?>"><i data-lucide="book-open"></i> Kardex</a>
                        <a href="index.php?url=inventario/alertas" class="nav-dropdown-link <?= $paginaActual === 'alertas' ? 'activo' : '' ?>"><i data-lucide="alert-triangle"></i> Alertas</a>
                        <?php if ($puedeTransferencia): ?>
                        <a href="index.php?url=transferencias" class="nav-dropdown-link <?= $paginaActual === 'transferencias' ? 'activo' : '' ?>"><i data-lucide="arrow-left-right"></i> Transferencias</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Despacho -->
                <?php if ($puedeDespachar): ?>
                <div class="nav-item">
                    <a class="nav-link <?= $paginaActual === 'despachos' ? 'activo' : '' ?>" href="index.php?url=despachos"><i data-lucide="clipboard-list"></i> Despacho</a>
                </div>
                <?php endif; ?>

                <!-- Catálogos -->
                <?php if ($puedeMedicamentos || $puedeProveedores || $esAdmin): ?>
                <div class="nav-item">
                    <a class="nav-link" href="#"><i data-lucide="file-text"></i> Catálogos <i data-lucide="chevron-down" class="nav-arrow"></i></a>
                    <div class="nav-dropdown">
                        <div class="nav-dropdown-title">Maestros y Catálogos</div>
                        <?php if ($puedeMedicamentos): ?>
                        <a href="index.php?url=medicamentos" class="nav-dropdown-link <?= $paginaActual === 'medicamentos' ? 'activo' : '' ?>"><i data-lucide="tablets"></i> Medicamentos</a>
                        <?php endif; ?>
                        <?php if ($esAdmin): ?>
                        <a href="index.php?url=grupos" class="nav-dropdown-link <?= $paginaActual === 'grupos' ? 'activo' : '' ?>"><i data-lucide="layers"></i> Grupos Terapéuticos</a>
                        <?php endif; ?>
                        <?php if ($puedeProveedores): ?>
                        <a href="index.php?url=proveedores" class="nav-dropdown-link <?= $paginaActual === 'proveedores' ? 'activo' : '' ?>"><i data-lucide="truck"></i> Proveedores</a>
                        <?php endif; ?>
                        <?php if ($esAdmin): ?>
                        <a href="index.php?url=almacenes" class="nav-dropdown-link <?= $paginaActual === 'almacenes' ? 'activo' : '' ?>"><i data-lucide="warehouse"></i> Almacenes</a>
                        <?php endif; ?>
                        <?php if ($puedeVerPacientes): ?>
                        <a href="index.php?url=servicios" class="nav-dropdown-link <?= $paginaActual === 'servicios' ? 'activo' : '' ?>"><i data-lucide="building"></i> Servicios Médicos</a>
                        <a href="index.php?url=patologias" class="nav-dropdown-link <?= $paginaActual === 'patologias' ? 'activo' : '' ?>"><i data-lucide="heart-pulse"></i> Patologías</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Operaciones Especiales -->
                <?php if ($puedeDevolucion || $puedeAjustar): ?>
                <div class="nav-item">
                    <a class="nav-link" href="#"><i data-lucide="sliders"></i> Oper. Esp. <i data-lucide="chevron-down" class="nav-arrow"></i></a>
                    <div class="nav-dropdown">
                        <div class="nav-dropdown-title">Operaciones Especiales</div>
                        <?php if ($puedeDevolucion): ?>
                        <a href="index.php?url=devoluciones" class="nav-dropdown-link <?= $paginaActual === 'devoluciones' ? 'activo' : '' ?>"><i data-lucide="corner-down-left"></i> Devoluciones</a>
                        <?php endif; ?>
                        <?php if ($puedeAjustar): ?>
                        <a href="index.php?url=inventario/ajuste" class="nav-dropdown-link <?= $paginaActual === 'ajuste' ? 'activo' : '' ?>"><i data-lucide="sliders"></i> Ajuste de Inventario</a>
                        <a href="index.php?url=despachos/anular" class="nav-dropdown-link <?= $paginaActual === 'anular' ? 'activo' : '' ?>"><i data-lucide="x-circle"></i> Anulación de Transacciones</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Reportes -->
                <?php if ($puedeReportes): ?>
                <div class="nav-item">
                    <a class="nav-link" href="#"><i data-lucide="bar-chart-3"></i> Reportes <i data-lucide="chevron-down" class="nav-arrow"></i></a>
                    <div class="nav-dropdown">
                        <div class="nav-dropdown-title">Central de Reportes</div>
                        <a href="index.php?url=reportes/auditoria" class="nav-dropdown-link"><i data-lucide="shield-check"></i> Auditoría</a>
                        <a href="index.php?url=reportes/alertas" class="nav-dropdown-link"><i data-lucide="alert-triangle"></i> Alertas de Calidad</a>
                        <a href="index.php?url=reportes/servicio" class="nav-dropdown-link"><i data-lucide="building-2"></i> Por Servicio</a>
                        <a href="index.php?url=reportes/medicamento" class="nav-dropdown-link"><i data-lucide="pill"></i> Por Medicamento</a>
                        <a href="index.php?url=reportes/periodo" class="nav-dropdown-link"><i data-lucide="calendar-range"></i> Por Período</a>
                        <a href="index.php?url=reportes/consumo" class="nav-dropdown-link"><i data-lucide="coins"></i> Consumo en Bs</a>
                        <a href="index.php?url=reportes/inventario" class="nav-dropdown-link"><i data-lucide="package-check"></i> Inventario Valorizado</a>
                        <a href="index.php?url=reportes/kardex" class="nav-dropdown-link"><i data-lucide="book-open"></i> Kardex Completo</a>
                        <a href="index.php?url=reportes/patologia" class="nav-dropdown-link"><i data-lucide="heart-pulse"></i> Por Patología</a>
                        <a href="index.php?url=reportes/recetas" class="nav-dropdown-link"><i data-lucide="clipboard-list"></i> Recetas Diarias</a>
                        <a href="index.php?url=reportes/consumo_masivo" class="nav-dropdown-link"><i data-lucide="trending-up"></i> Consumo Masivo</a>
                        <a href="index.php?url=reportes/costo_promedio" class="nav-dropdown-link"><i data-lucide="dollar-sign"></i> Costo Promedio</a>
                        <?php if ($puedeVerPacientes): ?>
                        <a href="index.php?url=reportes/paciente" class="nav-dropdown-link"><i data-lucide="user-search"></i> Prescripción por Paciente</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Sistema (Sin Logout) -->
                <?php if ($puedeUsuarios): ?>
                <div class="nav-item">
                    <a class="nav-link" href="#"><i data-lucide="settings"></i> Sistema <i data-lucide="chevron-down" class="nav-arrow"></i></a>
                    <div class="nav-dropdown">
                        <div class="nav-dropdown-title">Administración</div>
                        <a href="index.php?url=usuarios" class="nav-dropdown-link <?= $paginaActual === 'usuarios' ? 'activo' : '' ?>"><i data-lucide="users"></i> Usuarios</a>
                        <a href="index.php?url=backup" class="nav-dropdown-link"><i data-lucide="database-backup"></i> Backup de Datos</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BLOQUE DERECHA: Perfil -->
        <div class="navbar-right">
            <a href="index.php?url=inventario/alertas" class="navbar-notification" title="Alertas Críticas">
                <i data-lucide="bell"></i>
                <?php if ($totalAlertasHeader > 0): ?>
                    <span class="notification-dot"></span>
                <?php endif; ?>
            </a>

            <div class="navbar-user">
                <div class="user-avatar"><?= strtoupper(substr($nombreUsuario, 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($nombreUsuario) ?></div>
                    <div class="user-role"><?= htmlspecialchars($rolUsuario) ?></div>
                </div>
            </div>

            <!-- BOTÓN SALIR REDISEÑADO -->
            <a href="index.php?url=logout" class="btn-logout" title="Cerrar Sesión">
                <i data-lucide="log-out"></i>
            </a>
        </div>

    </nav>
    <main class="contenido-principal">
        <section class="pagina-contenido">
