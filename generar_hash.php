<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$passwords = [
    'gerente'    => 'admin123',
    'luis.rios'  => 'seguridad123',
    'ana.torres' => 'seguridad123',
    'maria.g'    => 'residente123',
    'pedro.c'    => 'residente123',
];

foreach ($passwords as $usuario => $pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    echo "$usuario => $hash <br>";
}
?>