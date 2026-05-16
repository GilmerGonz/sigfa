<?php
/**
 * =====================================================
 * SIGFA - Modelo: Inventario (Lotes)
 * =====================================================
 * Gestiona lotes de inventario con lógica FIFO,
 * alertas de vencimiento y movimientos de Kardex.
 * =====================================================
 */

require_once __DIR__ . '/../../config/db.php';

class Inventario
{
    private Conexion $db;

    public function __construct()
    {
        $this->db = Conexion::obtenerInstancia();
    }

    // =====================================================
    // GESTIÓN DE LOTES
    // =====================================================

    /**
     * Registrar entrada de un nuevo lote al inventario.
     * Registra también el movimiento en el Kardex de auditoría.
     *
     * @param array $datos  Datos del lote (medicamento_id, numero_lote, fecha_vencimiento, cantidad, etc.)
     * @param int   $usuarioId ID del usuario que registra la entrada.
     * @return int  ID del lote creado.
     */
    public function registrarEntrada(array $datos, int $usuarioId): int
    {
        $this->db->iniciarTransaccion();

        try {
            // 1. Insertar el lote
            $this->db->ejecutar(
                "INSERT INTO lotes_inventario 
                    (medicamento_id, almacen_id, proveedor_id, numero_lote, fecha_fabricacion, fecha_vencimiento,
                     cantidad_recibida, cantidad_disponible, precio_unitario,
                     numero_guia, chofer_nombre, chofer_cedula, chofer_telefono, chofer_correo, placa_vehiculo, 
                     observaciones, registrado_por)
                 VALUES 
                    (:medicamento_id, :almacen_id, :proveedor_id, :numero_lote, :fecha_fabricacion, :fecha_vencimiento,
                     :cant_rec, :cant_disp, :precio_unitario,
                     :numero_guia, :chofer_nombre, :chofer_cedula, :chofer_telefono, :chofer_correo, :placa_vehiculo,
                     :observaciones, :registrado_por)",
                [
                    'medicamento_id'   => $datos['medicamento_id'],
                    'almacen_id'      => $datos['almacen_id'] ?? null,
                    'proveedor_id'     => $datos['proveedor_id'] ?? null,
                    'numero_lote'      => $datos['numero_lote'],
                    'fecha_fabricacion' => $datos['fecha_fabricacion'] ?? null,
                    'fecha_vencimiento' => $datos['fecha_vencimiento'],
                    'cant_rec'         => $datos['cantidad'],
                    'cant_disp'        => $datos['cantidad'],
                    'precio_unitario'  => $datos['precio_unitario'] ?? 0,
                    'numero_guia'      => $datos['numero_guia'] ?? null,
                    'chofer_nombre'    => $datos['chofer_nombre'] ?? null,
                    'chofer_cedula'    => $datos['chofer_cedula'] ?? null,
                    'chofer_telefono'  => $datos['chofer_telefono'] ?? null,
                    'chofer_correo'    => $datos['chofer_correo'] ?? null,
                    'placa_vehiculo'   => $datos['placa_vehiculo'] ?? null,
                    'observaciones'    => $datos['observaciones'] ?? null,
                    'registrado_por'   => $usuarioId,
                ]
            );

            $loteId = (int) $this->db->ultimoId();

            // 2. Obtener stock total actual del medicamento (antes de esta entrada)
            $stockAnterior = $this->obtenerStockTotalMedicamento($datos['medicamento_id']) - (int) $datos['cantidad'];

            // 3. Registrar en el Kardex de auditoría
            $this->registrarMovimientoKardex([
                'medicamento_id'   => $datos['medicamento_id'],
                'lote_id'          => $loteId,
                'tipo_movimiento'  => 'Entrada',
                'cantidad'         => (int) $datos['cantidad'],
                'stock_anterior'   => max(0, $stockAnterior),
                'stock_posterior'  => $stockAnterior + (int) $datos['cantidad'],
                'referencia_tipo'  => 'lote_inventario',
                'referencia_id'    => $loteId,
                'motivo'           => 'Recepción de lote: ' . $datos['numero_lote'],
                'usuario_id'       => $usuarioId,
            ]);

            // 4. Verificar si el lote vence en menos de 30 días → generar alerta
            $this->verificarAlertaVencimiento($loteId);

            $this->db->confirmar();
            return $loteId;

        } catch (\Exception $e) {
            $this->db->revertir();
            throw $e;
        }
    }

