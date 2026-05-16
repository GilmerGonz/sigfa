<?php
/**
 * =====================================================
 * SIGFA - Modelo: Médico
 * =====================================================
 * Gestiona los médicos con código MPPS obligatorio.
 * =====================================================
 */

require_once __DIR__ . '/../../config/db.php';

class Medico
{
    private Conexion $db;

    public function __construct()
    {
        $this->db = Conexion::obtenerInstancia();
    }

    /**
     * Buscar médico por ID.
     */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM medicos WHERE id = :id AND activo = 1",
            ['id' => $id]
        );
        $medico = $stmt->fetch();
        return $medico ?: null;
    }

    /**
     * Buscar médico por código MPPS.
     */
    public function buscarPorMPPS(string $codigoMpps): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM medicos WHERE codigo_mpps = :mpps AND activo = 1",
            ['mpps' => $codigoMpps]
        );
        $medico = $stmt->fetch();
        return $medico ?: null;
    }

    /**
     * Buscar médico por cédula.
     */
    public function buscarPorCedula(string $cedula): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM medicos WHERE cedula = :cedula AND activo = 1",
            ['cedula' => $cedula]
        );
        $medico = $stmt->fetch();
        return $medico ?: null;
    }

    /**
     * Listar todos los médicos activos.
     */
    public function listarActivos(): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT id, cedula, nombre, apellido, codigo_mpps, especialidad, telefono, correo
             FROM medicos 
             WHERE activo = 1 
             ORDER BY apellido ASC, nombre ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Crear un nuevo médico. El código MPPS es obligatorio.
     *
     * @throws \InvalidArgumentException Si no se proporciona el código MPPS.
     */
    public function crear(array $datos): int
    {
        if (empty($datos['codigo_mpps'])) {
            throw new \InvalidArgumentException('El código MPPS es obligatorio para registrar un médico.');
        }

        $this->db->ejecutar(
            "INSERT INTO medicos (cedula, nombre, apellido, codigo_mpps, especialidad, telefono, correo)
             VALUES (:cedula, :nombre, :apellido, :codigo_mpps, :especialidad, :telefono, :correo)",
            [
                'cedula'       => $datos['cedula'],
                'nombre'       => $datos['nombre'],
                'apellido'     => $datos['apellido'],
                'codigo_mpps'  => $datos['codigo_mpps'],
                'especialidad' => $datos['especialidad'] ?? null,
                'telefono'     => $datos['telefono'] ?? null,
                'correo'       => $datos['correo'] ?? null,
            ]
        );
        return (int) $this->db->ultimoId();
    }

    /**
     * Actualizar un médico existente.
     */
    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $params = ['id' => $id];

        foreach (['nombre', 'apellido', 'codigo_mpps', 'especialidad', 'telefono', 'correo'] as $campo) {
            if (array_key_exists($campo, $datos)) {
                $campos[] = "$campo = :$campo";
                $params[$campo] = $datos[$campo];
            }
        }

        if (empty($campos)) return false;

        $sql = "UPDATE medicos SET " . implode(', ', $campos) . " WHERE id = :id";
        $this->db->ejecutar($sql, $params);
        return true;
    }

    /**
     * Buscar médicos por nombre o código MPPS (búsqueda parcial).
     */
    public function buscar(string $termino): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM medicos 
             WHERE activo = 1 
               AND (nombre LIKE :t1 OR apellido LIKE :t2 OR codigo_mpps LIKE :t3 OR cedula LIKE :t4)
             ORDER BY apellido ASC",
            ['t1' => "%$termino%", 't2' => "%$termino%", 't3' => "%$termino%", 't4' => "%$termino%"]
        );
        return $stmt->fetchAll();
    }
}
