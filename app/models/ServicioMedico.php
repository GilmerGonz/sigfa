<?php
require_once __DIR__ . '/../../config/db.php';

class ServicioMedico
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->obtenerPDO();
    }

    public function listarActivos(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM servicios_medicos WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM servicios_medicos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM servicios_medicos WHERE codigo = ?");
        $stmt->execute([$codigo]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function crear(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO servicios_medicos (codigo, nombre, descripcion)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $data['codigo'],
            $data['nombre'],
            $data['descripcion'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE servicios_medicos 
            SET codigo = ?, nombre = ?, descripcion = ?
            WHERE id = ?
        ");
        return $stmt->execute([$data['codigo'], $data['nombre'], $data['descripcion'] ?? null, $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE servicios_medicos SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscar(string $query): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM servicios_medicos 
            WHERE activo = 1 AND (codigo LIKE ? OR nombre LIKE ?)
            ORDER BY nombre LIMIT 20
        ");
        $busqueda = "%{$query}%";
        $stmt->execute([$busqueda, $busqueda]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}