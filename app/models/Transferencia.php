<?php
require_once __DIR__ . '/../../config/db.php';

/**
 * SIGFA - Modelo: Transferencia (Normalizado)
 * Gestiona traspasos entre almacenes y servicios.
 */
class Transferencia
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::obtenerInstancia()->obtenerPDO();
    }

    public function generarCodigoTransaccion(): string
    {
        $fecha = date('Ymd');
        $stmt = $this->pdo->prepare("
            SELECT MAX(CAST(SUBSTRING(codigo_transaccion, 16) AS UNSIGNED)) as max_seq
            FROM transferencias
            WHERE codigo_transaccion LIKE ?
        ");
        $stmt->execute(["TRF-{$fecha}-%"]);
        $result = $stmt->fetch();
        $seq = ($result['max_seq'] ?? 0) + 1;
        return "TRF-{$fecha}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function crear(array $data, array $detalles, int $usuarioId): array
    {
        $this->pdo->beginTransaction();
        
        try {
            $codigo = $this->generarCodigoTransaccion();
            
            // 1. Insertar Cabecera
            $stmt = $this->pdo->prepare("
                INSERT INTO transferencias 
                (codigo_transaccion, almacen_origen_id, almacen_destino_id, servicio_destino_id, motivo, observaciones, registrado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $codigo,
                $data['almacen_origen'],
                $data['almacen_destino'] ?? null,
                $data['servicio_destino'] ?? null,
                $data['motivo'] ?? 'Traspaso Interno',
                $data['observaciones'] ?? null,
                $usuarioId
            ]);
            
            $transferenciaId = $this->pdo->lastInsertId();
            
            // 2. Insertar Detalles y Actualizar Stock
            $stmtDet = $this->pdo->prepare("
                INSERT INTO transferencia_detalle (transferencia_id, medicamento_id, lote_id, cantidad)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($detalles as $detalle) {
                $stmtDet->execute([
                    $transferenciaId,
                    $detalle['medicamento_id'],
                    $detalle['lote_id'] ?? null,
                    $detalle['cantidad']
                ]);
                
                $this->actualizarStockLote($detalle['lote_id'], $detalle['cantidad']);
                $this->registrarKardex(
                    $detalle['medicamento_id'], 
                    $detalle['lote_id'], 
                    'Salida', 
                    -$detalle['cantidad'], 
                    'transferencia', 
                    $transferenciaId, 
                    'Transferencia Interna: ' . $codigo, 
                    $usuarioId
                );
            }
            
            $this->pdo->commit();
            return ['success' => true, 'codigo' => $codigo, 'id' => $transferenciaId];
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function actualizarStockLote(int $loteId, int $cantidad): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE lotes_inventario 
            SET cantidad_disponible = cantidad_disponible - ?
            WHERE id = ? AND cantidad_disponible >= ?
        ");
        $stmt->execute([$cantidad, $loteId, $cantidad]);
        
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException("Stock insuficiente en lote ID: {$loteId}");
        }
    }

    private function registrarKardex(int $medicamentoId, ?int $loteId, string $tipo, int $cantidad, ?string $refTipo, ?int $refId, ?string $motivo, int $usuarioId): void
    {
        // Obtener stock total actual para el cálculo de anterior/posterior
        $stmtStock = $this->pdo->prepare("SELECT COALESCE(SUM(cantidad_disponible),0) FROM lotes_inventario WHERE medicamento_id = ?");
        $stmtStock->execute([$medicamentoId]);
        $stockActual = (int)$stmtStock->fetchColumn();

        $stmt = $this->pdo->prepare("
            INSERT INTO kardex 
            (medicamento_id, lote_id, tipo_movimiento, cantidad, stock_anterior, stock_posterior, referencia_tipo, referencia_id, operacion, observacion, usuario_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stockAnterior = $stockActual + abs($cantidad); 
        $stockPosterior = $stockActual;
        
        $stmt->execute([
            $medicamentoId,
            $loteId,
            $tipo == 'Salida' ? 'Salida' : 'Entrada',
            $cantidad,
            $stockAnterior,
            $stockPosterior,
            $refTipo,
            $refId,
            'Transferencia / Traspaso',
            $motivo,
            $usuarioId
        ]);
    }

    public function listar(array $filtros = []): array
    {
        $sql = "
            SELECT t.*, 
                   ao.nombre as almacen_origen_nombre,
                   ad.nombre as almacen_destino_nombre,
                   sm.nombre as servicio_nombre,
                   CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                   CASE 
                        WHEN t.servicio_destino_id IS NOT NULL THEN 'Almacen_Servicio'
                        ELSE 'Almacen_Almacen'
                   END as tipo
            FROM transferencias t
            LEFT JOIN almacenes ao ON t.almacen_origen_id = ao.id
            LEFT JOIN almacenes ad ON t.almacen_destino_id = ad.id
            LEFT JOIN servicios_medicos sm ON t.servicio_destino_id = sm.id
            LEFT JOIN usuarios u ON t.registrado_por = u.id
            WHERE 1=1
        ";
        
        $params = [];
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(t.fecha_registro) >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(t.fecha_registro) <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['tipo'])) {
            if ($filtros['tipo'] === 'Almacen_Almacen') {
                $sql .= " AND t.almacen_destino_id IS NOT NULL";
            } elseif ($filtros['tipo'] === 'Almacen_Servicio') {
                $sql .= " AND t.servicio_destino_id IS NOT NULL";
            }
        }
        if (!empty($filtros['estatus'])) {
            $sql .= " AND t.estatus = ?";
            $params[] = $filtros['estatus'];
        }
        
        $sql .= " ORDER BY t.fecha_registro DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "
            SELECT t.*, 
                   ao.nombre as almacen_origen_nombre,
                   ad.nombre as almacen_destino_nombre,
                   sm.nombre as servicio_nombre,
                   CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                   CASE 
                        WHEN t.servicio_destino_id IS NOT NULL THEN 'Almacen_Servicio'
                        ELSE 'Almacen_Almacen'
                   END as tipo
            FROM transferencias t
            LEFT JOIN almacenes ao ON t.almacen_origen_id = ao.id
            LEFT JOIN almacenes ad ON t.almacen_destino_id = ad.id
            LEFT JOIN servicios_medicos sm ON t.servicio_destino_id = sm.id
            LEFT JOIN usuarios u ON t.registrado_por = u.id
            WHERE t.id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function obtenerDetalles(int $transferenciaId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT td.*, 
                   m.nombre_generico, 
                   m.concentracion,
                   l.numero_lote,
                   l.fecha_vencimiento
            FROM transferencia_detalle td
            INNER JOIN medicamentos m ON td.medicamento_id = m.id
            LEFT JOIN lotes_inventario l ON td.lote_id = l.id
            WHERE td.transferencia_id = ?
        ");
        $stmt->execute([$transferenciaId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function completar(int $id, string $recibidoPor): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE transferencias 
            SET estatus = 'Completada', 
                recibido_por = ?, 
                fecha_recepcion = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$recibidoPor, $id]);
    }

    public function anularTransferencia(int $id, int $usuarioId, string $motivo): void
    {
        $this->pdo->beginTransaction();
        
        try {
            $t = $this->buscarPorId($id);
            if (!$t) throw new \RuntimeException("La transferencia no existe.");
            if ($t['estatus'] === 'Anulada') throw new \RuntimeException("Esta transferencia ya está anulada.");

            $detalles = $this->obtenerDetalles($id);

            foreach ($detalles as $det) {
                // 1. Revertir stock
                if ($det['lote_id']) {
                    $stmtStock = $this->pdo->prepare("UPDATE lotes_inventario SET cantidad_disponible = cantidad_disponible + ? WHERE id = ?");
                    $stmtStock->execute([$det['cantidad'], $det['lote_id']]);
                    
                    // 2. Registrar Kardex como ENTRADA (reverso de la salida original)
                    $this->registrarKardex(
                        $det['medicamento_id'],
                        $det['lote_id'],
                        'Entrada',
                        $det['cantidad'],
                        'anulacion_transferencia',
                        $id,
                        "Anulación de Transferencia {$t['codigo_transaccion']}: $motivo",
                        $usuarioId
                    );
                }
            }

            // 3. Marcar como anulada
            $stmtUpdate = $this->pdo->prepare("
                UPDATE transferencias 
                SET estatus = 'Anulada',
                    anulado_por = ?,
                    motivo_anulacion = ?,
                    fecha_anulacion = NOW()
                WHERE id = ?
            ");
            $stmtUpdate->execute([$usuarioId, $motivo, $id]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listarAlmacenes(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM almacenes WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function listarServicios(): array
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM servicios_medicos WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}