<?php
require_once __DIR__ . '/../../config/db.php';

class Almacen
{
    private Conexion $db;

    public function __construct()
    {
        $this->db = Conexion::obtenerInstancia();
    }

    public function listar(): array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM almacenes ORDER BY codigo ASC");
        return $stmt->fetchAll();
    }

    public function listarActivos(): array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM almacenes WHERE activo = 1 ORDER BY codigo ASC");
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM almacenes WHERE id = :id LIMIT 1", ['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM almacenes WHERE codigo = :codigo LIMIT 1", ['codigo' => $codigo]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): int
    {
        $this->db->ejecutar(
            "INSERT INTO almacenes (codigo, nombre, tipo, ubicacion) VALUES (:codigo, :nombre, :tipo, :ubicacion)",
            [
                'codigo' => strtoupper($datos['codigo']),
                'nombre' => $datos['nombre'],
                'tipo' => $datos['tipo'] ?? 'General',
                'ubicacion' => $datos['ubicacion'] ?? null
            ]
        );
        return (int) $this->db->ultimoId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->db->ejecutar(
            "UPDATE almacenes SET codigo = :codigo, nombre = :nombre, tipo = :tipo, ubicacion = :ubicacion WHERE id = :id",
            [
                'codigo' => strtoupper($datos['codigo']),
                'nombre' => $datos['nombre'],
                'tipo' => $datos['tipo'] ?? 'General',
                'ubicacion' => $datos['ubicacion'] ?? null,
                'id' => $id
            ]
        );
    }

    public function toggleActivo(int $id): void
    {
        $this->db->ejecutar("UPDATE almacenes SET activo = NOT activo WHERE id = :id", ['id' => $id]);
    }

    public function desactivar(int $id): void
    {
        $this->db->ejecutar("UPDATE almacenes SET activo = 0 WHERE id = :id", ['id' => $id]);
    }
}