<?php
/**
 * =====================================================
 * SIGFA - Modelo: Asegurado (Paciente)
 * =====================================================
 * Gestiona pacientes asegurados, incluyendo la alerta
 * de identidad para mayores de 10 años.
 * =====================================================
 */

require_once __DIR__ . '/../../config/db.php';

class Asegurado
{
    private Conexion $db;

    public function __construct()
    {
        $this->db = Conexion::obtenerInstancia();
    }

    /**
     * Buscar asegurado por cédula.
     */
    public function buscarPorCedula(string $cedula): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM asegurados WHERE cedula = :cedula AND estatus = 'Activo'",
            ['cedula' => $cedula]
        );
        $asegurado = $stmt->fetch();

        if ($asegurado) {
            // Verificar alerta de identidad al consultar
            $asegurado['alerta_identidad'] = $this->verificarAlertaIdentidad($asegurado);
            $asegurado['alerta_edad_partida'] = $this->verificarAlertaEdadPartida($asegurado);
        }

        return $asegurado ?: null;
    }

    /**
     * Buscar asegurado por cualquier identificador:
     *   - Cédula
     *   - Número de Historia Médica
     *   - Partida de Nacimiento
     *
     * @param string $identificador Término de búsqueda.
     * @return array|null  Datos del asegurado o null.
     */
    public function buscarPorIdentificador(string $identificador): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM asegurados 
             WHERE estatus = 'Activo' 
               AND (cedula = :t1 OR historia_medica = :t2 OR partida_nacimiento = :t3)
             LIMIT 1",
            ['t1' => $identificador, 't2' => $identificador, 't3' => $identificador]
        );
        $asegurado = $stmt->fetch();

        if ($asegurado) {
            $asegurado['alerta_identidad'] = $this->verificarAlertaIdentidad($asegurado);
            $asegurado['alerta_edad_partida'] = $this->verificarAlertaEdadPartida($asegurado);
        }

        return $asegurado ?: null;
    }

    /**
     * Buscar asegurado por ID.
     */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM asegurados WHERE id = :id",
            ['id' => $id]
        );
        $asegurado = $stmt->fetch();

        if ($asegurado) {
            $asegurado['alerta_identidad'] = $this->verificarAlertaIdentidad($asegurado);
            $asegurado['alerta_edad_partida'] = $this->verificarAlertaEdadPartida($asegurado);
        }

        return $asegurado ?: null;
    }

    /**
     * ALERTA DE IDENTIDAD: Verificar si el paciente tiene más de 10 años
     * y sus datos no han sido actualizados recientemente.
     * 
     * Regla: Si el paciente tiene > 10 años de edad y la última
     * actualización de datos fue hace más de 1 año (o nunca), 
     * se genera una alerta para solicitar actualización.
     *
     * @param array $asegurado Datos del asegurado.
     * @return array|null  Alerta o null si no aplica.
     */
    public function verificarAlertaIdentidad(array $asegurado): ?array
    {
        $fechaNacimiento = new \DateTime($asegurado['fecha_nacimiento']);
        $hoy = new \DateTime();
        $edad = $hoy->diff($fechaNacimiento)->y;

        // Solo aplica para mayores de 10 años
        if ($edad <= 10) {
            return null;
        }

        // Verificar si los datos han sido actualizados
        $ultimaActualizacion = $asegurado['fecha_ultima_actualizacion'] 
            ? new \DateTime($asegurado['fecha_ultima_actualizacion']) 
            : null;

        $necesitaActualizar = false;
        $motivo = '';

        if ($ultimaActualizacion === null) {
            $necesitaActualizar = true;
            $motivo = 'Los datos del paciente nunca han sido verificados/actualizados.';
        } elseif ($hoy->diff($ultimaActualizacion)->y >= 1) {
            $necesitaActualizar = true;
            $motivo = 'Los datos del paciente no han sido actualizados en más de 1 año.';
        }

        if ($necesitaActualizar && !(bool) $asegurado['datos_actualizados']) {
            return [
                'requiere_actualizacion' => true,
                'edad'                   => $edad,
                'motivo'                 => "⚠️ ALERTA DE IDENTIDAD: Paciente mayor de 10 años (edad: $edad). $motivo Solicite la actualización de datos y documentación de identidad.",
            ];
        }

        return null;
    }

    /**
     * ALERTA DE EDAD POR PARTIDA: Verificar si un paciente registrado
     * con partida de nacimiento ya cumplió 10 años y necesita actualizar
     * su identificación a número de cédula.
     *
     * @param array $asegurado Datos del asegurado.
     * @return array|null  Alerta o null si no aplica.
     */
    public function verificarAlertaEdadPartida(array $asegurado): ?array
    {
        // Solo aplica si el paciente tiene partida y NO tiene cédula
        $tienePartida = !empty($asegurado['partida_nacimiento']);
        $sinCedula = empty($asegurado['cedula']);

        if (!$tienePartida || !$sinCedula) {
            return null;
        }

        $fechaNacimiento = new \DateTime($asegurado['fecha_nacimiento']);
        $hoy = new \DateTime();
        $edad = $hoy->diff($fechaNacimiento)->y;

        if ($edad >= 10) {
            return [
                'requiere_cedula' => true,
                'edad'            => $edad,
                'motivo'          => "⚠️ ALERTA: Paciente con partida de nacimiento tiene {$edad} años. Se requiere actualizar a número de cédula de identidad.",
            ];
        }

        return null;
    }

    /**
     * Marcar los datos del asegurado como actualizados.
     */
    public function marcarDatosActualizados(int $aseguradoId): void
    {
        $this->db->ejecutar(
            "UPDATE asegurados 
             SET datos_actualizados = 1, fecha_ultima_actualizacion = NOW() 
             WHERE id = :id",
            ['id' => $aseguradoId]
        );
    }

    /**
     * Crear un nuevo asegurado.
     */
    public function crear(array $datos): int
    {
        $this->db->ejecutar(
            "INSERT INTO asegurados 
                (cedula, nombre, apellido, fecha_nacimiento, sexo, grupo_sanguineo,
                 historia_medica, estado, municipio, parroquia, direccion, 
                 telefono, telefono_familiar, correo, tipo_asegurado, 
                 datos_actualizados, fecha_ultima_actualizacion)
             VALUES 
                (:cedula, :nombre, :apellido, :fecha_nacimiento, :sexo, :grupo_sanguineo,
                 :historia_medica, :estado, :municipio, :parroquia, :direccion,
                 :telefono, :telefono_familiar, :correo, :tipo_asegurado,
                 :datos_actualizados, NOW())",
            [
                'datos_actualizados' => 1,
                'cedula'           => $datos['cedula'],
                'nombre'           => $datos['nombre'],
                'apellido'         => $datos['apellido'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'sexo'             => $datos['sexo'],
                'grupo_sanguineo'  => $datos['grupo_sanguineo'] ?? null,
                'historia_medica'  => (!empty($datos['historia_medica']) && strtolower($datos['historia_medica']) !== 'no') ? $datos['historia_medica'] : null,
                'estado'           => $datos['estado'] ?? null,
                'municipio'        => $datos['municipio'] ?? null,
                'parroquia'        => $datos['parroquia'] ?? null,
                'direccion'        => $datos['direccion'] ?? null,
                'telefono'         => $datos['telefono'] ?? null,
                'telefono_familiar'=> $datos['telefono_familiar'] ?? null,
                'correo'           => $datos['correo'] ?? null,
                'tipo_asegurado'   => $datos['tipo_asegurado'] ?? 'Titular',
            ]
        );
        return (int) $this->db->ultimoId();
    }

    /**
     * Actualizar datos de un asegurado.
     */
    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $params = ['id' => $id];

        $camposPermitidos = [
            'nombre', 'apellido', 'fecha_nacimiento', 'sexo', 'grupo_sanguineo',
            'historia_medica', 'estado', 'municipio', 'parroquia', 'direccion', 
            'telefono', 'telefono_familiar', 'correo', 'tipo_asegurado', 'estatus'
        ];

        foreach ($camposPermitidos as $campo) {
            if (array_key_exists($campo, $datos)) {
                $campos[] = "$campo = :$campo";
                $params[$campo] = $datos[$campo];
            }
        }

        if (empty($campos)) return false;

        // Marcar como actualizado automáticamente
        $campos[] = "datos_actualizados = 1";
        $campos[] = "fecha_ultima_actualizacion = NOW()";

        $sql = "UPDATE asegurados SET " . implode(', ', $campos) . " WHERE id = :id";
        $this->db->ejecutar($sql, $params);
        return true;
    }

    /**
     * Listar todos los asegurados activos.
     */
    public function listarActivos(): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT id, cedula, nombre, apellido, fecha_nacimiento, sexo, 
                    grupo_sanguineo, historia_medica, tipo_asegurado
             FROM asegurados 
             WHERE estatus = 'Activo' 
             ORDER BY apellido ASC, nombre ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Buscar asegurados por nombre, cédula, historia médica o partida.
     */
    public function buscarPorNombre(string $termino): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT * FROM asegurados 
             WHERE estatus = 'Activo' 
               AND (nombre LIKE :t1 OR apellido LIKE :t2 OR cedula LIKE :t3 
                    OR historia_medica LIKE :t4 OR partida_nacimiento LIKE :t5)
             ORDER BY apellido ASC",
            ['t1' => "%$termino%", 't2' => "%$termino%", 't3' => "%$termino%",
             't4' => "%$termino%", 't5' => "%$termino%"]
        );
        return $stmt->fetchAll();
    }

    /**
     * Obtener pacientes que requieren actualización de datos.
     */
    public function obtenerPendientesActualizacion(): array
    {
        $stmt = $this->db->ejecutar(
            "SELECT id, cedula, nombre, apellido, fecha_nacimiento,
                    TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad,
                    fecha_ultima_actualizacion
             FROM asegurados 
             WHERE estatus = 'Activo'
               AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) > 10
               AND (datos_actualizados = 0 
                    OR fecha_ultima_actualizacion IS NULL 
                    OR fecha_ultima_actualizacion < DATE_SUB(NOW(), INTERVAL 1 YEAR))
             ORDER BY fecha_ultima_actualizacion ASC"
        );
        return $stmt->fetchAll();
    }
}
