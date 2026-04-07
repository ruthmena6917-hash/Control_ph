<?php
require '../../../auth/session.php';
verificarRol(['residente']);
require '../../../config/database.php';

// 1. Obtener el residente_id real del usuario en sesión
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT id FROM residentes WHERE usuario_id = :usuario_id");
$stmt->execute([':usuario_id' => $usuario_id]);
$residente = $stmt->fetch();

if (!$residente) {
    die("Error: No se encontró perfil de residente para este usuario.");
}

// 2. Traer visitas vinculadas a ese residente_id
$stmt = $pdo->prepare("SELECT * FROM visitas WHERE residente_id = :id ORDER BY fecha_programada DESC");
$stmt->execute([':id' => $residente['id']]);
$visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Visitas - Módulo Residente</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body style="background: var(--gris-fondo); font-family: 'Inter', sans-serif; padding: 2rem;">

<div style="max-width: 900px; margin: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="color: var(--azul-primario); margin: 0;">📦 Mis Visitas Programadas</h2>
        <a href="../../dashboard_residente.php" style="color: var(--azul-primario); font-weight: 500; text-decoration: none;">← Volver al Panel</a>
    </div>

    <div class="content-box">
        <table class="custom-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--azul-primario); color: white;">
                    <th style="padding: 1rem; text-align: left;">Visitante</th>
                    <th style="padding: 1rem; text-align: left;">Cédula</th>
                    <th style="padding: 1rem; text-align: left;">Fecha/Hora</th>
                    <th style="padding: 1rem; text-align: left;">Estado</th>
                    <th style="padding: 1rem; text-align: center;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($visitas)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 3rem; color: var(--texto-suave);">No tienes visitas programadas actualmente.</td></tr>
                <?php endif; ?>
                
                <?php foreach ($visitas as $v): ?>
                <tr style="border-bottom: 1px solid var(--gris-borde);">
                    <td style="padding: 1rem; font-weight: 500;"><?= $v['visitante_nombre'] ?></td>
                    <td style="padding: 1rem; color: var(--texto-suave);"><?= $v['visitante_cedula'] ?></td>
                    <td style="padding: 1rem;"><?= date('d/m/Y H:i', strtotime($v['fecha_programada'])) ?></td>
                    <td style="padding: 1rem;">
                        <?php 
                            $badgeClass = match($v['estado']) {
                                'pendiente' => 'badge-pendiente',
                                'en_edificio' => 'badge-edificio',
                                'finalizada' => 'badge-finalizada',
                                'cancelada' => 'badge-finalizada', // Usamos el mismo gris para cancelada por simplicidad visual
                                default => 'badge-finalizada'
                            };
                        ?>
                        <span class="badge-status <?= $badgeClass ?>"><?= ucfirst($v['estado']) ?></span>
                    </td>
                    <td style="padding: 1rem; text-align: center;">
                        <?php if ($v['estado'] === 'pendiente'): ?>
                            <form action="visitas/cancelar.php" method="POST">
                                <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                <button type="submit" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">
                                    Cancelar
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color: #ccc; font-size: 0.8rem;">Sin acciones</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>