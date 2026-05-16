<?php
/**
 * Script temporal para corregir la contraseña del administrador.
 * ELIMINAR DESPUÉS DE USAR.
 */
require_once dirname(__DIR__) . '/config/db.php';

$clave = 'Admin2026!';
$hash = password_hash($clave, PASSWORD_DEFAULT);

$db = Conexion::obtenerInstancia();
$stmt = $db->ejecutar(
    "UPDATE usuarios SET clave = :clave WHERE cedula = :cedula",
    ['clave' => $hash, 'cedula' => 'V-00000001']
);

echo "<h2 style='font-family:sans-serif;color:green;'>✅ Contraseña actualizada correctamente</h2>";
echo "<p style='font-family:sans-serif;'>Cédula: <b>V-00000001</b><br>Contraseña: <b>Admin2026!</b></p>";
echo "<p style='font-family:sans-serif;color:red;'><b>⚠️ Elimina este archivo (fix_password.php) después de usarlo.</b></p>";
echo "<p><a href='index.php' style='font-family:sans-serif;'>→ Ir al Login</a></p>";
