<?php
/**
 * =====================================================
 * SIGFA - Middleware: Autenticación y Permisos
 * =====================================================
 * Gestiona seguridad, roles y permisos por módulo.
 * =====================================================
 */

class AuthMiddleware
{
    /**
     * Roles válidos en el sistema.
     */
    public const ROLES = [
        'Administrador',
        'Auxiliar_General',
        'Auxiliar_Alto_Costo',
        'Almacenista',
        'Farmaceutico',
        'Kardista'
    ];

    /**
     * Grupos de medicamentos restringidos (solo Aux. Alto Costo y Farmacéutico).
     */
    public const GRUPOS_RESTRINGIDOS = [3, 4, 5]; // Estupefacientes, Psicotrópicos, Crónicos

    /**
     * Verificar si el usuario está autenticado.
     */
    public static function verificarAutenticacion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Verificar rol específico.
     */
    public static function verificarRol(string ...$rolesPermitidos): void
    {
        self::verificarAutenticacion();

        $rolUsuario = $_SESSION['usuario_rol'] ?? '';

        if (!in_array($rolUsuario, $rolesPermitidos)) {
            $_SESSION['error_permisos'] = 'No tienes permiso para acceder a este módulo.';
            header('Location: index.php?url=dashboard');
            exit;
        }
    }

    /**
     * Verificar si puede despachar medicamentos de grupos restringidos.
     */
    public static function puedeDespacharGrupo(int $grupoId): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';

        // Administrador, Auxiliar_Alto_Costo y Farmacéutico pueden despachar cualquier grupo
        if (in_array($rol, ['Administrador', 'Auxiliar_Alto_Costo', 'Farmaceutico'])) {
            return true;
        }

        // Auxiliar_General NO puede despachar grupos restringidos
        if ($rol === 'Auxiliar_General' && in_array($grupoId, self::GRUPOS_RESTRINGIDOS)) {
            return false;
        }

        // Almacenista no puede despachar nada
        if ($rol === 'Almacenista') {
            return false;
        }

        return true;
    }

    /**
     * Obtener mensaje de denegación para despacho.
     */
    public static function getMensajeDenegacionDespacho(): string
    {
        $rol = $_SESSION['usuario_rol'] ?? '';

        if ($rol === 'Almacenista') {
            return 'El Almacenista no tiene permisos para despachar medicamentos. Esta función es exclusiva de Auxiliares de Farmacia o Alto Costo.';
        }

        if ($rol === 'Auxiliar_General') {
            return 'Acceso denegado: este medicamento solo puede ser despachado por Auxiliar de Alto Costo o Farmacéutico.';
        }

        return 'No tienes permiso para realizar despachos.';
    }

    /**
     * Verificar permiso para modificar inventario manualmente.
     */
    public static function puedeModificarInventario(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return $rol === 'Administrador';
    }

    /**
     * Verificar permiso para ajustar inventario.
     */
    public static function puedeAjustarInventario(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return $rol === 'Administrador';
    }

    /**
     * Verificar permiso para anular transacciones.
     */
    public static function puedeAnularTransaccion(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return $rol === 'Administrador';
    }

    /**
     * Verificar permiso para gestionar usuarios.
     */
    public static function puedeGestionarUsuarios(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return $rol === 'Administrador';
    }

    /**
     * Verificar permiso para gestionar medicamentos.
     */
    public static function puedeGestionarMedicamentos(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return in_array($rol, ['Administrador', 'Farmaceutico']);
    }

    /**
     * Verificar permiso para registrar entradas de inventario.
     */
    public static function puedeRegistrarEntradas(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return in_array($rol, ['Administrador', 'Almacenista', 'Farmaceutico']);
    }

    /**
     * Verificar permiso para realizar despachos.
     */
    public static function puedeRealizarDespachos(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return in_array($rol, ['Administrador', 'Auxiliar_General', 'Auxiliar_Alto_Costo', 'Farmaceutico']);
    }

    /**
     * Verificar permiso para ver reportes de pacientes.
     */
    public static function puedeVerReportesPacientes(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        // Kardista solo puede ver, no realizar acciones
        return in_array($rol, ['Administrador', 'Auxiliar_General', 'Auxiliar_Alto_Costo', 'Farmaceutico', 'Kardista']);
    }

    /**
     * Verificar permiso para ver reportes de inventario/logística.
     */
    public static function puedeVerReportesInventario(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return in_array($rol, ['Administrador', 'Almacenista', 'Farmaceutico', 'Kardista']);
    }

    /**
     * Verificar permiso para realizar transferencias.
     */
    public static function puedeRealizarTransferencias(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return in_array($rol, ['Administrador', 'Almacenista', 'Farmaceutico']);
    }

    /**
     * Verificar permiso para gestionar proveedores.
     */
    public static function puedeGestionarProveedores(): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';
        return in_array($rol, ['Administrador', 'Almacenista', 'Farmaceutico']);
    }

    /**
     * Renderizar error de permiso y terminar.
     */
    public static function mostrarErrorPermiso(string $mensaje): void
    {
        $_SESSION['inventario_error'] = $mensaje;
        header('Location: index.php?url=dashboard');
        exit;
    }

    /**
     * Verificar acceso a módulo específico del menú.
     * Retorna true si tiene acceso, false si no.
     */
    public static function tieneAccesoModulo(string $modulo): bool
    {
        $rol = $_SESSION['usuario_rol'] ?? '';

        $permisos = [
            'Administrador' => ['dashboard', 'usuarios', 'asegurados', 'medicos', 'medicamentos', 'grupos', 'proveedores', 'almacenes', 'servicios', 'patologias', 'inventario', 'despachos', 'transferencias', 'devoluciones', 'reportes', 'ajuste', 'anular', 'kardex'],
            'Auxiliar_General' => ['dashboard', 'asegurados', 'medicos', 'medicamentos', 'proveedores', 'servicios', 'patologias', 'inventario', 'despachos', 'reportes', 'kardex'],
            'Auxiliar_Alto_Costo' => ['dashboard', 'asegurados', 'medicos', 'medicamentos', 'proveedores', 'servicios', 'patologias', 'inventario', 'despachos', 'reportes', 'kardex'],
            'Almacenista' => ['dashboard', 'proveedores', 'almacenes', 'servicios', 'patologias', 'inventario', 'transferencias', 'devoluciones', 'reportes', 'kardex'],
            'Farmaceutico' => ['dashboard', 'medicamentos', 'grupos', 'proveedores', 'almacenes', 'servicios', 'patologias', 'inventario', 'despachos', 'transferencias', 'devoluciones', 'reportes', 'kardex'],
            'Kardista' => ['dashboard', 'reportes', 'kardex']
        ];

        return isset($permisos[$rol]) && in_array($modulo, $permisos[$rol]);
    }
}