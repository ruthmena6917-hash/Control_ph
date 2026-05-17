<?php
require_once '../../auth/session.php';
require_once '../../config/database.php';

verificarRol(['gerente']);

header('Content-Type: application/json');

try {
    $sql = "SELECT * FROM empleados_externos ORDER BY nombre ASC";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
