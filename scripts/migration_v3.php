<?php
require_once 'config/database.php';

try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN tipo_sangre VARCHAR(5) DEFAULT NULL;");
    echo "usuarios: tipo_sangre added\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN contacto_emergencia VARCHAR(100) DEFAULT NULL;");
    echo "usuarios: contacto_emergencia added\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN placa_principal VARCHAR(20) DEFAULT NULL;");
    echo "usuarios: placa_principal added\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

try {
    $pdo->exec("ALTER TABLE empleados ADD COLUMN observaciones_seguridad TEXT DEFAULT NULL;");
    echo "empleados: observaciones_seguridad added\n";
} catch (Exception $e) { echo $e->getMessage() . "\n"; }

echo "Migration complete.\n";
