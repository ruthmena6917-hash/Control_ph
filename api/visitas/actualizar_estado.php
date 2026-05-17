<?php

require '../../auth/session.php';
require '../../config/database.php';

verificarRol(['seguridad']);

header('Content-Type: application/json');

$id      = $_POST['id']      ?? null;
$estado  = $_POST['estado']  ?? null;
$tag_iot = $_POST['tag_iot'] ?? null;
$foto    = $_POST['foto']    ?? null; // Base64

// Validar que llegaron los datos básicos
if (!$id || !$estado) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Validar que el estado sea válido
$estados_permitidos = ['en_edificio', 'finalizada'];
if (!in_array($estado, $estados_permitidos)) {
    echo json_encode(['success' => false, 'error' => 'Estado no válido']);
    exit;
}

try {
    if ($estado === 'en_edificio') {
        $foto_url = null;
        
        // Procesar foto si existe
        if ($foto) {
            $foto = str_replace('data:image/jpeg;base64,', '', $foto);
            $foto = str_replace(' ', '+', $foto);
            $data = base64_decode($foto);
            $fileName = 'visita_' . $id . '_' . time() . '.jpg';
            $filePath = '../../assets/uploads/visitas/' . $fileName;
            
            if (file_put_contents($filePath, $data)) {
                $foto_url = 'assets/uploads/visitas/' . $fileName;
            }
        }

        $sql = "UPDATE visitas 
                SET estado = 'en_edificio',
                    fecha_entrada_real = NOW(),
                    validado_por = :usuario_id,
                    foto_url = :foto_url,
                    token_iot = :tag_iot
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $_SESSION['usuario_id'],
            ':foto_url'   => $foto_url,
            ':tag_iot'    => $tag_iot,
            ':id'         => $id
        ]);

        // --- CREAR NOTIFICACIÓN PARA EL RESIDENTE ---
        $stmt_info = $pdo->prepare("SELECT v.visitante_nombre, r.usuario_id 
                                    FROM visitas v 
                                    JOIN residentes r ON v.residente_id = r.id 
                                    WHERE v.id = :id");
        $stmt_info->execute([':id' => $id]);
        $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if ($info) {
            $msg = "🔔 Tu visita " . $info['visitante_nombre'] . " acaba de ingresar al residencial.";
            $stmt_notif = $pdo->prepare("INSERT INTO notificaciones (usuario_id, mensaje) VALUES (:uid, :msg)");
            $stmt_notif->execute([':uid' => $info['usuario_id'], ':msg' => $msg]);
        }
    }

    if ($estado === 'finalizada') {
        $sql = "UPDATE visitas 
                SET estado = 'finalizada',
                    fecha_salida = NOW()
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>