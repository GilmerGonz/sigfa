<?php
/**
 * Script de Backup Automático Semanal
 * Debe ejecutarse mediante cron job cada domingo a las 00:00
 * 
 * Configuración cron (Linux):
 * 0 0 * * 0 /usr/bin/php /path/to/app/cron/backup_semanal.php
 * 
 * En Windows (Programador de tareas):
 * Ejecutar: php.exe C:\path\to\app\cron\backup_semanal.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Backup.php';

echo "Iniciando backup automático semanal...\n";

try {
    $backup = new Backup();
    $resultado = $backup->generarBackup();
    
    if ($resultado['success']) {
        echo "✅ Backup creado exitosamente: {$resultado['filename']}\n";
        echo "Tamaño: " . number_format($resultado['tamano']/1024, 2) . " KB\n";
        
        // Mantener solo los últimos 4 backups (1 mes)
        $backups = $backup->listarBackups();
        if (count($backups) > 4) {
            for ($i = 4; $i < count($backups); $i++) {
                $backup->eliminarBackup($backups[$i]['nombre']);
                echo "🗑️ Backup antiguo eliminado: {$backups[$i]['nombre']}\n";
            }
        }
    } else {
        echo "❌ Error: {$resultado['error']}\n";
    }
} catch (Exception $e) {
    echo "❌ Excepción: {$e->getMessage()}\n";
}

echo "Proceso completado.\n";