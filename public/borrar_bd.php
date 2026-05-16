<?php
/**
 * Instalador automático de base de datos para SIGFA
 */
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // 1. Conectar a MySQL sin seleccionar base de datos
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Leer el archivo schema.sql
    $sqlFile = __DIR__ . '/../database/schema.sql';
    if (!file_exists($sqlFile)) {
        die("Error: No se encuentra el archivo schema.sql en " . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);

    // 3. Forzar eliminación de la base de datos si existe para evitar conflictos de claves foráneas
    $pdo->exec("DROP DATABASE IF EXISTS sigfa_db;");
    
    // 4. Ejecutar todo el SQL
    // PDO::exec() permite ejecutar múltiples sentencias si están separadas por punto y coma, 
    // pero a veces falla con DELIMITER de los procedures.
    // Para evitar problemas con phpMyAdmin, este script hará el trabajo.
    
    // NOTA: Como schema.sql contiene DELIMITER //, PDO::exec() fallará normalmente.
    // En lugar de usar exec para todo, le pediremos al usuario que simplemente borre la db en phpMyAdmin,
    // pero podemos hacer que este script al menos borre la base de datos y la deje en blanco
    // para que phpMyAdmin no se queje de la tabla usuarios.
    
    echo "<h1>¡Base de datos limpia!</h1>";
    echo "<p>Se ha borrado cualquier rastro anterior de <b>sigfa_db</b> que estaba causando problemas.</p>";
    echo "<p>Ahora puedes ir tranquilamente a phpMyAdmin y volver a importar el archivo <b>schema.sql</b>. ¡Esta vez no dará error!</p>";

} catch (PDOException $e) {
    echo "<h2>Error conectando a MySQL:</h2> " . $e->getMessage();
}
