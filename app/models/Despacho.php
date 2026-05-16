<?php
/**
 * =====================================================
 * SIGFA - Modelo: Despacho
 * =====================================================
 * Gestiona los despachos de medicamentos, generación
 * de tickets únicos y validación de duplicados.
 * =====================================================
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/Inventario.php';
require_once __DIR__ . '/Asegurado.php';

class Despacho
{
    private Conexion $db;
    private Inventario $inventario;

    public function __construct()
    {
        $this->db = Conexion::obtenerInstancia();
        $this->inventario = new Inventario();
    }

    /**
     * Generar un UUID v4 pseudo-aleatorio.
     */
    private function generarUUID(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // =====================================================
    // VALIDACIONES (Refactorizado: por ítem / principio activo)
    // =====================================================

    /**
     * VALIDACIÓN POR ÍTEM (Principio Activo): Verificar si un medicamento con el mismo 
     * principio activo fue despachado al paciente en las últimas 24 horas.
     * Para medicamentos GENERALES: Soft Warning.
     *
     * @param int $aseguradoId
     * @param int $medicamentoId
     * @return array
     */
    public function verificarDuplicidadPorItem(int $aseguradoId, int $medicamentoId): array
    {
        // Obtener el principio activo del medicamento actual
        $stmtPa = $this->db->ejecutar("SELECT id_principio_activo FROM medicamentos WHERE id = :id", ['id' => $medicamentoId]);
        $pa = $stmtPa->fetch();
        $paId = $pa['id_principio_activo'] ?? null;

        if (!$paId) {
            // Si no tiene principio activo, validar por medicamento_id (fallback)
            $whereClause = "dd.medicamento_id = :med_id";
            $params = ['asegurado_id' => $aseguradoId, 'med_id' => $medicamentoId];
        } else {
            $whereClause = "m.id_principio_activo = :pa_id";
            $params = ['asegurado_id' => $aseguradoId, 'pa_id' => $paId];
        }

        $sql = "SELECT d.ticket, d.fecha_despacho, dd.cantidad, m.nombre_generico
                FROM despachos d
                INNER JOIN despacho_detalle dd ON d.id = dd.despacho_id
                INNER JOIN medicamentos m ON dd.medicamento_id = m.id
                WHERE d.asegurado_id = :asegurado_id
                  AND $whereClause
                  AND d.estatus != 'Anulado'
                  AND d.fecha_despacho >= (CURRENT_TIMESTAMP - INTERVAL 24 HOUR)
                ORDER BY d.fecha_despacho DESC
                LIMIT 1";

        $stmt = $this->db->ejecutar($sql, $params);
        $resultado = $stmt->fetch();

        if ($resultado) {
            return [
                'advertencia' => true,
                'mensaje'     => "⚠️ El paciente ya recibió '{$resultado['nombre_generico']}' (mismo principio activo) en las últimas 24h (Ticket: {$resultado['ticket']}). ¿Desea autorizar una dosis adicional de emergencia?",
                'ticket'      => $resultado['ticket'],
            ];
        }

        return ['advertencia' => false, 'mensaje' => 'OK', 'ticket' => null];
    }

    /**
     * Verificar bloqueo por ciclo dinámico para medicamentos de Alto Costo / Controlados.
     * Basado en el Principio Activo.
     *
     * @param int $aseguradoId
     * @param int $medicamentoId
     * @return array
     */
    public function verificarRecurrencia(int $aseguradoId, int $medicamentoId): array
    {
        // Obtener info del medicamento
        $stmtMed = $this->db->ejecutar(
            "SELECT m.tipo_medicamento, m.id_principio_activo, gm.codigo AS grupo_codigo
             FROM medicamentos m
             LEFT JOIN grupos_medicamentos gm ON m.grupo_id = gm.id
             WHERE m.id = :id LIMIT 1",
            ['id' => $medicamentoId]
        );
        $medInfo = $stmtMed->fetch();
        $paId = $medInfo['id_principio_activo'] ?? null;
        $grupoCod = $medInfo['grupo_codigo'] ?? '';
        $tipoMed = $medInfo['tipo_medicamento'] ?? 'General';

        $esControlado = in_array($grupoCod, ['003', '004', '005']) || $tipoMed === 'Alto_Costo';
        if (!$esControlado) {
            return ['bloqueado' => false, 'faltan' => 0, 'fecha_disponible' => null];
        }

        if (!$paId) {
            $whereClause = "dd.medicamento_id = :med_id";
            $params = ['asegurado_id' => $aseguradoId, 'med_id' => $medicamentoId];
        } else {
            $whereClause = "m.id_principio_activo = :pa_id";
            $params = ['asegurado_id' => $aseguradoId, 'pa_id' => $paId];
        }

        // Buscar último despacho de este principio activo
        $sql = "SELECT d.fecha_despacho, dd.ciclo_asignado, dd.fecha_proxima
                FROM despachos d
                INNER JOIN despacho_detalle dd ON d.id = dd.despacho_id
                INNER JOIN medicamentos m ON dd.medicamento_id = m.id
                WHERE d.asegurado_id = :asegurado_id 
                  AND $whereClause
                  AND d.estatus = 'Despachado'
                ORDER BY d.fecha_despacho DESC LIMIT 1";

        $stmtUltimo = $this->db->ejecutar($sql, $params);
        $ultimo = $stmtUltimo->fetch();

        if ($ultimo) {
            $hoy = new \DateTime();
            $fechaDisponible = null;

            if (!empty($ultimo['fecha_proxima'])) {
                $fechaDisponible = new \DateTime($ultimo['fecha_proxima']);
            } else {
                $cicloAnterior = (int) ($ultimo['ciclo_asignado'] ?? 21);
                $fechaDespachoAnterior = new \DateTime($ultimo['fecha_despacho']);
                $fechaDisponible = (clone $fechaDespachoAnterior)->modify("+{$cicloAnterior} days");
            }

            if ($hoy < $fechaDisponible) {
                $faltan = (int) $hoy->diff($fechaDisponible)->days;
                return [
                    'bloqueado'        => true,
                    'faltan'           => $faltan,
                    'fecha_disponible' => $fechaDisponible->format('d/m/Y'),
                    'mensaje'          => "🚫 Medicamento Bloqueado: Entrega vigente. Disponible a partir del {$fechaDisponible->format('d/m/Y')}."
                ];
            }
        }

        return ['bloqueado' => false, 'faltan' => 0, 'fecha_disponible' => null];
    }

    // =====================================================
    // GENERACIÓN DE TICKET ÚNICO
    // =====================================================

    /**
     * Generar un ticket único de despacho.
     * Formato: DSP-YYYYMMDD-####
     *
     * @return string  Ticket generado (Ej: DSP-20260326-0001)
     */
    public function generarTicket(): string
    {
        $fecha = date('Ymd');

        $stmt = $this->db->ejecutar(
            "SELECT MAX(CAST(SUBSTRING(ticket, 14) AS UNSIGNED)) AS ultimo
             FROM despachos 
             WHERE ticket LIKE :patron",
            ['patron' => "DSP-$fecha-%"]
        );
        $resultado = $stmt->fetch();
        $secuencia = ((int) ($resultado['ultimo'] ?? 0)) + 1;

        return sprintf('DSP-%s-%04d', $fecha, $secuencia);
    }

    // =====================================================
    // OPERACIONES CRUD
    // =====================================================

    /**
     * Crear un nuevo despacho completo con sus detalles.
     * Aplica validación de duplicidad, genera ticket único y
     * descuenta el inventario por FIFO.
     *
     * @param array $cabecera  Datos del despacho (asegurado_id, medico_id, diagnostico, etc.)
     * @param array $detalles  Array de ['medicamento_id' => int, 'cantidad' => int]
     * @param int   $usuarioId ID del usuario que despacha.
     * @return array  Resultado con ticket, ID del despacho y lotes consumidos.
     * @throws \RuntimeException Si hay duplicidad o stock insuficiente.
     */
    public function crearDespacho(array $cabecera, array $detalles, int $usuarioId): array
    {
        // 1. Verificar duplicidad informativa (ya no bloquea globalmente)
        // La validación real es por ítem, manejada abajo.

        // 2. Obtener rol del usuario que despacha
        $stmtUsr = $this->db->ejecutar(
            "SELECT rol FROM usuarios WHERE id = :id LIMIT 1",
            ['id' => $usuarioId]
        );
        $rolUsuario = $stmtUsr->fetch()['rol'] ?? '';

        // 3. Validar cada medicamento por ítem
        $detallesValidos = [];
        $itemsBloqueados = [];

        foreach ($detalles as $detalle) {
            $medId = $detalle['medicamento_id'];
            $cantidad = $detalle['cantidad'];
            $cantidadRecetada = $detalle['cantidad_recetada'] ?? $cantidad;
            $cicloAsignado = $detalle['ciclo_asignado'] ?? null;

            // Obtener info del medicamento
            $stmtMed = $this->db->ejecutar(
                "SELECT m.tipo_medicamento, m.nombre_generico, gm.codigo AS grupo_codigo
                 FROM medicamentos m
                 LEFT JOIN grupos_medicamentos gm ON m.grupo_id = gm.id
                 WHERE m.id = :id LIMIT 1",
                ['id' => $medId]
            );
            $medInfo = $stmtMed->fetch();
            $tipoMed = $medInfo['tipo_medicamento'] ?? 'General';
            $grupoCod = $medInfo['grupo_codigo'] ?? '';

            // Validar acceso de Alto Costo por rol
            if ($tipoMed === 'Alto_Costo' && !in_array($rolUsuario, ['Auxiliar_Alto_Costo', 'Administrador'])) {
                throw new \RuntimeException(
                    "Acceso Denegado: Solo el personal de Alto Costo puede procesar '{$medInfo['nombre_generico']}'."
                );
            }

            $esControlado = in_array($grupoCod, ['003', '004', '005']) || $tipoMed === 'Alto_Costo';

            if ($esControlado) {
                // HARD BLOCK: Verificar ciclo dinámico para controlados/alto costo
                $checkRecurrencia = $this->verificarRecurrencia($cabecera['asegurado_id'], $medId);
                if ($checkRecurrencia['bloqueado']) {
                    $faltan = $checkRecurrencia['faltan'];
                    if (in_array($rolUsuario, ['Administrador', 'Farmaceutico'])) {
                        // Admin/Farmacéutico pueden saltar con anotación
                        $cabecera['observaciones'] = ($cabecera['observaciones'] ?? '') . " [Salto de bloqueo por $rolUsuario en {$medInfo['nombre_generico']}: Faltaban $faltan días]";
                        $detalle['override_bloqueo'] = true;
                    } else {
                        // Bloqueo de ítem — no detiene el despacho completo
                        $itemsBloqueados[] = [
                            'medicamento_id'   => $medId,
                            'nombre'           => $medInfo['nombre_generico'],
                            'mensaje'          => $checkRecurrencia['mensaje'],
                            'fecha_disponible' => $checkRecurrencia['fecha_disponible'] ?? null,
                        ];
                        continue; // Salta este ítem, sigue con los demás
                    }
                }
            } else {
                // SOFT WARNING: Para medicamentos generales, verificar 24h
                // (la confirmación del usuario se maneja en el frontend)
                $check24h = $this->verificarDuplicidadPorItem($cabecera['asegurado_id'], $medId);
                if ($check24h['advertencia'] && empty($detalle['confirmar_emergencia'])) {
                    $itemsBloqueados[] = [
                        'medicamento_id' => $medId,
                        'nombre'         => $medInfo['nombre_generico'],
                        'tipo_bloqueo'   => 'ADVERTENCIA_24H',
                        'mensaje'        => $check24h['mensaje'],
                    ];
                    continue;
                }
            }

            $detalle['ciclo_asignado'] = $cicloAsignado;
            $detalle['cantidad_recetada'] = $cantidadRecetada;
            $detallesValidos[] = $detalle;
        }

        // Si hay ítems bloqueados y no quedan válidos, reportar
        if (empty($detallesValidos) && !empty($itemsBloqueados)) {
            $msgs = array_map(fn($b) => $b['mensaje'], $itemsBloqueados);
            throw new \RuntimeException(implode("\n", $msgs));
        }

        // 4. Calcular edad_paciente automáticamente
        $stmtPac = $this->db->ejecutar(
            "SELECT fecha_nacimiento FROM asegurados WHERE id = :id LIMIT 1",
            ['id' => $cabecera['asegurado_id']]
        );
        $paciente = $stmtPac->fetch();
        $edadPaciente = null;
        if ($paciente && !empty($paciente['fecha_nacimiento'])) {
            $nacimiento = new \DateTime($paciente['fecha_nacimiento']);
            $hoy = new \DateTime();
            $edadPaciente = (int) $nacimiento->diff($hoy)->y;
        }

        $this->db->iniciarTransaccion();

        try {
            // 5. Generar ticket y UUID único
            $ticket = $this->generarTicket();
            $uuid   = $this->generarUUID();
            $montoTotal = 0.00;

            // 6. Insertar cabecera del despacho
            $this->db->ejecutar(
                "INSERT INTO despachos (uuid, ticket, asegurado_id, medico_id, servicio_id, diagnostico, patologia_id, edad_paciente, estatus, observaciones, despachado_por, monto_total)
                 VALUES (:uuid, :ticket, :asegurado_id, :medico_id, :servicio_id, :diagnostico, :patologia_id, :edad_paciente, 'Despachado', :observaciones, :despachado_por, :monto_total)",
                [
                    'uuid'           => $uuid,
                    'ticket'         => $ticket,
                    'asegurado_id'   => $cabecera['asegurado_id'],
                    'medico_id'      => $cabecera['medico_id'] ?? null,
                    'servicio_id'    => $cabecera['servicio_id'] ?? null,
                    'diagnostico'    => $cabecera['diagnostico'] ?? null,
                    'patologia_id'   => $cabecera['patologia_id'] ?? null,
                    'edad_paciente'  => $edadPaciente,
                    'observaciones'  => $cabecera['observaciones'] ?? null,
                    'despachado_por' => $usuarioId,
                    'monto_total'    => $montoTotal,
                ]
            );
            $despachoId = (int) $this->db->ultimoId();

            // 7. Procesar cada detalle válido con FIFO
            $lotesConsumidos = [];
            foreach ($detallesValidos as $detalle) {
                $lotes = $this->inventario->descontarFIFO(
                    $detalle['medicamento_id'],
                    $detalle['cantidad'],
                    $usuarioId,
                    $despachoId
                );

                foreach ($lotes as $lote) {
                    $stmtPrecio = $this->db->ejecutar("SELECT precio_unitario FROM lotes_inventario WHERE id = :id", ['id' => $lote['lote_id']]);
                    $precioLote = (float)($stmtPrecio->fetch()['precio_unitario'] ?? 0);
                    $montoTotal += ($precioLote * $lote['cantidad']);

                    // Calcular fecha_proxima si hay ciclo asignado o fecha explícita
                    $fechaProxima = null;
                    if (!empty($detalle['fecha_proxima'])) {
                        $fechaProxima = $detalle['fecha_proxima'];
                    } elseif (!empty($detalle['ciclo_asignado'])) {
                        $fechaProxima = date('Y-m-d', strtotime("+{$detalle['ciclo_asignado']} days"));
                    }

                    // Insertar detalle CON ciclo y cantidad recetada
                    $this->db->ejecutar(
                        "INSERT INTO despacho_detalle (despacho_id, medicamento_id, lote_id, cantidad, precio_unitario, cantidad_recetada, ciclo_asignado, fecha_proxima)
                         VALUES (:despacho_id, :medicamento_id, :lote_id, :cantidad, :precio, :cantidad_recetada, :ciclo_asignado, :fecha_proxima)",
                        [
                            'despacho_id'      => $despachoId,
                            'medicamento_id'   => $detalle['medicamento_id'],
                            'lote_id'          => $lote['lote_id'],
                            'cantidad'         => $lote['cantidad'],
                            'precio'           => $precioLote,
                            'cantidad_recetada' => $detalle['cantidad_recetada'] ?? null,
                            'ciclo_asignado'   => $detalle['ciclo_asignado'] ?? null,
                            'fecha_proxima'    => $fechaProxima,
                        ]
                    );
                    // NOTA: descontarFIFO() ya actualiza lotes_inventario.
                    // NO hacer UPDATE adicional aquí (fix del bug de doble descuento).
                }

                $lotesConsumidos[] = [
                    'medicamento_id' => $detalle['medicamento_id'],
                    'cantidad_total' => $detalle['cantidad'],
                    'lotes'          => $lotes,
                ];

                // Registrar override si hubo salto de bloqueo
                if (!empty($detalle['override_bloqueo'])) {
                    $this->registrarOverride($despachoId, $detalle['medicamento_id'], $cabecera['asegurado_id'], 'SALTO_BLOQUEO', $cabecera['observaciones'] ?? '', $usuarioId);
                }
            }

            // 8. Actualizar el monto total final
            $this->db->ejecutar(
                "UPDATE despachos SET monto_total = :monto, total_articulos = :total WHERE id = :id",
                ['monto' => $montoTotal, 'total' => count($detallesValidos), 'id' => $despachoId]
            );

            $this->db->confirmar();

            return [
                'despacho_id'      => $despachoId,
                'ticket'           => $ticket,
                'lotes_consumidos' => $lotesConsumidos,
                'items_bloqueados' => $itemsBloqueados,
            ];

        } catch (\Exception $e) {
            $this->db->revertir();
            throw $e;
        }
    }

    /**
     * Registrar un override/salto de advertencia en la tabla de logs.
     */
    private function registrarOverride(int $despachoId, int $medicamentoId, int $aseguradoId, string $tipo, string $motivo, int $usuarioId): void
    {
        try {
            $this->db->ejecutar(
                "INSERT INTO logs_override_despacho (despacho_id, medicamento_id, asegurado_id, tipo_override, motivo, usuario_id, ip_address)
                 VALUES (:despacho_id, :med_id, :aseg_id, :tipo, :motivo, :usu_id, :ip)",
                [
                    'despacho_id' => $despachoId,
                    'med_id'      => $medicamentoId,
                    'aseg_id'     => $aseguradoId,
                    'tipo'        => $tipo,
                    'motivo'      => $motivo,
                    'usu_id'      => $usuarioId,
                    'ip'          => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                ]
            );
        } catch (\Exception $e) {
            error_log('Error registrando override: ' . $e->getMessage());
        }
    }

    /**
     * Buscar despacho por su ticket único.
     */
    public function buscarPorTicket(string $ticket): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT d.*, 
                    a.cedula AS paciente_cedula, a.nombre AS paciente_nombre, a.apellido AS paciente_apellido,
                    m.nombre AS medico_nombre, m.apellido AS medico_apellido, m.codigo_mpps,
                    u.nombre AS despachador_nombre, u.apellido AS despachador_apellido
             FROM despachos d
             INNER JOIN asegurados a ON d.asegurado_id = a.id
             LEFT JOIN medicos m ON d.medico_id = m.id
             LEFT JOIN usuarios u ON d.despachado_por = u.id
             WHERE d.ticket = :ticket",
            ['ticket' => $ticket]
        );
        $despacho = $stmt->fetch();

        if (!$despacho) return null;

        // Obtener detalles del despacho
        $stmt = $this->db->ejecutar(
            "SELECT dd.*, med.nombre_generico, med.concentracion, med.presentacion,
                    l.numero_lote, l.fecha_vencimiento
             FROM despacho_detalle dd
             INNER JOIN medicamentos med ON dd.medicamento_id = med.id
             INNER JOIN lotes_inventario l ON dd.lote_id = l.id
             WHERE dd.despacho_id = :despacho_id",
            ['despacho_id' => $despacho['id']]
        );
        $despacho['detalles'] = $stmt->fetchAll();

        return $despacho;
    }

    /**
     * Listar despachos del día actual.
     */
    public function listarDespachosHoy(): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT d.id, d.ticket, d.fecha_despacho, d.estatus,
                    a.cedula AS paciente_cedula, 
                    CONCAT(a.nombre, ' ', a.apellido) AS paciente_nombre,
                    u.nombre AS despachador_nombre
             FROM despachos d
             INNER JOIN asegurados a ON d.asegurado_id = a.id
             LEFT JOIN usuarios u ON d.despachado_por = u.id
             WHERE DATE(d.fecha_despacho) = CURDATE()
             ORDER BY d.fecha_despacho DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Contar despachos del día.
     */
    public function contarDespachosHoy(): int
    {
        $stmt = $this->db->ejecutar(
            "SELECT COUNT(*) AS total FROM despachos 
             WHERE DATE(fecha_despacho) = CURDATE() AND estatus != 'Anulado'"
        );
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Anular un despacho CON reversión de inventario.
     * Devuelve las cantidades despachadas a los lotes originales
     * y registra cada reversión en el Kardex de auditoría.
     *
     * @param int    $despachoId ID del despacho a anular.
     * @param int    $usuarioId  ID del administrador que anula.
     * @param string $motivo     Motivo de la anulación.
     * @return bool
     * @throws \RuntimeException Si el despacho no existe o ya fue anulado.
     */
    public function anularDespacho(int $despachoId, int $usuarioId, string $motivo = ''): bool
    {
        // 1. Verificar que el despacho existe y no está anulado
        $stmtDespacho = $this->db->ejecutar(
            "SELECT id, ticket, estatus FROM despachos WHERE id = :id LIMIT 1",
            ['id' => $despachoId]
        );
        $despacho = $stmtDespacho->fetch();

        if (!$despacho) {
            throw new \RuntimeException('El despacho no existe.');
        }
        if ($despacho['estatus'] === 'Anulado') {
            throw new \RuntimeException('Este despacho ya fue anulado anteriormente.');
        }

        $this->db->iniciarTransaccion();

        try {
            // 2. Obtener detalles del despacho para revertir
            $stmtDetalles = $this->db->ejecutar(
                "SELECT dd.medicamento_id, dd.lote_id, dd.cantidad
                 FROM despacho_detalle dd
                 WHERE dd.despacho_id = :despacho_id",
                ['despacho_id' => $despachoId]
            );
            $detalles = $stmtDetalles->fetchAll();

            // 3. Revertir cada línea: devolver stock al lote original
            foreach ($detalles as $det) {
                // Devolver cantidad al lote
                $this->db->ejecutar(
                    "UPDATE lotes_inventario SET cantidad_disponible = cantidad_disponible + :cantidad WHERE id = :lote_id",
                    ['cantidad' => $det['cantidad'], 'lote_id' => $det['lote_id']]
                );

                // Obtener stock actualizado para el Kardex
                $stockPosterior = $this->inventario->obtenerStockTotalMedicamento($det['medicamento_id']);

                // Registrar la devolución en el Kardex
                $this->inventario->registrarMovimientoKardex([
                    'medicamento_id'   => $det['medicamento_id'],
                    'lote_id'          => $det['lote_id'],
                    'tipo_movimiento'  => 'Devolucion',
                    'cantidad'         => $det['cantidad'],
                    'stock_anterior'   => $stockPosterior - $det['cantidad'],
                    'stock_posterior'  => $stockPosterior,
                    'referencia_tipo'  => 'anulacion_despacho',
                    'referencia_id'    => $despachoId,
                    'operacion'        => 'Anulación',
                    'motivo'           => "Anulación Despacho #{$despacho['ticket']}",
                    'observacion'      => $motivo,
                    'usuario_id'       => $usuarioId,
                ]);
            }

            // 4. Marcar el despacho como Anulado con datos de auditoría
            $this->db->ejecutar(
                "UPDATE despachos 
                 SET estatus = 'Anulado', 
                     anulado_por = :anulado_por,
                     motivo_anulacion = :motivo,
                     fecha_anulacion = NOW(),
                     observaciones = CONCAT(COALESCE(observaciones, ''), '\n[ANULADO por usuario #', :uid, '] ', :motivo2)
                 WHERE id = :id",
                [
                    'anulado_por' => $usuarioId,
                    'motivo'      => $motivo,
                    'uid'         => $usuarioId,
                    'motivo2'     => $motivo,
                    'id'          => $despachoId,
                ]
            );

            $this->db->confirmar();
            return true;

        } catch (\Exception $e) {
            $this->db->revertir();
            throw $e;
        }
    }
}
