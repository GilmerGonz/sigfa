<?php
require_once 'c:/xampp/htdocs/sigfa/config/db.php';

try {
    $db = Conexion::obtenerInstancia();
    $pdo = $db->obtenerPDO();

    $cedula = 'V-00000001';
    $nuevaClave = 'Admin2026!';
    $hash = password_hash($nuevaClave, PASSWORD_DEFAULT);

    // Verificar si existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE cedula = ?");
    $stmt->execute([$cedula]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Actualizar
        $stmt = $pdo->prepare("UPDATE usuarios SET clave = ?, activo = 1 WHERE id = ?");
        $stmt->execute([$hash, $usuario['id']]);
        echo "ÉXITO: Se ha restablecido la contraseña para $cedula a '$nuevaClave'.\n";
    } else {
        // Crear
        $stmt = $pdo->prepare("INSERT INTO usuarios (cedula, nombre, apellido, correo, clave, rol, activo) VALUES (?, 'Administrador', 'SIGFA', 'admin@sigfa.local', ?, 'Administrador', 1)");
        $stmt->execute([$cedula, $hash]);
        echo "ÉXITO: Se ha creado el usuario administrador $cedula con la contraseña '$nuevaClave'.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
