<?php

session_start();

function verificar_sesion(){
    if(!isset($_SESSION['usuario_id'])){
        header("Location: ../views/login.php");
        exit();
    }
}

function verificarRol($rolesPermitidos){
    verificar_sesion();

    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $rolesPermitidos)) {
        header('Location: ../views/login.php');
        exit;
    }
}


?>
