<?php
/**
 * SIGFA - Herramienta de Diagnóstico Local
 * Ayuda a identificar problemas de configuración.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>SIGFA Debug</title>";
echo "<link href='https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap' rel='stylesheet'>";
echo "<style>body{font-family:Outfit,sans-serif;background:#f8fafc;padding:40px;color:#1e293b;} .card{background:white;padding:24px;border-radius:16px;box-shadow:0 4px 6px -1px rgb(0 0 0 / 0.1);max-width:800px;margin:auto;} h1{color:#002466;margin-bottom:20px;} .step{display:flex;align-items:center;padding:12px;border-bottom:1px solid #e2e8f0;} .status{width:20px;height:20px;border-radius:50%;margin-right:12px;} .ok{background:#22c55e;} .fail{background:#ef4444;} .info{color:#64748b;font-size:0.9rem;margin-left:auto;}</style></head><body>";

echo "<div class='card'>";
echo "<h1>🛠️ Diagnóstico de SIGFA</h1>";

// 1. Verificar PHP
$phpVersion = PHP_VERSION;
$phpOk = version_compare($phpVersion, '8.0.0', '>=');
echo "<div class='step'><div class='status ".($phpOk ? 'ok' : 'fail')."'></div><b>Versión PHP:</b> <span class='info'>$phpVersion</span></div>";

// 2. Verificar Carpeta del Proyecto
$rootPath = dirname(__DIR__);
$isHtdocs = (strpos($rootPath, 'htdocs') !== false);
echo "<div class='step'><div class='status ".($isHtdocs ? 'ok' : 'fail')."'></div><b>Ubicación del Proyecto:</b> <span class='info'>$rootPath</span></div>";
if (!$isHtdocs) {
    echo "<p style='color:#ef4444; font-size:0.85rem; margin-top:5px;'>⚠️ ¡Error! El proyecto DEBE estar dentro de C:\\xampp\\htdocs\\ para funcionar con localhost.</p>";
}

// 3. Verificar Archivo de Configuración
$configFile = $rootPath . '/config/db.php';
$configExists = file_exists($configFile);
echo "<div class='step'><div class='status ".($configExists ? 'ok' : 'fail')."'></div><b>Archivo db.php:</b> <span class='info'>".($configExists ? 'Encontrado' : 'No encontrado')."</span></div>";

// 4. Probar Conexión a Base de Datos
if ($configExists) {
    require_once $configFile;
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "<div class='step'><div class='status ok'></div><b>Base de Datos:</b> <span class='info'>Conectado a ".DB_NAME."</span></div>";
    } catch (PDOException $e) {
        echo "<div class='step'><div class='status fail'></div><b>Base de Datos:</b> <span class='info'>Error: ".$e->getMessage()."</span></div>";
    }
}

echo "<div style='margin-top:30px; border-top:2px solid #e2e8f0; padding-top:20px;'>";
echo "<p>Si todos están en <b>Verde</b>, puedes entrar aquí: <a href='index.php' style='color:#3b6fcc; font-weight:700;'>IR AL LOGIN</a></p>";
echo "</div></div></body></html>";
