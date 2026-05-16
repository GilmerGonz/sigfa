<?php
require_once __DIR__ . '/../../config/db.php';

class Devolucion
{
    private Conexion $db;

    public function __construct()
    {
        $this->db = Conexion::obtenerInstancia();
    }

    public function crear(array $datos, int $usuarioId): int
    {
        $this->db->iniciarTransaccion();
        try {
            $stmtCheck = $this->db->ejecutar(
                "SELECT cantidad_disponible FROM lotes_inventario WHERE id = :lote_id",
                ['lote_id' => $datos['lote_id']]
            );
            $disponible = (int) $stmtCheck->fetch()['cantidad_disponible'];

            if ($datos['cantidad'] > $disponible) {
                throw new \RuntimeException("La cantidad a devolver (" . $datos['cantidad'] . ") supera el stock disponible en el lote ($disponible).");
            }

            // Generar número de comprobante DEV-YYYYMMDD-XXXX
            $comprobante = $this->generarNumeroComprobante();

            $this->db->ejecutar(
                "INSERT INTO devoluciones_proveedores 
                (proveedor_id, medicamento_id, lote_id, cantidad, motivo, observaciones, numero_comprobante, registrado_por)
                VALUES (:proveedor_id, :medicamento_id, :lote_id, :cantidad, :motivo, :observaciones, :numero_comprobante, :registrado_por)",
                [
                    'proveedor_id' => $datos['proveedor_id'],
                    'medicamento_id' => $datos['medicamento_id'],
                    'lote_id' => $datos['lote_id'],
                    'cantidad' => $datos['cantidad'],
                    'motivo' => $datos['motivo'],
                    'observaciones' => $datos['observaciones'] ?? null,
                    'numero_comprobante' => $comprobante,
                    'registrado_por' => $usuarioId
                ]
            );
            $devolucionId = (int) $this->db->ultimoId();

            // Descontar stock del lote
            $this->db->ejecutar(
                "UPDATE lotes_inventario SET cantidad_disponible = cantidad_disponible - :cant WHERE id = :lote_id",
                ['cant' => $datos['cantidad'], 'lote_id' => $datos['lote_id']]
            );

            $stmtTotal = $this->db->ejecutar(
                "SELECT COALESCE(SUM(cantidad_disponible),0) FROM lotes_inventario WHERE medicamento_id = :med_id",
                ['med_id' => $datos['medicamento_id']]
            );
            $stockActual = (int) $stmtTotal->fetch()[0];

            $stmtKardex = $this->db->ejecutar(
                "INSERT INTO kardex 
                (medicamento_id, lote_id, tipo_movimiento, cantidad, stock_anterior, stock_posterior, referencia_tipo, referencia_id, operacion, observacion, usuario_id)
                VALUES (:med_id, :lote_id, 'Salida', :cant, :stock_ant, :stock_pos, 'devolucion_proveedor', :ref_id, :oper, :obs, :uid)",
                [
                    'med_id' => $datos['medicamento_id'],
                    'lote_id' => $datos['lote_id'],
                    'cant' => -$datos['cantidad'],
                    'stock_ant' => $stockActual + $datos['cantidad'],
                    'stock_pos' => $stockActual,
                    'ref_id' => $devolucionId,
                    'oper' => 'Devolución a Proveedor',
                    'obs' => 'Motivo: ' . $datos['motivo'],
                    'uid' => $usuarioId
                ]
            );

            $this->db->confirmar();
            return $devolucionId;
        } catch (\Exception $e) {
            $this->db->revertir();
            throw $e;
        }
    }

    public function listar(): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT d.*, p.razon_social AS proveedor_nombre, m.nombre_generico
              FROM devoluciones_proveedores d
              LEFT JOIN proveedores p ON d.proveedor_id = p.id
              LEFT JOIN medicamentos m ON d.medicamento_id = m.id
              ORDER BY d.fecha_devolucion DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Generar número de comprobante único para devolución
     */
    public function generarNumeroComprobante(): string
    {
        $fecha = date('Ymd');
        $stmt = $this->db->ejecutar(
            "SELECT COUNT(*) FROM devoluciones_proveedores WHERE numero_comprobante LIKE :prefix",
            ['prefix' => "DEV-$fecha-%"]
        );
        $num = (int) $stmt->fetchColumn() + 1;
        return "DEV-$fecha-" . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Anular devolución y restaurar stock
     */
    public function anular(int $id, int $usuarioId, string $motivo): void
    {
        // Obtener datos para restaurar stock
        $stmt = $this->db->ejecutar(
            "SELECT lote_id, cantidad FROM devoluciones_proveedores WHERE id = :id",
            ['id' => $id]
        );
        $dev = $stmt->fetch();
        
        if (!$dev) {
            throw new \RuntimeException('Devolución no encontrada.');
        }

        $this->db->iniciarTransaccion();
        
        try {
            // Restaurar stock
            $this->db->ejecutar(
                "UPDATE lotes_inventario SET cantidad_disponible = cantidad_disponible + :cant WHERE id = :lote_id",
                ['cant' => $dev['cantidad'], 'lote_id' => $dev['lote_id']]
            );

            // Registrar en Kardex
            require_once __DIR__ . '/../../config/db.php';
            $inv = new Inventario();
            $inv->registrarMovimientoKardex([
                'medicamento_id' => 0,
                'lote_id' => $dev['lote_id'],
                'tipo_movimiento' => 'Devolucion',
                'cantidad' => $dev['cantidad'],
                'stock_anterior' => 0,
                'stock_posterior' => $dev['cantidad'],
                'motivo' => 'Anulación devolución: ' . $motivo,
                'usuario_id' => $usuarioId,
            ]);

            $this->db->confirmar();
        } catch (\Exception $e) {
            $this->db->revertir();
            throw $e;
        }
    }

    /**
     * Buscar devolución por ID para generar PDF
     */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT d.*, p.rif, p.razon_social AS proveedor_nombre, p.direccion AS proveedor_direccion,
                    m.codigo AS med_codigo, m.nombre_generico, m.concentracion, m.presentacion,
                    l.numero_lote, l.fecha_vencimiento,
                    u.nombre AS usuario_nombre, u.apellido AS usuario_apellido
             FROM devoluciones_proveedores d
             LEFT JOIN proveedores p ON d.proveedor_id = p.id
             LEFT JOIN medicamentos m ON d.medicamento_id = m.id
             LEFT JOIN lotes_inventario l ON d.lote_id = l.id
             LEFT JOIN usuarios u ON d.registrado_por = u.id
             WHERE d.id = :id",
            ['id' => $id]
        );
        return $stmt->fetch() ?: null;
    }
}