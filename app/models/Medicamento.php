<?php
/**
 * =====================================================
 * SIGFA - Modelo: Medicamento
 * =====================================================
 * Gestiona medicamentos y su catálogo.
 * =====================================================
 */

require_once __DIR__ . '/../../config/db.php';

class Medicamento
{
    private Conexion $db;

    public function __construct()
    {
        $this->db = Conexion::obtenerInstancia();
    }

    /**
     * Listar TODOS los medicamentos (incluyendo inactivos).
     */
    public function listarTodos(): array
    {
        $stmt = $this->db->ejecutar("SELECT * FROM medicamentos ORDER BY nombre_generico ASC");
        return $stmt->fetchAll();
    }

    /**
     * Listar todos los medicamentos activos con su stock total.
     */
    public function listarConStock(): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT 
                m.id, m.codigo, m.nombre_generico, m.nombre_comercial, m.id_principio_activo,
                m.concentracion, m.tipo, m.presentacion, m.grupo_id, gm.codigo AS grupo_codigo, gm.nombre AS grupo_nombre,
                m.stock_minimo, m.tipo_medicamento,
                pa.nombre AS principio_activo_nombre,
                COALESCE(SUM(l.cantidad_disponible), 0) AS stock_total,
                MIN(CASE WHEN l.cantidad_disponible > 0 THEN l.fecha_vencimiento END) AS proximo_vencimiento
             FROM medicamentos m
             LEFT JOIN grupos_medicamentos gm ON m.grupo_id = gm.id
             LEFT JOIN principios_activos pa ON m.id_principio_activo = pa.id
             LEFT JOIN lotes_inventario l ON m.id = l.medicamento_id AND l.cantidad_disponible > 0
             WHERE m.activo = 1
             GROUP BY m.id, m.codigo, m.nombre_generico, m.nombre_comercial, m.id_principio_activo,
                      m.concentracion, m.tipo, m.presentacion, m.grupo_id, gm.codigo, gm.nombre, m.stock_minimo, m.tipo_medicamento, pa.nombre
             ORDER BY m.nombre_generico ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Buscar medicamento por ID.
     */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT m.*, gm.codigo AS grupo_codigo, pa.nombre AS principio_activo_nombre 
             FROM medicamentos m 
             LEFT JOIN grupos_medicamentos gm ON m.grupo_id = gm.id 
             LEFT JOIN principios_activos pa ON m.id_principio_activo = pa.id
             WHERE m.id = :id AND m.activo = 1",
            ['id' => $id]
        );
        $med = $stmt->fetch();
        return $med ?: null;
    }

    /**
     * Buscar medicamento por código.
     */
    public function buscarPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT m.*, gm.codigo AS grupo_codigo, pa.nombre AS principio_activo_nombre 
             FROM medicamentos m 
             LEFT JOIN grupos_medicamentos gm ON m.grupo_id = gm.id 
             LEFT JOIN principios_activos pa ON m.id_principio_activo = pa.id
             WHERE m.codigo = :codigo AND m.activo = 1",
            ['codigo' => $codigo]
        );
        $med = $stmt->fetch();
        return $med ?: null;
    }

    /**
     * Buscar medicamentos por nombre (búsqueda parcial).
     */
    public function buscarPorNombre(string $termino): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT m.*, pa.nombre AS principio_activo_nombre, COALESCE(SUM(l.cantidad_disponible), 0) AS stock_total
             FROM medicamentos m
             LEFT JOIN principios_activos pa ON m.id_principio_activo = pa.id
             LEFT JOIN lotes_inventario l ON m.id = l.medicamento_id AND l.cantidad_disponible > 0
             WHERE m.activo = 1 AND (m.nombre_generico LIKE :termino OR m.nombre_comercial LIKE :termino2 OR pa.nombre LIKE :termino3)
             GROUP BY m.id
             ORDER BY m.nombre_generico ASC",
            ['termino' => "%$termino%", 'termino2' => "%$termino%", 'termino3' => "%$termino%"]
        );
        return $stmt->fetchAll();
    }

    /**
     * Crear un nuevo medicamento.
     */
    public function crear(array $datos): int
    {
        $this->db->ejecutar(
            "INSERT INTO medicamentos (codigo, nombre_generico, nombre_comercial, id_principio_activo, concentracion, tipo, presentacion, grupo_id, stock_minimo, tipo_medicamento)
             VALUES (:codigo, :nombre_generico, :nombre_comercial, :id_principio_activo, :concentracion, :tipo, :presentacion, :grupo_id, :stock_minimo, :tipo_medicamento)",
            [
                'codigo'            => $datos['codigo'],
                'nombre_generico'   => $datos['nombre_generico'],
                'nombre_comercial'  => $datos['nombre_comercial'] ?? null,
                'id_principio_activo' => $datos['id_principio_activo'] ?? null,
                'concentracion'     => $datos['concentracion'],
                'tipo'              => $datos['tipo'],
                'presentacion'      => $datos['presentacion'],
                'grupo_id'          => $datos['grupo_id'] ?? null,
                'stock_minimo'      => $datos['stock_minimo'] ?? 10,
                'tipo_medicamento'  => $datos['tipo_medicamento'] ?? 'General',
            ]
        );
        return (int) $this->db->ultimoId();
    }

    /**
     * Actualizar un medicamento existente.
     */
    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $params = ['id' => $id];

        foreach (['nombre_generico', 'nombre_comercial', 'id_principio_activo', 'concentracion', 'tipo', 'presentacion', 'grupo_id', 'stock_minimo', 'tipo_medicamento'] as $campo) {
            if (array_key_exists($campo, $datos)) {
                $campos[] = "$campo = :$campo";
                $params[$campo] = $datos[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "UPDATE medicamentos SET " . implode(', ', $campos) . " WHERE id = :id";
        $this->db->ejecutar($sql, $params);
        return true;
    }

    /**
     * Obtener medicamentos con stock bajo (por debajo del mínimo).
     */
    public function obtenerStockBajo(): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT 
                m.id, m.codigo, m.nombre_generico, m.concentracion, m.presentacion,
                m.stock_minimo,
                COALESCE(SUM(l.cantidad_disponible), 0) AS stock_total
             FROM medicamentos m
             LEFT JOIN lotes_inventario l ON m.id = l.medicamento_id AND l.cantidad_disponible > 0
             WHERE m.activo = 1
             GROUP BY m.id, m.codigo, m.nombre_generico, m.concentracion, m.presentacion, m.stock_minimo
             HAVING stock_total <= m.stock_minimo
             ORDER BY stock_total ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Contar total de medicamentos activos.
     */
    public function contarActivos(): int
    {
        $stmt = $this->db->ejecutar("SELECT COUNT(*) AS total FROM medicamentos WHERE activo = 1");
        return (int) $stmt->fetch()['total'];
    }
}
