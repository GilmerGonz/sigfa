<?php
require_once __DIR__ . '/../../config/db.php';

class Reporte
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->obtenerPDO();
    }

    public function porServicio(array $filtros): array
    {
        $sql = "SELECT 
                    sm.nombre as servicio,
                    COUNT(d.id) as total_despachos,
                    SUM(dd.cantidad) as total_medicamentos,
                    SUM(dd.cantidad * dd.precio_unitario) as monto_total,
                    a.sexo,
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) < 12 THEN 'Niño'
                        WHEN TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) >= 12 AND TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) < 18 THEN 'Adolescente'
                        WHEN TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) >= 18 THEN 'Adulto'
                        ELSE 'Sin datos'
                    END AS grupo_etario
                FROM despachos d
                INNER JOIN despacho_detalle dd ON d.id = dd.despacho_id
                LEFT JOIN servicios_medicos sm ON d.servicio_id = sm.id
                LEFT JOIN asegurados a ON d.asegurado_id = a.id
                WHERE d.estatus = 'Despachado'";
        
        $params = [];
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(d.fecha_despacho) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(d.fecha_despacho) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['servicio_id'])) {
            $sql .= " AND d.servicio_id = ?";
            $params[] = $filtros['servicio_id'];
        }

        $sql .= " GROUP BY sm.id, sm.nombre, a.sexo, grupo_etario ORDER BY sm.nombre, a.sexo";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function porMedicamento(array $filtros): array
    {
        $sql = "SELECT 
                    m.codigo,
                    m.nombre_generico,
                    m.concentracion,
                    sm.nombre as servicio,
                    SUM(dd.cantidad) as cantidad_despachada
                FROM despacho_detalle dd
                INNER JOIN medicamentos m ON dd.medicamento_id = m.id
                INNER JOIN despachos d ON dd.despacho_id = d.id
                LEFT JOIN servicios_medicos sm ON d.servicio_id = sm.id
                WHERE d.estatus = 'Despachado'";

        $params = [];
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(d.fecha_despacho) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(d.fecha_despacho) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['medicamento_id'])) {
            $sql .= " AND dd.medicamento_id = ?";
            $params[] = $filtros['medicamento_id'];
        }

        $sql .= " GROUP BY m.id, sm.id ORDER BY m.nombre_generico, sm.nombre";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function porPeriodo(array $filtros): array
    {
        $sql = "SELECT 
                    d.ticket,
                    DATE(d.fecha_despacho) as fecha,
                    CONCAT(a.nombre, ' ', a.apellido) as paciente,
                    a.cedula,
                    m.nombre_generico,
                    dd.cantidad
                FROM despachos d
                INNER JOIN despacho_detalle dd ON d.id = dd.despacho_id
                INNER JOIN medicamentos m ON dd.medicamento_id = m.id
                INNER JOIN asegurados a ON d.asegurado_id = a.id
                WHERE d.estatus = 'Despachado'";

        $params = [];
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(d.fecha_despacho) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(d.fecha_despacho) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['paciente_id'])) {
            $sql .= " AND d.asegurado_id = ?";
            $params[] = $filtros['paciente_id'];
        }
        if (!empty($filtros['medicamento_id'])) {
            $sql .= " AND dd.medicamento_id = ?";
            $params[] = $filtros['medicamento_id'];
        }

        $sql .= " ORDER BY d.fecha_despacho DESC, d.ticket";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function consumoBolivares(array $filtros): array
    {
        $sql = "SELECT 
                    DATE(d.fecha_despacho) as fecha,
                    sm.nombre as servicio,
                    COUNT(DISTINCT d.id) as despachos,
                     SUM(dd.cantidad) as total_medicamentos,
                     SUM(dd.cantidad * COALESCE(l.precio_unitario, 0)) as monto
                 FROM despachos d
                INNER JOIN despacho_detalle dd ON d.id = dd.despacho_id
                LEFT JOIN lotes_inventario l ON dd.lote_id = l.id
                LEFT JOIN servicios_medicos sm ON d.servicio_id = sm.id
                WHERE d.estatus = 'Despachado'";

        $params = [];
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(d.fecha_despacho) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(d.fecha_despacho) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }

        $sql .= " GROUP BY DATE(d.fecha_despacho), sm.id ORDER BY fecha DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function inventarioValorizado(): array
    {
        $sql = "SELECT 
                    m.codigo,
                    m.nombre_generico,
                    m.concentracion,
                    m.tipo,
                    COALESCE(SUM(l.cantidad_disponible), 0) as stock_total,
                    COALESCE(l.precio_unitario, 0) as precio_unitario,
                    COALESCE(SUM(l.cantidad_disponible * l.precio_unitario), 0) as valor_total
                FROM medicamentos m
                LEFT JOIN lotes_inventario l ON m.id = l.medicamento_id AND l.cantidad_disponible > 0
                WHERE m.activo = 1
                GROUP BY m.id
                HAVING stock_total > 0
                ORDER BY m.nombre_generico";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function kardexCompleto(int $medicamentoId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT k.*, l.numero_lote, u.nombre as usuario_nombre
            FROM kardex k
            LEFT JOIN lotes_inventario l ON k.lote_id = l.id
            LEFT JOIN usuarios u ON k.usuario_id = u.id
            WHERE k.medicamento_id = ?
            ORDER BY k.fecha_movimiento DESC
            LIMIT 100
        ");
        $stmt->execute([$medicamentoId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function auditoriaMovimientos(array $filtros): array
    {
        $sql = "SELECT 
                    a.fecha_accion,
                    a.accion,
                    a.modulo,
                    a.detalle,
                    a.ip_address,
                    CONCAT(u.nombre, ' ', u.apellido) as usuario
                FROM auditoria_sistema a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE 1=1";

        $params = [];
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(a.fecha_accion) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(a.fecha_accion) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['accion'])) {
            $sql .= " AND a.accion = ?";
            $params[] = $filtros['accion'];
        }
        if (!empty($filtros['modulo'])) {
            $sql .= " AND a.modulo = ?";
            $params[] = $filtros['modulo'];
        }

        $sql .= " ORDER BY a.fecha_accion DESC LIMIT 200";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Si la tabla no existe, devolvemos un array vacío en lugar de fallar
            if (strpos($e->getMessage(), 'Base table or view not found') !== false) {
                error_log("Advertencia: Tabla auditoria_sistema no existe. Retornando array vacío.");
                return [];
            }
            // Para otros errores PDO, los releanzamos
            throw $e;
        }
    }

    public function porPatologia(array $filtros): array
    {
        $sql = "SELECT 
                    p.nombre as patologia,
                    a.sexo,
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) < 12 THEN 'Niño'
                        WHEN TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) >= 12 AND TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) < 18 THEN 'Adolescente'
                        WHEN TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) >= 18 THEN 'Adulto'
                        ELSE 'Sin datos'
                    END AS grupo_etario,
                    COUNT(d.id) as total_despachos,
                    SUM(dd.cantidad) as total_medicamentos
                FROM despachos d
                INNER JOIN despacho_detalle dd ON d.id = dd.despacho_id
                LEFT JOIN patologias p ON d.patologia_id = p.id
                LEFT JOIN asegurados a ON d.asegurado_id = a.id
                WHERE d.estatus = 'Despachado'";

        $params = [];
        if (!empty($filtros['patologia_id'])) {
            $sql .= " AND d.patologia_id = ?";
            $params[] = $filtros['patologia_id'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(d.fecha_despacho) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(d.fecha_despacho) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }

        $sql .= " GROUP BY p.id, p.nombre, a.sexo, grupo_etario ORDER BY p.nombre, a.sexo";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function prescripcionPaciente(string $cedula, string $fecha_desde, string $fecha_hasta): array
    {
        $sql = "SELECT 
                    d.fecha_despacho as fecha,
                    d.ticket,
                    CONCAT(a.nombre, ' ', a.apellido) as paciente_nombre,
                    a.cedula as paciente_cedula,
                    m.nombre_generico,
                    m.concentracion,
                    dd.cantidad,
                    dd.cantidad_recetada,
                    dd.ciclo_asignado,
                    med.nombre as medico_nombre,
                    med.apellido as medico_apellido
                FROM despachos d
                INNER JOIN asegurados a ON d.asegurado_id = a.id
                INNER JOIN despacho_detalle dd ON d.id = dd.despacho_id
                INNER JOIN medicamentos m ON dd.medicamento_id = m.id
                LEFT JOIN medicos med ON d.medico_id = med.id
                WHERE a.cedula = ? AND d.estatus = 'Despachado'";

        $params = [$cedula];
        
        if (!empty($fecha_desde)) {
            $sql .= " AND DATE(d.fecha_despacho) >= ?";
            $params[] = $fecha_desde;
        }
        if (!empty($fecha_hasta)) {
            $sql .= " AND DATE(d.fecha_despacho) <= ?";
            $params[] = $fecha_hasta;
        }

        $sql .= " ORDER BY d.fecha_despacho DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarPacientePorCedula(string $cedula): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, cedula, nombre, apellido, historia_medica FROM asegurados WHERE cedula = ? OR historia_medica = ? LIMIT 1");
        $stmt->execute([$cedula, $cedula]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function obtenerMedicamentos(): array
    {
        $stmt = $this->pdo->query("SELECT id, codigo, nombre_generico, concentracion FROM medicamentos WHERE activo = 1 ORDER BY nombre_generico");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerServicios(): array
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM servicios_medicos WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerPatologias(): array
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM patologias WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerGrupos(): array
    {
        $stmt = $this->pdo->query("SELECT id, codigo, nombre FROM grupos_medicamentos WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function recetasDiarias(string $fecha): array
    {
        $sql = "SELECT 
                    d.ticket,
                    d.fecha_despacho as fecha,
                    CONCAT(a.nombre, ' ', a.apellido) as paciente,
                    a.cedula,
                    m.nombre_generico as medicamento,
                    dd.cantidad,
                    sm.nombre as servicio,
                    CONCAT(u.nombre, ' ', u.apellido) as usuario_operador,
                    d.estatus
                FROM despachos d
                INNER JOIN despacho_detalle dd ON d.id = dd.despacho_id
                INNER JOIN medicamentos m ON dd.medicamento_id = m.id
                INNER JOIN asegurados a ON d.asegurado_id = a.id
                LEFT JOIN servicios_medicos sm ON d.servicio_id = sm.id
                LEFT JOIN usuarios u ON d.despachado_por = u.id
                WHERE DATE(d.fecha_despacho) = ? AND d.estatus IN ('Pendiente', 'Despachado')
                ORDER BY d.fecha_despacho DESC, d.ticket";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fecha]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function consumoMasivo(array $filtros): array
    {
        $sql = "SELECT 
                    m.codigo,
                    m.nombre_generico as medicamento,
                    m.concentracion,
                    m.presentacion,
                    SUM(dd.cantidad) as cantidad_despachada,
                    COUNT(DISTINCT d.id) as total_recetas,
                    (SELECT COALESCE(SUM(cantidad_disponible), 0) FROM lotes_inventario WHERE medicamento_id = m.id AND cantidad_disponible > 0) as stock_actual
                FROM despacho_detalle dd
                INNER JOIN medicamentos m ON dd.medicamento_id = m.id
                INNER JOIN despachos d ON dd.despacho_id = d.id
                WHERE d.estatus = 'Despachado'";

        $params = [];
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(d.fecha_despacho) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(d.fecha_despacho) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['grupo_id'])) {
            $sql .= " AND m.grupo_id = ?";
            $params[] = $filtros['grupo_id'];
        }

        $sql .= " GROUP BY m.id, m.codigo, m.nombre_generico, m.concentracion, m.presentacion ORDER BY cantidad_despachada DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function costoPromedio(array $filtros): array
    {
        $sql = "SELECT 
                    m.nombre_generico as medicamento,
                    m.concentracion,
                 AVG(COALESCE(l.precio_unitario, 0)) as costo_promedio,
                 SUM(COALESCE(l.cantidad_disponible, 0)) as stock_actual,
                 SUM(COALESCE(l.cantidad_disponible, 0) * l.precio_unitario) as valor_total
                FROM medicamentos m
                LEFT JOIN lotes_inventario l ON m.id = l.medicamento_id AND l.cantidad_disponible > 0
                WHERE m.activo = 1";

        $params = [];
        if (!empty($filtros['grupo_id'])) {
            $sql .= " AND m.grupo_id = ?";
            $params[] = $filtros['grupo_id'];
        }

        $sql .= " GROUP BY m.id HAVING stock_actual > 0 ORDER BY valor_total DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function alertasGlobal(): array
    {
        $alertas = [];
        
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM medicamentos WHERE activo = 1 AND (grupo_id IS NULL OR grupo_id = 0)");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row['total'] > 0) {
            $alertas[] = "Existen {$row['total']} medicamentos sin grupo asignado";
        }
        
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM asegurados WHERE estatus = 'Activo' AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) > 10 AND (cedula IS NULL OR cedula = '')");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row['total'] > 0) {
            $alertas[] = "Existen {$row['total']} pacientes mayores de 10 años sin cédula";
        }
        
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM despachos WHERE estatus = 'Despachado' AND fecha_despacho > NOW() + INTERVAL 1 DAY");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row['total'] > 0) {
            $alertas[] = "Existen {$row['total']} despachos con fecha futura sospechosa";
        }
        
        $stmt = $this->pdo->query("SELECT nombre_generico, COUNT(*) as total FROM medicamentos WHERE activo = 1 GROUP BY nombre_generico HAVING total > 1");
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $alertas[] = "Medicamento duplicado: '{$row['nombre_generico']}' ({$row['total']} registros)";
        }
        
        return $alertas;
    }
    public function listarAnulaciones(array $filtros = []): array
    {
        $sql = "
            SELECT * FROM (
                (SELECT 
                    'Despacho' as tipo,
                    d.ticket as referencia,
                    d.fecha_despacho as fecha_original,
                    d.fecha_anulacion,
                    d.motivo_anulacion,
                    CONCAT(u.nombre, ' ', u.apellido) as usuario_anulo
                FROM despachos d
                LEFT JOIN usuarios u ON d.anulado_por = u.id
                WHERE d.estatus = 'Anulado')
                
                UNION ALL
                
                (SELECT 
                    'Transferencia' as tipo,
                    t.codigo_transaccion as referencia,
                    t.fecha_registro as fecha_original,
                    t.fecha_anulacion,
                    t.motivo_anulacion,
                    CONCAT(u.nombre, ' ', u.apellido) as usuario_anulo
                FROM transferencias t
                LEFT JOIN usuarios u ON t.anulado_por = u.id
                WHERE t.estatus = 'Anulada')
                
                UNION ALL
                
                (SELECT 
                    'Entrada de Inventario' as tipo,
                    l.numero_lote as referencia,
                    l.fecha_recepcion as fecha_original,
                    l.fecha_anulacion,
                    l.motivo_anulacion,
                    CONCAT(u.nombre, ' ', u.apellido) as usuario_anulo
                FROM lotes_inventario l
                LEFT JOIN usuarios u ON l.anulado_por = u.id
                WHERE l.estatus = 'Anulado')
            ) AS anulaciones
            WHERE 1=1
        ";
        
        $params = [];
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(fecha_anulacion) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(fecha_anulacion) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        
        $sql .= " ORDER BY fecha_anulacion DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Log the error for debugging
            error_log("Error en listarAnulaciones: " . $e->getMessage());
            throw $e;
        }
    }
}