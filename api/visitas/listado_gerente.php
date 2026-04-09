<?php
require_once '../../config/database.php';
require_once '../../auth/session.php';

// Verificar que sea gerente
verificarRol(['gerente']);

try {
    $sql = "SELECT v.id, v.visitante_nombre, v.visitante_cedula,
                   v.fecha_entrada_real, v.fecha_salida, v.fecha_programada,
                   v.notas, v.estado,
                   a.numero AS apartamento,
                   ru.nombre AS residente_nombre
            FROM visitas v
            JOIN apartamentos a ON v.apartamento_id = a.id
            JOIN residentes r ON v.residente_id = r.id
            JOIN usuarios ru ON r.usuario_id = ru.id
            ORDER BY v.id DESC";

    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);

} catch (PDOException $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
