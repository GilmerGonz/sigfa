<?php
/**
 * Script de migración de nombres genéricos a principios activos.
 */
require_once __DIR__ . '/config/db.php';

try {
    $db = Conexion::obtenerInstancia();
    
    // 1. Obtener todos los medicamentos con nombre genérico
    $stmt = $db->ejecutar("SELECT DISTINCT nombre_generico FROM medicamentos");
    $medicamentos = $stmt->fetchAll();
    
    foreach ($medicamentos as $med) {
        $nombre = $med['nombre_generico'];
        
        // 2. Insertar en principios_activos si no existe
        $db->ejecutar("INSERT IGNORE INTO principios_activos (nombre) VALUES (:nombre)", ['nombre' => $nombre]);
        
        // 3. Obtener el ID
        $stmtId = $db->ejecutar("SELECT id FROM principios_activos WHERE nombre = :nombre", ['nombre' => $nombre]);
        $id = $stmtId->fetch()['id'];
        
        // 4. Actualizar el medicamento
        $db->ejecutar("UPDATE medicamentos SET id_principio_activo = :id WHERE nombre_generico = :nombre", ['id' => $id, 'nombre' => $nombre]);
    }
    
    echo "Migración completada exitosamente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
