<?php
require_once __DIR__ . '/../../config/db.php';

class GrupoMedicamento
{
    private Conexion $db;

    public function __construct()
    {
        $this->db = Conexion::obtenerInstancia();
    }

    public function listar(): array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM grupos_medicamentos ORDER BY codigo ASC");
        return $stmt->fetchAll();
    }

    public function listarActivos(): array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM grupos_medicamentos WHERE activo = 1 ORDER BY codigo ASC");
        return $stmt->fetchAll();
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM grupos_medicamentos WHERE codigo = :codigo LIMIT 1", ['codigo' => $codigo]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM grupos_medicamentos WHERE id = :id LIMIT 1", ['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): int
    {
        $this->db->ejecutar(
            "INSERT INTO grupos_medicamentos (codigo, nombre) VALUES (:codigo, :nombre)",
            ['codigo' => strtoupper($datos['codigo']), 'nombre' => $datos['nombre']]
        );
        return (int) $this->db->ultimoId();
    }

    public function actualizar(int $id, array $datos): void
    {
        $this->db->ejecutar(
            "UPDATE grupos_medicamentos SET codigo = :codigo, nombre = :nombre WHERE id = :id",
            ['codigo' => strtoupper($datos['codigo']), 'nombre' => $datos['nombre'], 'id' => $id]
        );
    }

    public function toggleActivo(int $id): void
    {
        $this->db->ejecutar("UPDATE grupos_medicamentos SET activo = NOT activo WHERE id = :id", ['id' => $id]);
    }

    public function desactivar(int $id): void
    {
        $this->db->ejecutar("UPDATE grupos_medicamentos SET activo = 0 WHERE id = :id", ['id' => $id]);
    }
}
