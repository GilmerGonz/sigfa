<?php
header('Content-Type: application/json; charset=UTF-8');

class AjaxController
{
    public function buscarMedicamentos(): void
    {
        $query = $_GET['q'] ?? '';
        $resultados = [];
        
        if (strlen($query) >= 1) {
            $pdo = \Conexion::obtenerInstancia()->obtenerPDO();
            $stmt = $pdo->prepare("SELECT id, codigo, nombre_generico, concentracion, grupo_codigo, tipo_medicamento FROM medicamentos WHERE activo = 1 AND (nombre_generico LIKE ? OR codigo LIKE ?) ORDER BY nombre_generico LIMIT 50");
            $stmt->execute(["%{$query}%", "%{$query}%"]);
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        echo json_encode($resultados);
        exit;
    }

    public function buscarPacientes(): void
    {
        $query = $_GET['q'] ?? '';
        $resultados = [];
        
        if (strlen($query) >= 1) {
            $pdo = \Conexion::obtenerInstancia()->obtenerPDO();
            $stmt = $pdo->prepare("SELECT id, cedula, nombre, apellido FROM asegurados WHERE estatus = 'Activo' AND (nombre LIKE ? OR apellido LIKE ? OR cedula LIKE ?) ORDER BY nombre, apellido LIMIT 50");
            $stmt->execute(["%{$query}%", "%{$query}%", "%{$query}%"]);
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        echo json_encode($resultados);
        exit;
    }

    public function buscarMedicos(): void
    {
        $query = $_GET['q'] ?? '';
        $resultados = [];
        
        if (strlen($query) >= 1) {
            $pdo = \Conexion::obtenerInstancia()->obtenerPDO();
            $stmt = $pdo->prepare("SELECT id, nombre, apellido, especialidad FROM medicos WHERE activo = 1 AND (nombre LIKE ? OR apellido LIKE ? OR especialidad LIKE ?) ORDER BY nombre, apellido LIMIT 50");
            $stmt->execute(["%{$query}%", "%{$query}%", "%{$query}%"]);
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        echo json_encode($resultados);
        exit;
    }

    public function buscarProveedores(): void
    {
        $query = $_GET['q'] ?? '';
        $resultados = [];
        
        if (strlen($query) >= 1) {
            $pdo = \Conexion::obtenerInstancia()->obtenerPDO();
            $stmt = $pdo->prepare("SELECT id, razon_social, rif FROM proveedores WHERE activo = 1 AND (razon_social LIKE ? OR rif LIKE ?) ORDER BY razon_social LIMIT 50");
            $stmt->execute(["%{$query}%", "%{$query}%"]);
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        echo json_encode($resultados);
        exit;
    }

    public function buscarGrupos(): void
    {
        $query = $_GET['q'] ?? '';
        $resultados = [];
        
        if (strlen($query) >= 1) {
            $pdo = \Conexion::obtenerInstancia()->obtenerPDO();
            $stmt = $pdo->prepare("SELECT id, codigo, nombre FROM grupos_medicamentos WHERE activo = 1 AND (nombre LIKE ? OR codigo LIKE ?) ORDER BY nombre LIMIT 50");
            $stmt->execute(["%{$query}%", "%{$query}%"]);
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        echo json_encode($resultados);
        exit;
    }

    public function buscarAlmacenes(): void
    {
        $query = $_GET['q'] ?? '';
        $resultados = [];
        
        if (strlen($query) >= 1) {
            $pdo = \Conexion::obtenerInstancia()->obtenerPDO();
            $stmt = $pdo->prepare("SELECT id, nombre, codigo FROM almacenes WHERE activo = 1 AND nombre LIKE ? ORDER BY nombre LIMIT 50");
            $stmt->execute(["%{$query}%"]);
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        echo json_encode($resultados);
        exit;
    }

    public function buscarServicios(): void
    {
        $query = $_GET['q'] ?? '';
        $resultados = [];
        
        if (strlen($query) >= 1) {
            $pdo = \Conexion::obtenerInstancia()->obtenerPDO();
            $stmt = $pdo->prepare("SELECT id, codigo, nombre FROM servicios_medicos WHERE activo = 1 AND nombre LIKE ? ORDER BY nombre LIMIT 50");
            $stmt->execute(["%{$query}%"]);
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        echo json_encode($resultados);
        exit;
    }

    public function buscarPatologias(): void
    {
        $query = $_GET['q'] ?? '';
        $resultados = [];
        
        if (strlen($query) >= 1) {
            $pdo = \Conexion::obtenerInstancia()->obtenerPDO();
            $stmt = $pdo->prepare("SELECT id, nombre, codigo_cie10 FROM patologias WHERE activo = 1 AND nombre LIKE ? ORDER BY nombre LIMIT 50");
            $stmt->execute(["%{$query}%"]);
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        echo json_encode($resultados);
        exit;
    }
}