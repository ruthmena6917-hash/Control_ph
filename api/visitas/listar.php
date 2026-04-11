<?php
require_once '../../auth/session.php';
require_once '../../config/database.php';

// Establecer headers para respuesta JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Verificar sesión activa
    verificar_sesion();
    
    // Obtener parámetro de fecha (opcional, por defecto fecha actual)
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    
    // Validar formato de fecha
    if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
        throw new Exception('Formato de fecha inválido. Use YYYY-MM-DD');
    }
    
    $rol = $_SESSION['rol'];
    $usuario_id = $_SESSION['usuario_id'];
    
    // Verificar roles permitidos
    verificarRol(['gerente', 'seguridad', 'residente']);
    
    // Construir consulta base
    $sql = "SELECT 
                v.id,
                v.visitante_nombre,
                v.visitante_cedula,
                v.fecha_programada,
                v.fecha_entrada_real,
                v.fecha_salida_real,
                v.estado,
                v.notas,
                r.nombre AS residente_nombre,
                a.numero AS apartamento_numero
            FROM visitas v
            JOIN residentes r ON v.residente_id = r.id
            JOIN usuarios u ON r.usuario_id = u.id
            JOIN apartamentos a ON v.apartamento_id = a.id
            WHERE DATE(v.fecha_programada) = :fecha
              AND v.estado != 'cancelada'";
    
    // Agregar filtros según rol
    $params = [':fecha' => $fecha];
    
    if ($rol === 'residente') {
        // Residente solo ve sus propias visitas
        $sql .= " AND r.usuario_id = :usuario_id";
        $params[':usuario_id'] = $usuario_id;
    }
    
    $sql .= " ORDER BY v.fecha_programada ASC";
    
    // Preparar y ejecutar consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas para mejor presentación
    foreach ($visitas as &$visita) {
        $visita['fecha_programada'] = date('Y-m-d H:i', strtotime($visita['fecha_programada']));
        $visita['fecha_entrada_real'] = $visita['fecha_entrada_real'] 
            ? date('Y-m-d H:i', strtotime($visita['fecha_entrada_real'])) 
            : null;
        $visita['fecha_salida_real'] = $visita['fecha_salida_real'] 
            ? date('Y-m-d H:i', strtotime($visita['fecha_salida_real'])) 
            : null;
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $visitas,
        'total' => count($visitas),
        'fecha_consultada' => $fecha,
        'rol_usuario' => $rol
    ]);
    
} catch (Exception $e) {
    // Manejo de errores
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

