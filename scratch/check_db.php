<?php
require 'config/db.php';
$pdo = Conexion::obtenerInstancia()->obtenerPDO();
$stmt = $pdo->query('DESCRIBE transferencias');
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo $r['Field'] . " - " . $r['Type'] . "\n";
}
