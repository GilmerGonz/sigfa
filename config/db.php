<?php
/**
 * =====================================================
 * SIGFA - Sistema de Gestión Farmacéutica
 * Hospital Dr. Juan Daza Pereira
 * =====================================================
 * Archivo: config/db.php
 * Descripción: Conexión PDO a la base de datos MySQL/MariaDB
 * Proyecto Académico PNFI - UPTAEB
 * =====================================================
 */

// Constantes de configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'sigfa_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Clase Conexion
 * Implementa el patrón Singleton para gestionar la conexión PDO.
 */
class Conexion
{
    private static ?Conexion $instancia = null;
    private ?PDO $pdo = null;

    /**
     * Constructor privado: establece la conexión PDO.
     */
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            // Error amigable para el usuario final pero informativo para desarrollo
            $error_msg = "Error de conexión SIGFA. Verifique que MySQL esté iniciado en XAMPP.";
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                $error_msg = "Error: La base de datos '" . DB_NAME . "' no existe. Por favor impórtela en phpMyAdmin.";
            }
            error_log('Error de conexión SIGFA: ' . $e->getMessage());
            die("<div style='font-family:sans-serif; padding:40px; background:#fff5f5; color:#c53030; border:1px solid #feb2b2; border-radius:8px; margin:20px;'>
                 <h2 style='margin-top:0;'>⚠️ Problema de Conexión</h2>
                 <p>$error_msg</p>
                 <small style='color:#742a2a;'>Detalle técnico: " . $e->getMessage() . "</small>
                 </div>");
        }
    }

    /**
     * Evitar la clonación de la instancia.
     */
    private function __clone() {}

    /**
     * Evitar la deserialización de la instancia.
     */
    public function __wakeup()
    {
        throw new \Exception('No se permite deserializar una instancia Singleton.');
    }

    /**
     * Obtener la instancia única de Conexion (Singleton).
     */
    public static function obtenerInstancia(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Obtener el objeto PDO para ejecutar consultas.
     */
    public function obtenerPDO(): PDO
    {
        return $this->pdo;
    }

    /**
     * Método de conveniencia: preparar y ejecutar una consulta.
     * 
     * @param string $sql     Consulta SQL con marcadores de posición.
     * @param array  $params  Parámetros para la consulta preparada.
     * @return PDOStatement
     */
    public function ejecutar(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Iniciar una transacción.
     */
    public function iniciarTransaccion(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirmar una transacción.
     */
    public function confirmar(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Revertir una transacción.
     */
    public function revertir(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Obtener el último ID insertado.
     */
    public function ultimoId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