    /**
     * FIFO: Obtener el lote más próximo a vencer con stock disponible
     * para un medicamento dado.
     *
     * @param int $medicamentoId
     * @return array|null  Datos del lote FIFO o null si no hay stock.
     */
    public function obtenerLoteFIFO(int $medicamentoId): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM lotes_inventario 
             WHERE medicamento_id = :med_id 
               AND cantidad_disponible > 0 
               AND fecha_vencimiento > CURDATE()
             ORDER BY fecha_vencimiento ASC, id ASC 
             LIMIT 1",
            ['med_id' => $medicamentoId]
        );
        $lote = $stmt->fetch();
        return $lote ?: null;
    }

    /**
     * FIFO: Descontar cantidad de un medicamento respetando el orden FIFO.
     * Puede consumir de múltiples lotes si la cantidad lo requiere.
     *
     * @param int $medicamentoId    ID del medicamento.
     * @param int $cantidadSolicitada Cantidad a descontar.
     * @param int $usuarioId        ID del usuario que despacha.
     * @param int $despachoId       ID del despacho asociado.
     * @return array  Lista de lotes consumidos con sus cantidades.
     * @throws \RuntimeException Si no hay stock suficiente.
     */
    public function descontarFIFO(int $medicamentoId, int $cantidadSolicitada, int $usuarioId, int $despachoId): array
    {
        // Obtener todos los lotes disponibles en orden FIFO
        $stmt = $this->db->ejecutar(
            "SELECT * FROM lotes_inventario 
             WHERE medicamento_id = :med_id 
               AND cantidad_disponible > 0 
               AND fecha_vencimiento > CURDATE()
             ORDER BY fecha_vencimiento ASC, id ASC",
            ['med_id' => $medicamentoId]
        );
        $lotes = $stmt->fetchAll();

        // Verificar stock total
        $stockTotal = array_sum(array_column($lotes, 'cantidad_disponible'));
        if ($stockTotal < $cantidadSolicitada) {
            throw new \RuntimeException(
                "Stock insuficiente. Disponible: $stockTotal, Solicitado: $cantidadSolicitada"
            );
        }

        $lotesConsumidos = [];
        $restante = $cantidadSolicitada;

        foreach ($lotes as $lote) {
            if ($restante <= 0) break;

            $descontar = min($restante, (int) $lote['cantidad_disponible']);
            $nuevoDisponible = (int) $lote['cantidad_disponible'] - $descontar;

            // Actualizar la cantidad disponible del lote
            $this->db->ejecutar(
                "UPDATE lotes_inventario SET cantidad_disponible = :nueva WHERE id = :id",
                ['nueva' => $nuevoDisponible, 'id' => $lote['id']]
            );

            // Registrar en Kardex
            $stockAnterior = $this->obtenerStockTotalMedicamento($medicamentoId) + $descontar;
            $this->registrarMovimientoKardex([
                'medicamento_id'   => $medicamentoId,
                'lote_id'          => $lote['id'],
                'tipo_movimiento'  => 'Salida',
                'cantidad'         => -$descontar,
                'stock_anterior'   => $stockAnterior,
                'stock_posterior'  => $stockAnterior - $descontar,
                'referencia_tipo'  => 'despacho',
                'referencia_id'    => $despachoId,
                'motivo'           => "Despacho #$despachoId - Lote: {$lote['numero_lote']}",
                'usuario_id'       => $usuarioId,
            ]);

            $lotesConsumidos[] = [
                'lote_id'     => $lote['id'],
                'numero_lote' => $lote['numero_lote'],
                'cantidad'    => $descontar,
                'fecha_vencimiento' => $lote['fecha_vencimiento'],
            ];

            $restante -= $descontar;
        }

        return $lotesConsumidos;
    }

    // =====================================================
    // ALERTAS
    // =====================================================

    /**
     * Obtener lotes próximos a vencer (< 30 días).
     */
    public function obtenerLotesPorVencer(int $dias = 30): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT 
                l.id, l.numero_lote, l.fecha_vencimiento, l.cantidad_disponible,
                DATEDIFF(l.fecha_vencimiento, CURDATE()) AS dias_para_vencer,
                m.codigo, m.nombre_generico, m.concentracion, m.presentacion
             FROM lotes_inventario l
             INNER JOIN medicamentos m ON l.medicamento_id = m.id
             WHERE l.cantidad_disponible > 0
               AND l.fecha_vencimiento > CURDATE()
               AND DATEDIFF(l.fecha_vencimiento, CURDATE()) <= :dias
             ORDER BY l.fecha_vencimiento ASC",
            ['dias' => $dias]
        );
        return $stmt->fetchAll();
    }

    /**
     * Verificar y generar alerta de vencimiento para un lote específico.
     */
    private function verificarAlertaVencimiento(int $loteId): void
    {
        $stmt = $this->db->ejecutar(
            "SELECT l.*, m.nombre_generico, m.concentracion,
                    DATEDIFF(l.fecha_vencimiento, CURDATE()) AS dias_para_vencer
             FROM lotes_inventario l
             INNER JOIN medicamentos m ON l.medicamento_id = m.id
             WHERE l.id = :id",
            ['id' => $loteId]
        );
        $lote = $stmt->fetch();

        if ($lote && $lote['dias_para_vencer'] <= 30 && $lote['dias_para_vencer'] > 0) {
            $nivel = $lote['dias_para_vencer'] <= 7 ? 'Critico' : 'Advertencia';

            $this->db->ejecutar(
                "INSERT INTO alertas (tipo, nivel, titulo, mensaje, referencia_tipo, referencia_id)
                 VALUES ('Vencimiento', :nivel, :titulo, :mensaje, 'lote_inventario', :ref_id)",
                [
                    'nivel'   => $nivel,
                    'titulo'  => "Lote próximo a vencer: {$lote['nombre_generico']}",
                    'mensaje' => "El lote {$lote['numero_lote']} de {$lote['nombre_generico']} ({$lote['concentracion']}) "
                                . "vence en {$lote['dias_para_vencer']} días ({$lote['fecha_vencimiento']}). "
                                . "Stock disponible: {$lote['cantidad_disponible']} unidades.",
                    'ref_id'  => $loteId,
                ]
            );
        }
    }

    /**
     * Contar alertas de vencimiento activas (no resueltas).
     */
    public function contarAlertasVencimiento(): int
    {
        $stmt = $this->db->ejecutar(
            "SELECT COUNT(*) AS total FROM alertas 
             WHERE tipo = 'Vencimiento' AND resuelta = 0"
        );
        return (int) $stmt->fetch()['total'];
    }

    // =====================================================
    // KARDEX DE AUDITORÍA
    // =====================================================

    /**
     * Registrar un movimiento en el Kardex de auditoría.
     */
    public function registrarMovimientoKardex(array $datos): int
    {
        $this->db->ejecutar(
            "INSERT INTO kardex 
                (medicamento_id, lote_id, tipo_movimiento, cantidad, 
                 stock_anterior, stock_posterior, referencia_tipo, referencia_id, 
                 operacion, motivo, observacion, usuario_id, ip_address, session_id, user_agent)
             VALUES 
                (:medicamento_id, :lote_id, :tipo_movimiento, :cantidad,
                 :stock_anterior, :stock_posterior, :referencia_tipo, :referencia_id,
                 :operacion, :motivo, :observacion, :usuario_id, :ip, :sid, :ua)",
            [
                'medicamento_id'   => $datos['medicamento_id'],
                'lote_id'          => $datos['lote_id'] ?? null,
                'tipo_movimiento'  => $datos['tipo_movimiento'],
                'cantidad'         => $datos['cantidad'],
                'stock_anterior'   => $datos['stock_anterior'],
                'stock_posterior'  => $datos['stock_posterior'],
                'referencia_tipo'  => $datos['referencia_tipo'] ?? null,
                'referencia_id'    => $datos['referencia_id'] ?? null,
                'operacion'        => $datos['operacion'] ?? 'Desconocida',
                'motivo'           => $datos['motivo'] ?? null,
                'observacion'      => $datos['observacion'] ?? null,
                'usuario_id'       => $datos['usuario_id'] ?? null,
                'ip'               => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'sid'              => session_id(),
                'ua'               => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido',
            ]
        );
        return (int) $this->db->ultimoId();
    }

    /**
     * Obtener historial del Kardex para un medicamento.
     */
    public function obtenerKardexMedicamento(int $medicamentoId, int $limite = 50): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT k.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido,
                    l.chofer_nombre, l.placa_vehiculo
             FROM kardex k
             LEFT JOIN usuarios u ON k.usuario_id = u.id
             LEFT JOIN lotes_inventario l ON k.lote_id = l.id
             WHERE k.medicamento_id = :med_id
             ORDER BY k.fecha_movimiento DESC
             LIMIT :limite",
            ['med_id' => $medicamentoId, 'limite' => $limite]
        );
        return $stmt->fetchAll();
    }

    // =====================================================
    // CONSULTAS GENERALES
    // =====================================================

    /**
     * Obtener el stock total disponible de un medicamento.
     */
    public function obtenerStockTotalMedicamento(int $medicamentoId): int
    {
        $stmt = $this->db->ejecutar(
            "SELECT COALESCE(SUM(cantidad_disponible), 0) AS total 
             FROM lotes_inventario 
             WHERE medicamento_id = :med_id AND cantidad_disponible > 0",
            ['med_id' => $medicamentoId]
        );
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Listar todos los lotes de un medicamento.
     */
    public function listarLotesMedicamento(int $medicamentoId): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT l.*, p.razon_social AS proveedor_nombre
             FROM lotes_inventario l
             LEFT JOIN proveedores p ON l.proveedor_id = p.id
             WHERE l.medicamento_id = :med_id
             ORDER BY l.fecha_vencimiento ASC",
            ['med_id' => $medicamentoId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Contar lotes con stock bajo.
     */
    public function contarLotesStockBajo(): int
    {
        $stmt = $this->db->ejecutar(
            "SELECT COUNT(*) AS total FROM (
                SELECT m.id, MIN(m.stock_minimo) AS stock_min
                FROM medicamentos m
                LEFT JOIN lotes_inventario l ON m.id = l.medicamento_id AND l.cantidad_disponible > 0
                WHERE m.activo = 1
                GROUP BY m.id
                HAVING COALESCE(SUM(l.cantidad_disponible), 0) <= stock_min
             ) AS sub"
        );
        return (int) $stmt->fetch()['total'];
    }

    // =====================================================
    // CORRECCIONES ADMINISTRATIVAS (Corrección 4)
    // =====================================================

    /**
     * Ajustar el stock de un lote específico (conteo físico).
     * Registra el ajuste en el Kardex con motivo obligatorio.
     *
     * @param int    $loteId        ID del lote a ajustar.
     * @param int    $nuevaCantidad Nueva cantidad disponible.
     * @param int    $usuarioId     ID del administrador.
     * @param string $motivo        Motivo del ajuste (obligatorio).
     * @throws \RuntimeException Si el lote no existe.
     */
    public function ajustarStock(int $loteId, int $nuevaCantidad, string $nuevaFechaVenc, int $usuarioId, string $motivo): void
    {
        // 1. Obtener lote actual
        $stmt = $this->db->ejecutar(
            "SELECT l.*, m.nombre_generico FROM lotes_inventario l
             INNER JOIN medicamentos m ON l.medicamento_id = m.id
             WHERE l.id = :id",
            ['id' => $loteId]
        );
        $lote = $stmt->fetch();

        if (!$lote) {
            throw new \RuntimeException('El lote especificado no existe.');
        }

        $cantidadAnterior = (int) $lote['cantidad_disponible'];
        $diferencia = $nuevaCantidad - $cantidadAnterior;

        if ($diferencia === 0) {
            return; // No hay cambio
        }

        $this->db->iniciarTransaccion();

        try {
            // 2. Actualizar la cantidad disponible y fecha de vencimiento del lote
            $sql = "UPDATE lotes_inventario SET cantidad_disponible = :nueva";
            $params = ['nueva' => $nuevaCantidad, 'id' => $loteId];
            
            if (!empty($nuevaFechaVenc)) {
                $sql .= ", fecha_vencimiento = :nueva_fecha";
                $params['nueva_fecha'] = $nuevaFechaVenc;
            }
            $sql .= " WHERE id = :id";
            
            $this->db->ejecutar($sql, $params);

            // 3. Calcular stock total después del ajuste
            $stockPosterior = $this->obtenerStockTotalMedicamento($lote['medicamento_id']);

            // 4. Registrar en Kardex
            $tipoMovimiento = $diferencia > 0 ? 'Ajuste_Positivo' : 'Ajuste_Negativo';
            $this->registrarMovimientoKardex([
                'medicamento_id'   => $lote['medicamento_id'],
                'lote_id'          => $loteId,
                'tipo_movimiento'  => $tipoMovimiento,
                'cantidad'         => $diferencia,
                'stock_anterior'   => $stockPosterior - $diferencia,
                'stock_posterior'  => $stockPosterior,
                'referencia_tipo'  => 'ajuste_manual',
                'referencia_id'    => $loteId,
                'operacion'        => 'Ajuste Manual',
                'motivo'           => $motivo,
                'observacion'      => "Ajuste manual por Administrador — Lote: {$lote['numero_lote']}",
                'usuario_id'       => $usuarioId,
            ]);

            $this->db->confirmar();

        } catch (\Exception $e) {
            $this->db->revertir();
            throw $e;
        }
    }

    /**
     * Eliminar un lote erróneo del inventario.
     * Solo se permite si no tiene movimientos de despacho asociados.
     *
     * @param int    $loteId    ID del lote a eliminar.
     * @param int    $usuarioId ID del administrador.
     * @param string $motivo    Motivo de la eliminación.
     * @throws \RuntimeException Si el lote tiene despachos asociados.
     */
    public function eliminarLote(int $loteId, int $usuarioId, string $motivo = ''): void
    {
        // 1. Obtener datos del lote
        $stmt = $this->db->ejecutar(
            "SELECT l.*, m.nombre_generico FROM lotes_inventario l
             INNER JOIN medicamentos m ON l.medicamento_id = m.id
             WHERE l.id = :id",
            ['id' => $loteId]
        );
        $lote = $stmt->fetch();

        if (!$lote) {
            throw new \RuntimeException('El lote especificado no existe.');
        }

        // 2. Verificar que no tenga despachos asociados ni transferencias
        $stmtUso = $this->db->ejecutar(
            "SELECT 
                (SELECT COUNT(*) FROM despacho_detalle WHERE lote_id = :id) +
                (SELECT COUNT(*) FROM transferencia_detalle WHERE lote_id = :id2) AS total_uso",
            ['id' => $loteId, 'id2' => $loteId]
        );
        $totalUso = (int) $stmtUso->fetch()['total_uso'];

        if ($totalUso > 0) {
            throw new \RuntimeException(
                "❌ BLOQUEADA: No se puede eliminar el lote '{$lote['numero_lote']}' porque tiene $totalUso transacciones (despachos o transferencias) asociadas. Anule dichas transacciones primero."
            );
        }

        $this->db->iniciarTransaccion();

        try {
            $cantidadEliminada = (int) $lote['cantidad_disponible'];

            // 3. Registrar en Kardex ANTES de eliminar
            if ($cantidadEliminada > 0) {
                $stockAnterior = $this->obtenerStockTotalMedicamento($lote['medicamento_id']);
                $this->registrarMovimientoKardex([
                    'medicamento_id'   => $lote['medicamento_id'],
                    'lote_id'          => $loteId,
                    'tipo_movimiento'  => 'Anulacion',
                    'cantidad'         => -$cantidadEliminada,
                    'stock_anterior'   => $stockAnterior,
                    'stock_posterior'  => $stockAnterior - $cantidadEliminada,
                    'referencia_tipo'  => 'anulacion_entrada',
                    'referencia_id'    => $loteId,
                    'motivo'           => "Eliminación de lote erróneo: {$lote['numero_lote']} — Motivo: $motivo",
                    'usuario_id'       => $usuarioId,
                ]);
            }

            // 4. (Omitido) No eliminamos registros de Kardex porque es eliminación lógica

            // 5. Anular el lote lógicamente
            $this->db->ejecutar(
                "UPDATE lotes_inventario 
                 SET estatus = 'Anulado', cantidad_disponible = 0, motivo_anulacion = :motivo, anulado_por = :usuario, fecha_anulacion = NOW() 
                 WHERE id = :id",
                ['id' => $loteId, 'motivo' => $motivo, 'usuario' => $usuarioId]
            );

            $this->db->confirmar();

        } catch (\Exception $e) {
            $this->db->revertir();
            throw $e;
        }
    }

    /**
     * Obtener un lote específico por su ID.
     */
    public function obtenerLotePorId(int $loteId): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT l.*, m.nombre_generico, m.concentracion
             FROM lotes_inventario l
             INNER JOIN medicamentos m ON l.medicamento_id = m.id
             WHERE l.id = :id",
            ['id' => $loteId]
        );
        $lote = $stmt->fetch();
        return $lote ?: null;
    }
}

