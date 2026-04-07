<?php
require '../../auth/session.php';
verificarRol(['residente']);
require '../../config/database.php';

// 1. Obtener residente_id
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT id FROM residentes WHERE usuario_id = :usuario_id");
$stmt->execute([':usuario_id' => $usuario_id]);
$residente = $stmt->fetch();

if (!$residente) {
    die("Error: Perfil de residente no encontrado.");
}

// 2. Traer sus visitas
$stmt = $pdo->prepare("SELECT * FROM visitas WHERE residente_id = :id ORDER BY fecha_programada DESC");
$stmt->execute([':id' => $residente['id']]);
$visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Visitas - Control de Visitas</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body style="background: var(--gris-fondo); font-family: 'Inter', sans-serif; padding: 2rem;">

<div style="max-width: 900px; margin: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="color: var(--azul-primario); margin: 0;">📦 Mis Visitas Programadas</h2>
        <a href="../dashboard_residente.php" style="color: var(--azul-primario); font-weight: 500; text-decoration: none;">← Volver al Panel</a>
    </div>

    <div class="content-box" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid var(--gris-borde);">
        <table style="width: 100%; border-collapse: collapse;">
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
                    <tr><td colspan="5" style="text-align: center; padding: 3rem; color: var(--texto-suave);">Aún no has registrado visitas.</td></tr>
                <?php endif; ?>
                
                <?php foreach ($visitas as $v): ?>
                <tr style="border-bottom: 1px solid var(--gris-borde);">
                    <td style="padding: 1rem; font-weight: 500;"><?= htmlspecialchars($v['visitante_nombre']) ?></td>
                    <td style="padding: 1rem; color: var(--texto-suave);"><?= htmlspecialchars($v['visitante_cedula']) ?></td>
                    <td style="padding: 1rem;"><?= date('d/m/Y H:i', strtotime($v['fecha_programada'])) ?></td>
                    <td style="padding: 1rem;">
                        <?php 
                            $badgeStyle = match($v['estado']) {
                                'pendiente' => 'background: #fef3c7; color: #92400e;',
                                'en_edificio' => 'background: #d1fae5; color: #065f46;',
                                'finalizada' => 'background: #f3f4f6; color: #374151;',
                                'cancelada' => 'background: #fee2e2; color: #991b1b;',
                                default => 'background: #eee; color: #666;'
                            };
                        ?>
                        <span style="padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; <?= $badgeStyle ?>">
                            <?= ucfirst($v['estado']) ?>
                        </span>
                    </td>
                    <td style="padding: 1rem; text-align: center;">
                        <?php if ($v['estado'] === 'pendiente'): ?>
                            <form action="../../api/visitas/cancelar.php" method="POST">
                                <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                <button type="submit" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 500;">
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
