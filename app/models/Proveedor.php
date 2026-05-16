<?php
/** SIGFA - Modelo: Proveedor */
require_once __DIR__ . '/../../config/db.php';

class Proveedor
{
    private Conexion $db;
    public function __construct() { $this->db = Conexion::obtenerInstancia(); }

    public function listarActivos(): array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM proveedores WHERE activo = 1 ORDER BY razon_social ASC");
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM proveedores WHERE id = :id", ['id' => $id]);
        $p = $stmt->fetch();
        return $p ?: null;
    }

    /**
     * Buscar proveedor por RIF.
     */
    public function buscarPorRIF(string $rif): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM proveedores WHERE rif = :rif AND activo = 1",
            ['rif' => $rif]
        );
        $p = $stmt->fetch();
        return $p ?: null;
    }

    public function crear(array $datos): int
    {
        $this->db->ejecutar(
            "INSERT INTO proveedores (rif, razon_social, direccion, telefono, correo, contacto_nombre, activo)
             VALUES (:rif, :razon_social, :direccion, :telefono, :correo, :contacto_nombre, 1)",
            [
                'rif'             => $datos['rif'],
                'razon_social'    => $datos['razon_social'],
                'direccion'       => $datos['direccion'] ?? null,
                'telefono'        => $datos['telefono'] ?? null,
                'correo'          => $datos['correo'] ?? null,
                'contacto_nombre' => $datos['contacto_nombre'] ?? null,
            ]
        );
        return (int) $this->db->ultimoId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->ejecutar(
            "UPDATE proveedores 
             SET rif = :rif, razon_social = :razon_social, direccion = :direccion, 
                 telefono = :telefono, correo = :correo, contacto_nombre = :contacto_nombre,
                 activo = :activo
             WHERE id = :id",
            [
                'id'              => $id,
                'rif'             => $datos['rif'],
                'razon_social'    => $datos['razon_social'],
                'direccion'       => $datos['direccion'] ?? null,
                'telefono'        => $datos['telefono'] ?? null,
                'correo'          => $datos['correo'] ?? null,
                'contacto_nombre' => $datos['contacto_nombre'] ?? null,
                'activo'          => $datos['activo'] ?? 1
            ]
        );
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        // Eliminación lógica
        $stmt = $this->db->ejecutar("UPDATE proveedores SET activo = 0 WHERE id = :id", ['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
