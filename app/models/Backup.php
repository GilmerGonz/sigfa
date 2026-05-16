<?php
require_once __DIR__ . '/../../config/db.php';

class Backup
{
    private $pdo;
    private $backupDir;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->obtenerPDO();
        $this->backupDir = dirname(__DIR__, 2) . '/backups';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function generarBackup(): array
    {
        $fecha = date('Y-m-d_H-i-s');
        $filename = "sigfa_backup_{$fecha}.sql";
        $filepath = $this->backupDir . '/' . $filename;

        try {
            $sql = "-- SIGFA Backup {$fecha}\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            $tablas = [
                'usuarios', 'asegurados', 'medicos', 'medicamentos', 
                'proveedores', 'almacenes', 'servicios_medicos', 
                'patologias', 'lotes_inventario', 'despachos', 
                'despacho_detalle', 'transferencias', 'transferencia_detalle',
                'kardex', 'auditoria_sistema', 'alertas'
            ];

            foreach ($tablas as $tabla) {
                $stmt = $this->pdo->query("SELECT * FROM {$tabla}");
                $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                if (!empty($registros)) {
                    $sql .= "-- Tabla: {$tabla}\n";
                    foreach ($registros as $fila) {
                        $columnas = array_keys($fila);
                        $valores = array_map(function($v) {
                            return $v === null ? 'NULL' : "'" . addslashes($v) . "'";
                        }, array_values($fila));
                        $sql .= "INSERT INTO {$tabla} (" . implode(', ', $columnas) . ") VALUES (" . implode(', ', $valores) . ");\n";
                    }
                    $sql .= "\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

            file_put_contents($filepath, $sql);

            $tamano = filesize($filepath);
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'tamano' => $tamano
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function listarBackups(): array
    {
        $backups = [];
        if (is_dir($this->backupDir)) {
            $archivos = glob($this->backupDir . '/sigfa_backup_*.sql');
            foreach ($archivos as $archivo) {
                $backups[] = [
                    'nombre' => basename($archivo),
                    'ruta' => $archivo,
                    'tamano' => filesize($archivo),
                    'fecha' => filemtime($archivo)
                ];
            }
        }
        usort($backups, function($a, $b) {
            return $b['fecha'] - $a['fecha'];
        });
        return $backups;
    }

    public function eliminarBackup(string $nombre): bool
    {
        $ruta = $this->backupDir . '/' . $nombre;
        if (file_exists($ruta)) {
            return unlink($ruta);
        }
        return false;
    }

    public static function ejecutarBackupProgramado(): void
    {
        $backup = new self();
        $backup->generarBackup();
    }
}