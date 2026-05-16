<?php
/** SIGFA - Controlador genérico para módulos CRUD simples */
require_once __DIR__ . '/../models/Asegurado.php';
require_once __DIR__ . '/../models/Medicamento.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Despacho.php';

class ModuloController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }
    }

    // ===================== ASEGURADOS =====================
    public function listarAsegurados(): void
    {
        $modelo = new Asegurado();
        $asegurados = $modelo->listarActivos();
        $exito = $_SESSION['modulo_exito'] ?? null;
        $error = $_SESSION['modulo_error'] ?? null;
        unset($_SESSION['modulo_exito'], $_SESSION['modulo_error']);
        require_once __DIR__ . '/../views/asegurados/index.php';
    }

    public function crearAsegurado(): void
    {
        $this->validarCSRF();
        try {
            $modelo = new Asegurado();
            
            // Verificación previa de cédula
            $cedula = $_POST['cedula'] ?? '';
            if ($modelo->buscarPorCedula($cedula)) {
                throw new \RuntimeException("La cédula $cedula ya está registrada.");
            }

            // Verificación previa de historia médica (si se proporciona y no es "No")
            $historia = $_POST['historia_medica'] ?? '';
            if (!empty($historia) && strtolower($historia) !== 'no') {
                $stmt = Conexion::obtenerInstancia()->ejecutar(
                    "SELECT 1 FROM asegurados WHERE historia_medica = :h AND estatus = 'Activo' LIMIT 1",
                    ['h' => $historia]
                );
                if ($stmt->fetch()) {
                    throw new \RuntimeException("El número de historia $historia ya está asignado a otro paciente.");
                }
            }

            $modelo->crear($_POST);
            $_SESSION['modulo_exito'] = '✅ Asegurado registrado exitosamente.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }
        header('Location: index.php?url=asegurados');
        exit;
    }

    // ===================== MEDICAMENTOS =====================
    public function listarMedicamentos(): void
    {
        $modelo = new Medicamento();
        $medicamentos = $modelo->listarConStock();
        $exito = $_SESSION['modulo_exito'] ?? null;
        $error = $_SESSION['modulo_error'] ?? null;
        unset($_SESSION['modulo_exito'], $_SESSION['modulo_error']);
        require_once __DIR__ . '/../views/medicamentos/index.php';
    }

    public function crearMedicamento(): void
    {
        $this->validarCSRF();
        try {
            $modelo = new Medicamento();
            
            // Verificación de código duplicado
            $codigo = $_POST['codigo'] ?? '';
            $existente = $modelo->buscarPorCodigo($codigo);
            if ($existente) {
                throw new \RuntimeException("El medicamento con el código $codigo ya existe.");
            }

            $modelo->crear($_POST);
            $_SESSION['modulo_exito'] = '✅ Medicamento registrado exitosamente.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }
        header('Location: index.php?url=medicamentos');
        exit;
    }

    // ===================== DESPACHOS =====================
    public function listarDespachos(): void
    {
        $despacho = new Despacho();
        $medicamento = new Medicamento();
        $medico = new Medico();
        $despachos = $despacho->listarDespachosHoy();
        $medicamentos = $medicamento->listarConStock();
        $medicos = $medico->listarActivos();
        $exito = $_SESSION['modulo_exito'] ?? null;
        $error = $_SESSION['modulo_error'] ?? null;
        unset($_SESSION['modulo_exito'], $_SESSION['modulo_error']);
        require_once __DIR__ . '/../views/despachos/index.php';
    }

    public function crearDespacho(): void
    {
        $this->validarCSRF();
        try {
            $modelo = new Despacho();
            $aseguradoId = (int)($_POST['asegurado_id'] ?? 0);
            if ($aseguradoId <= 0) throw new \RuntimeException('Seleccione un paciente válido.');

            $detalles = [];
            $medicamentosIds = $_POST['medicamento_id'] ?? [];
            $cantidades = $_POST['cantidad'] ?? [];
            for ($i = 0; $i < count($medicamentosIds); $i++) {
                $mid = (int)($medicamentosIds[$i] ?? 0);
                $cant = (int)($cantidades[$i] ?? 0);
                if ($mid > 0 && $cant > 0) $detalles[] = ['medicamento_id' => $mid, 'cantidad' => $cant];
            }
            if (empty($detalles)) throw new \RuntimeException('Agregue al menos un medicamento.');

            $resultado = $modelo->crearDespacho(
                ['asegurado_id' => $aseguradoId, 'medico_id' => !empty($_POST['medico_id']) ? (int)$_POST['medico_id'] : null, 'diagnostico' => $_POST['diagnostico'] ?? ''],
                $detalles,
                $_SESSION['usuario_id']
            );
            $_SESSION['modulo_exito'] = "✅ Despacho creado. Ticket: {$resultado['ticket']}";
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = $e->getMessage();
        }
        header('Location: index.php?url=despachos');
        exit;
    }

    // ===================== MÉDICOS =====================
    public function listarMedicos(): void
    {
        $modelo = new Medico();
        $medicos = $modelo->listarActivos();
        $exito = $_SESSION['modulo_exito'] ?? null;
        $error = $_SESSION['modulo_error'] ?? null;
        unset($_SESSION['modulo_exito'], $_SESSION['modulo_error']);
        require_once __DIR__ . '/../views/medicos/index.php';
    }

    public function crearMedico(): void
    {
        $this->validarCSRF();
        try {
            $modelo = new Medico();
            
            // Verificación de duplicidad
            if ($modelo->buscarPorCedula($_POST['cedula'] ?? '')) {
                throw new \RuntimeException("Ya existe un médico registrado con esa cédula.");
            }

            $modelo->crear($_POST);
            $_SESSION['modulo_exito'] = '✅ Médico registrado exitosamente.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }
        header('Location: index.php?url=medicos');
        exit;
    }

    // ===================== PROVEEDORES =====================
    public function listarProveedores(): void
    {
        $modelo = new Proveedor();
        $proveedores = $modelo->listarActivos();
        $exito = $_SESSION['modulo_exito'] ?? null;
        $error = $_SESSION['modulo_error'] ?? null;
        unset($_SESSION['modulo_exito'], $_SESSION['modulo_error']);
        require_once __DIR__ . '/../views/proveedores/index.php';
    }

    public function crearProveedor(): void
    {
        $this->validarCSRF();
        try {
            $modelo = new Proveedor();
            
            // Verificación previa de RIF
            $rif = $_POST['rif'] ?? '';
            if ($modelo->buscarPorRIF($rif)) {
                throw new \RuntimeException("El proveedor con RIF $rif ya existe.");
            }

            $modelo->crear($_POST);
            $_SESSION['modulo_exito'] = '✅ Proveedor registrado exitosamente.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }
        header('Location: index.php?url=proveedores');
        exit;
    }

    public function editarProveedor(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $modelo = new Proveedor();
        $proveedor = $modelo->buscarPorId($id);
        
        if (!$proveedor) {
            $_SESSION['modulo_error'] = 'Proveedor no encontrado.';
            header('Location: index.php?url=proveedores');
            exit;
        }

        $exito = $_SESSION['modulo_exito'] ?? null;
        $error = $_SESSION['modulo_error'] ?? null;
        unset($_SESSION['modulo_exito'], $_SESSION['modulo_error']);
        
        // Usar la misma vista de index pero con datos para editar, o una específica
        require_once __DIR__ . '/../views/proveedores/editar.php';
    }

    public function actualizarProveedor(): void
    {
        $this->validarCSRF();
        $id = (int)($_POST['id'] ?? 0);
        try {
            $modelo = new Proveedor();
            
            // Verificación de RIF (excluyendo el actual)
            $rif = $_POST['rif'] ?? '';
            $existente = $modelo->buscarPorRIF($rif);
            if ($existente && (int)$existente['id'] !== $id) {
                throw new \RuntimeException("El RIF $rif ya pertenece a otro proveedor.");
            }

            $modelo->actualizar($id, $_POST);
            $_SESSION['modulo_exito'] = '✅ Proveedor actualizado exitosamente.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
            header("Location: index.php?url=proveedores/editar&id=$id");
            exit;
        }
        header('Location: index.php?url=proveedores');
        exit;
    }

    public function eliminarProveedor(): void
    {
        if ($_SESSION['usuario_rol'] !== 'Administrador') {
            $_SESSION['modulo_error'] = 'Acceso denegado: Solo el Administrador puede eliminar proveedores.';
            header('Location: index.php?url=proveedores');
            exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        try {
            $modelo = new Proveedor();
            $modelo->eliminar($id);
            $_SESSION['modulo_exito'] = '✅ Proveedor eliminado (desactivado) exitosamente.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }
        header('Location: index.php?url=proveedores');
        exit;
    }

    // ===================== USUARIOS =====================
    public function listarUsuarios(): void
    {
        if ($_SESSION['usuario_rol'] !== 'Administrador') { header('Location: index.php?url=dashboard'); exit; }
        $modelo = new Usuario();
        $usuarios = $modelo->listarActivos();
        $exito = $_SESSION['modulo_exito'] ?? null;
        $error = $_SESSION['modulo_error'] ?? null;
        unset($_SESSION['modulo_exito'], $_SESSION['modulo_error']);
        require_once __DIR__ . '/../views/usuarios/index.php';
    }

    public function crearUsuario(): void
    {
        if ($_SESSION['usuario_rol'] !== 'Administrador') { header('Location: index.php?url=dashboard'); exit; }
        $this->validarCSRF();
        try {
            if (($_POST['clave'] ?? '') !== ($_POST['clave_confirmar'] ?? '')) {
                throw new \RuntimeException('Las contraseñas no coinciden.');
            }
            $modelo = new Usuario();
            
            // Verificación previa de cédula
            $cedula = $_POST['cedula'] ?? '';
            if ($modelo->buscarPorCedula($cedula)) {
                throw new \RuntimeException("El usuario con la cédula $cedula ya se encuentra registrado.");
            }

            $modelo->crear($_POST);
            $_SESSION['modulo_exito'] = '✅ Usuario creado exitosamente.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }
        header('Location: index.php?url=usuarios');
        exit;
    }

    // ===================== UTILIDADES =====================
    private function validarCSRF(): void
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['modulo_error'] = 'Token de seguridad inválido.';
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
            exit;
        }
    }
}
