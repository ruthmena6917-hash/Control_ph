<?php
require '../auth/session.php';
verificarRol(['seguridad']);
require '../config/database.php';

$busqueda = $_GET['busqueda'] ?? '';
$fecha = $_GET['fecha'] ?? date('Y-m-d');

$sql = "SELECT * FROM visitas 
        WHERE DATE(fecha_programada) = :fecha
        AND (nombre_visitante LIKE :busqueda OR cedula_visitante LIKE :busqueda)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':fecha' => $fecha,
    ':busqueda' => "%$busqueda%"
]);

$visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista</title>
</head>
<body>

<h2>Visitas del día</h2>

<form method="GET">
    <input type="text" name="busqueda" placeholder="Buscar..." value="<?= $busqueda ?>">
    <input type="date" name="fecha" value="<?= $fecha ?>">
    <button type="submit">Filtrar</button>
</form>

<table border="1">
<tr>
    <th>Hora</th>
    <th>Visitante</th>
    <th>Cédula</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>

<?php foreach ($visitas as $v): ?>
<tr>
    <td><?= $v['fecha_programada'] ?></td>
    <td><?= $v['nombre_visitante'] ?></td>
    <td><?= $v['cedula_visitante'] ?></td>
    <td><?= $v['estado'] ?></td>
    <td>
        <?php if ($v['estado'] == 'pendiente'): ?>
            <form action="marcar_entrada.php" method="POST">
                <input type="hidden" name="visita_id" value="<?= $v['id'] ?>">
                <button>Entrada</button>
            </form>
        <?php endif; ?>

        <?php if ($v['estado'] == 'en_edificio'): ?>
            <form action="marcar_salida.php" method="POST">
                <input type="hidden" name="visita_id" value="<?= $v['id'] ?>">
                <button>Salida</button>
            </form>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>