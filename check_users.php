<?php
/**
 * Script de diagnóstico para el login de SIGFA
 */

require_once 'c:/xampp/htdocs/sigfa/config/db.php';

try {
    $db = Conexion::obtenerInstancia();
    $pdo = $db->obtenerPDO();

    // 1. Verificar si la tabla usuarios existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    $tablaExiste = $stmt->fetch();

    if (!$tablaExiste) {
        die("ERROR: La tabla 'usuarios' no existe en la base de datos 'sigfa_db'. Por favor importe el archivo database/schema.sql.");
    }

    // 2. Contar usuarios
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $totalUsuarios = $stmt->fetchColumn();

    echo "--- DIAGNÓSTICO SIGFA ---\n";
    echo "Total de usuarios registrados: $totalUsuarios\n";

    if ($totalUsuarios == 0) {
        echo "AVISO: La tabla 'usuarios' está vacía. Por favor ejecute el insert en schema.sql.\n";
    } else {
        // 3. Listar cédulas existentes y verificar contraseña de Admin
        $stmt = $pdo->query("SELECT id, cedula, clave, nombre, rol, activo FROM usuarios");
        $usuarios = $stmt->fetchAll();

        echo "Usuarios encontrados:\n";
        foreach ($usuarios as $u) {
            $passVerificado = password_verify('Admin2026!', $u['clave']) ? 'CORRECTA' : 'INCORRECTA';
            echo "- Cédula: {$u['cedula']} | Nombre: {$u['nombre']} | Pass 'Admin2026!': $passVerificado\n";
        }
    }

} catch (Exception $e) {
    die("ERROR DE CONEXIÓN: " . $e->getMessage());
}
