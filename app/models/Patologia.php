<?php
require_once __DIR__ . '/../../config/db.php';

class Patologia
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->obtenerPDO();
    }

    public function listarActivos(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM patologias WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function listarPorClasificacion(string $clasificacion): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM patologias WHERE activo = 1 AND clasificacion = ? ORDER BY nombre");
        $stmt->execute([$clasificacion]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM patologias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorNombre(string $nombre): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM patologias WHERE nombre = ? AND activo = 1");
        $stmt->execute([$nombre]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function crear(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO patologias (nombre, clasificacion, grupo_etario, descripcion)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nombre'],
            $data['clasificacion'],
            $data['grupo_etario'] ?? null,
            $data['descripcion'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE patologias 
            SET nombre = ?, clasificacion = ?, grupo_etario = ?, descripcion = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['clasificacion'],
            $data['grupo_etario'] ?? null,
            $data['descripcion'] ?? null,
            $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE patologias SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscar(string $query): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM patologias 
            WHERE activo = 1 AND nombre LIKE ?
            ORDER BY nombre LIMIT 20
        ");
        $stmt->execute(["%{$query}%"]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerEstadisticas(): array
    {
        $stmt = $this->pdo->query("
            SELECT clasificacion, COUNT(*) as total 
            FROM patologias 
            WHERE activo = 1 
            GROUP BY clasificacion
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}