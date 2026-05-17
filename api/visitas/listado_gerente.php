<?php
require_once '../../config/database.php';
require_once '../../auth/session.php';

// Verificar que sea gerente
verificarRol(['gerente']);

try {
    // Consulta extendida con datos de seguridad
    $sql = "SELECT v.id, v.visitante_nombre, v.visitante_cedula, v.placa_vehiculo,
                   v.fecha_entrada_real, v.fecha_salida, v.fecha_programada,
                   v.notas, v.estado, v.foto_url,
                   a.numero AS apartamento, a.torre,
                   ru.nombre AS residente_nombre,
                   ureg.nombre AS registrado_por_nombre,
                   uval.nombre AS validado_por_nombre
            FROM visitas v
            JOIN apartamentos a ON v.apartamento_id = a.id
            JOIN residentes r ON v.residente_id = r.id
            JOIN usuarios ru ON r.usuario_id = ru.id
            JOIN usuarios ureg ON v.registrado_por = ureg.id
            LEFT JOIN usuarios uval ON v.validado_por = uval.id
            ORDER BY v.id DESC";

    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);

} catch (PDOException $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
