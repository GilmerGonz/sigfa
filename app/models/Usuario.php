<?php
/**
 * =====================================================
 * SIGFA - Modelo: Usuario
 * =====================================================
 * Gestiona la lógica de datos para la tabla usuarios.
 * =====================================================
 */

require_once __DIR__ . '/../../config/db.php';

class Usuario
{
    private Conexion $db;

    public function __construct()
    {
        $this->db = Conexion::obtenerInstancia();
    }

    /**
     * Buscar un usuario por su cédula.
     */
    public function buscarPorCedula(string $cedula): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM usuarios WHERE cedula = :cedula AND activo = 1 LIMIT 1",
            ['cedula' => $cedula]
        );
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Buscar un usuario por su ID.
     */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT id, cedula, nombre, apellido, correo, telefono, rol, activo, ultimo_acceso FROM usuarios WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Verificar credenciales del usuario para login.
     * Retorna un arreglo con 'status' y 'usuario' (si aplica).
     */
    public function verificarCredenciales(string $cedula, string $clave): array
    {
        // 1. Verificar si existen usuarios (si no, la DB está vacía)
        $stmtCount = $this->db->ejecutar("SELECT COUNT(*) FROM usuarios");
        if ($stmtCount->fetchColumn() == 0) {
            return ['status' => 'DB_VACIA', 'usuario' => null];
        }

        // 2. Buscar por cédula (incluyendo inactivos para dar mejor feedback)
        $stmt = $this->db->ejecutar(
            "SELECT * FROM usuarios WHERE cedula = :cedula LIMIT 1",
            ['cedula' => $cedula]
        );
        $usuario = $stmt->fetch();

        if (!$usuario) {
            return ['status' => 'NO_EXISTE', 'usuario' => null];
        }

        // 3. Verificar estado activo
        if ($usuario['activo'] != 1) {
            return ['status' => 'INACTIVO', 'usuario' => null];
        }

        // 4. Verificar contraseña
        if (password_verify($clave, $usuario['clave'])) {
            $this->actualizarUltimoAcceso($usuario['id']);
            unset($usuario['clave']);
            return ['status' => 'EXITO', 'usuario' => $usuario];
        }

        return ['status' => 'CLAVE_INCORRECTA', 'usuario' => null];
    }

    /**
     * Actualizar la fecha del último acceso.
     */
    public function actualizarUltimoAcceso(int $id): void
    {
        $this->db->ejecutar(
            "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Listar todos los usuarios activos.
     */
    public function listarActivos(): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT id, cedula, nombre, apellido, correo, rol, ultimo_acceso 
             FROM usuarios WHERE activo = 1 ORDER BY nombre ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Crear un nuevo usuario.
     */
    public function crear(array $datos): int
    {
        $this->db->ejecutar(
            "INSERT INTO usuarios (cedula, nombre, apellido, correo, telefono, clave, rol) 
             VALUES (:cedula, :nombre, :apellido, :correo, :telefono, :clave, :rol)",
            [
                'cedula'   => $datos['cedula'],
                'nombre'   => $datos['nombre'],
                'apellido' => $datos['apellido'],
                'correo'   => $datos['correo'],
                'telefono' => $datos['telefono'] ?? null,
                'clave'    => password_hash($datos['clave'], PASSWORD_DEFAULT),
                'rol'      => $datos['rol'] ?? 'Auxiliar_General',
            ]
        );
        return (int) $this->db->ultimoId();
    }
}
