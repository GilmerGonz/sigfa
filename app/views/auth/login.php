<?php
/**
 * =====================================================
 * SIGFA - Vista: Login (REDESIGN PREMIUM)
 * =====================================================
 * Pantalla de inicio de sesión con estética Glassmorphism Premium
 * Inspirado en interfaces administrativas de alto nivel.
 * =====================================================
 */

$csrf_token = AuthController::generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="SIGFA - Sistema de Gestión Farmacéutica. Acceso Seguro.">
    <title>SIGFA | Acceso Premium</title>

    <!-- Google Fonts: Outfit + DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --bg-deep: #030712;
            --azul-acento: #3b82f6;
            --azul-brillante: #60a5fa;
            --texto-puro: #f8fafc;
            --texto-tenue: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shine: rgba(255, 255, 255, 0.12);
            --error-red: #ef4444;
            --transicion: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html { zoom: 75%; }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg-deep);
            color: var(--texto-puro);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* =====================================================
         * ANIMACIÓN DE FONDO (MESH & WAVES) - MEJORADA
         * ===================================================== */
        .fondo-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: #020617;
            overflow: hidden;
        }

        /* Gradiente de fondo animado */
        .fondo-canvas::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(30, 58, 138, 0.4) 0%, transparent 80%);
            opacity: 0.5;
            transition: background 0.5s ease;
        }

        .wave-container {
            position: absolute;
            inset: 0;
            filter: blur(60px);
            opacity: 0.4;
            mix-blend-mode: screen;
        }

        .wave {
            position: absolute;
            border-radius: 50%;
            animation: move-organic 30s infinite alternate ease-in-out;
        }

        .wave-1 {
            width: 1200px; height: 1200px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, transparent 60%);
            top: -30%; left: -20%;
            animation-duration: 40s;
        }

        .wave-2 {
            width: 900px; height: 900px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, transparent 60%);
            bottom: -20%; right: -10%;
            animation-duration: 55s;
            animation-delay: -5s;
        }

        .wave-3 {
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.12) 0%, transparent 60%);
            top: 20%; right: 10%;
            animation-duration: 45s;
            animation-delay: -10s;
        }

        @keyframes move-organic {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(50px, -30px) scale(1.1) rotate(5deg); }
            66% { transform: translate(-30px, 40px) scale(0.9) rotate(-5deg); }
            100% { transform: translate(0, 0) scale(1) rotate(0deg); }
        }

        /* Líneas de malla sutiles con pulso */
        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: grid-pulse 10s infinite alternate ease-in-out;
        }

        @keyframes grid-pulse {
            0% { opacity: 0.3; transform: scale(1); }
            100% { opacity: 0.6; transform: scale(1.05); }
        }

        /* =====================================================
         * CONTENIDO PRINCIPAL (GLASS CARD)
         * ===================================================== */
        .main-container {
            position: relative;
            z-index: 10;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
        }

        .glass-card {
            width: 100%;
            max-width: 480px;
            background: var(--glass-bg);
            backdrop-filter: blur(30px) saturate(180%);
            -webkit-backdrop-filter: blur(30px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 3.5rem 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slide-up 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            transition: var(--transicion);
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        /* ANIMACIÓN DE LUZ PARA EL TAG DE CRÉDITOS */
        .pill-creditos {
            background: linear-gradient(135deg, rgba(226, 232, 240, 0.9) 0%, rgba(148, 163, 184, 0.9) 100%);
            color: #0f172a;
            padding: 8px 24px;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.5);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }

        .pill-creditos::after {
            content: "";
            position: absolute;
            top: 0;
            left: -150%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                110deg,
                transparent 40%,
                rgba(255, 255, 255, 0.25) 50%,
                transparent 60%
            );
            animation: light-swipe 6s infinite ease-in-out alternate;
        }

        @keyframes light-swipe {
            0% { transform: translateX(50%); }
            100% { transform: translateX(250%); }
        }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Brillo superior */
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: 50%; transform: translateX(-50%);
            width: 80%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        }

        /* Header del Logo mas minimalista */
        .brand {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 2.4rem;
            letter-spacing: -2px;
            background: linear-gradient(135deg, #fff 0%, var(--azul-brillante) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-desc {
            font-size: 0.95rem;
            color: var(--texto-tenue);
            margin-top: 0.5rem;
            line-height: 1.5;
            max-width: 320px;
            margin-left: auto;
            margin-right: auto;
        }

        /* =====================================================
         * CAPACIDADES DEL SISTEMA (Métricas sutiles)
         * ===================================================== */
        .capabilities {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .cap-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .cap-value {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--azul-brillante);
            letter-spacing: 0.5px;
        }

        .cap-label {
            font-size: 0.65rem;
            font-weight: 500;
            color: var(--texto-tenue);
            text-transform: uppercase;
        }

        /* =====================================================
         * CAMPOS DE ENTRADA
         * ===================================================== */
        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.4);
            margin-bottom: 0.6rem;
            padding-left: 4px;
        }

        .input-field {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--texto-tenue);
            pointer-events: none;
            transition: var(--transicion);
        }

        .input-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 16px 16px 16px 52px;
            color: white;
            font-size: 0.95rem;
            outline: none;
            transition: var(--transicion);
        }

        .input-control:focus {
            background: rgba(255, 255, 255, 0.07);
            border-color: var(--azul-acento);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .input-control:focus ~ .input-icon {
            color: var(--azul-brillante);
        }

        .pwd-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--texto-tenue);
            cursor: pointer;
            transition: var(--transicion);
        }

        .pwd-toggle:hover { color: white; }

        /* =====================================================
         * BOTÓN PRINCIPAL
         * ===================================================== */
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border: none;
            border-radius: 14px;
            padding: 18px;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transicion);
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.5);
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
        }

        .submit-btn:active { transform: translateY(0); }

        /* =====================================================
         * ALERTAS
         * ===================================================== */
        .alert-box {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--error-red);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .alert-text {
            font-size: 0.85rem;
            color: #fca5a5;
            font-weight: 500;
        }

        /* =====================================================
         * FOOTER
         * ===================================================== */
        .footer-info {
            margin-top: 3rem;
            text-align: center;
        }

        .hosp-name {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--texto-tenue);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .copy-text {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.2);
            margin-top: 0.5rem;
        }

        /* Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .loading .btn-text { display: none; }
        .loading .spinner { display: block; }

        @media (max-width: 480px) {
            .glass-card { padding: 2.5rem 1.5rem; }
            .logo-text { font-size: 2rem; }
        }
    </style>
</head>
<body>

    <div class="fondo-canvas">
        <div class="grid-overlay"></div>
        <div class="wave-container">
            <div class="wave wave-1"></div>
            <div class="wave wave-2"></div>
            <div class="wave wave-3"></div>
        </div>
    </div>

    <div class="main-container">
        <div class="glass-card">
            <header class="brand">
                <div class="logo-box">
                    <h1 class="logo-text">SIGFA</h1>
                </div>
                <p class="brand-desc">Plataforma integral para la administración, control de inventario y despacho del Hospital Dr. Juan Daza Pereyra.</p>
            </header>

            <div class="capabilities">
                <div class="cap-item">
                    <span class="cap-value">FIFO</span>
                    <span class="cap-label">Lotes</span>
                </div>
                <div class="cap-item">
                    <span class="cap-value">KARDEX</span>
                    <span class="cap-label">Auditoría</span>
                </div>
                <div class="cap-item">
                    <span class="cap-value">ALERTAS</span>
                    <span class="cap-label">Control</span>
                </div>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert-box">
                <i data-lucide="alert-circle" style="color: var(--error-red); width: 20px;"></i>
                <span class="alert-text"><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="index.php?url=login" id="login-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="input-group">
                    <label class="input-label" for="cedula">Cédula de Identidad</label>
                    <div class="input-field">
                        <i data-lucide="user" class="input-icon"></i>
                        <input type="text" id="cedula" name="cedula" class="input-control" placeholder="00000001" required autocomplete="username">
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label" for="clave">Contraseña</label>
                    <div class="input-field">
                        <i data-lucide="lock" class="input-icon"></i>
                        <input type="password" id="clave" name="clave" class="input-control" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="pwd-toggle" id="toggle-pwd">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="submit-btn">
                    <span class="btn-text">Acceder al Sistema</span>
                    <span class="spinner"></span>
                </button>
            </form>

            <footer style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); text-align: center;">
                <p style="font-size: 0.7rem; color: var(--texto-tenue); font-weight: 600; letter-spacing: 0.5px;">PROYECTO PNFI — UPTAEB &bull; 2026</p>
            </footer>
        </div>

        <div class="footer-info">
            <p class="hosp-name">Hospital Dr. Juan Daza Pereyra</p>
            <p class="copy-text">SIGFA v1.0 — Sistema de Gestión Farmacéutica</p>
            <div style="margin-top: 18px; display: inline-block;">
                <span class="pill-creditos">
                    <i data-lucide="code-2" style="width: 14px; height: 14px; opacity: 0.7;"></i>
                    Desarrollado por Gilmer González, Alirio Colmenarez y Sandy Oviedo
                </span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar Lucide
            lucide.createIcons();

            const loginForm = document.getElementById('login-form');
            const submitBtn = document.getElementById('submit-btn');
            const togglePwd = document.getElementById('toggle-pwd');
            const claveInput = document.getElementById('clave');

            // Toggle contraseña
            togglePwd.addEventListener('click', () => {
                const type = claveInput.getAttribute('type') === 'password' ? 'text' : 'password';
                claveInput.setAttribute('type', type);
                
                // Actualizar icono
                const icon = type === 'password' ? 'eye' : 'eye-off';
                togglePwd.innerHTML = `<i data-lucide="${icon}"></i>`;
                lucide.createIcons();
            });

            // Efecto de carga
            loginForm.addEventListener('submit', () => {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // --- MEJORA: Interacción Dinámica del Fondo ---
            document.addEventListener('mousemove', (e) => {
                const x = (e.clientX / window.innerWidth) * 100;
                const y = (e.clientY / window.innerHeight) * 100;
                document.documentElement.style.setProperty('--mouse-x', `${x}%`);
                document.documentElement.style.setProperty('--mouse-y', `${y}%`);
            });

            // DESACTIVAR ZOOM (Mouse Wheel + Keyboard)
            window.addEventListener('wheel', (e) => {
                if (e.ctrlKey) e.preventDefault();
            }, { passive: false });

            window.addEventListener('keydown', (e) => {
                if (e.ctrlKey && (e.key === '+' || e.key === '-' || e.key === '0' || e.key === '=' || e.keyCode === 187 || e.keyCode === 189 || e.keyCode === 48)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
