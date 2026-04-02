<?php
require_once 'config/database.php';

if ($pdo) {
    echo "¡Conexión exitosa a la base de datos!";
} else {
    echo "Hubo un problema con la conexión.";
}
?>
