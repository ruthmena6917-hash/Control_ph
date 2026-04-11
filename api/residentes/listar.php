<?php
require '../../auth/session.php';
require '../../config/database.php';

verificarRol(['gerente']);

$sql = "SELECT 
            r.id,
            u.nombre,
            u.cedula,
            u.telefono,
            u.email,
            a.numero AS apartamento,
            a.piso,
            r.fecha_ingreso,
            r.activo
        FROM residentes r
        JOIN usuarios u ON u.id = r.usuario_id
        JOIN apartamentos a ON a.id = r.apartamento_id
        WHERE r.activo = 1
        ORDER BY a.numero ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$residentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($residentes);