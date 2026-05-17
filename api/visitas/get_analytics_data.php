<?php
require_once '../../auth/session.php';
require_once '../../config/database.php';

verificarRol(['gerente']);

header('Content-Type: application/json');

$periodo = $_GET['periodo'] ?? 'dia'; // dia, semana
$metrica = $_GET['metrica'] ?? 'todas'; // visitas, ocupacion, servicios, todas

try {
    $results = [
        'success' => true,
        'periodo' => $periodo,
        'metrica' => $metrica,
        'datasets' => []
    ];

    if ($periodo === 'dia') {
        // Horas 0-23
        $horas = range(0, 23);
        $labels = array_map(function($h) { return $h . ":00"; }, $horas);
        
        // 1. Visitas (Entradas por hora)
        $sqlV = "SELECT HOUR(fecha_entrada_real) as h, COUNT(*) as total 
                 FROM visitas 
                 WHERE DATE(fecha_entrada_real) = CURDATE() 
                 GROUP BY h";
        $stmtV = $pdo->query($sqlV);
        $resV = $stmtV->fetchAll(PDO::FETCH_KEY_PAIR);
        $dataV = array_map(function($h) use ($resV) { return $resV[$h] ?? 0; }, $horas);

        // 2. Servicios (Entradas marcadas como servicio)
        $sqlS = "SELECT HOUR(fecha_entrada_real) as h, COUNT(*) as total 
                 FROM visitas 
                 WHERE DATE(fecha_entrada_real) = CURDATE() AND (notas LIKE '%[SERVICIO]%' OR notas LIKE '%[EXTERNO]%')
                 GROUP BY h";
        $stmtS = $pdo->query($sqlS);
        $resS = $stmtS->fetchAll(PDO::FETCH_KEY_PAIR);
        $dataS = array_map(function($h) use ($resS) { return $resS[$h] ?? 0; }, $horas);

        // 3. Ocupación (Presentes en el edificio a esa hora)
        // Calculamos: Entradas <= hora AND (Salidas > hora OR Salidas IS NULL)
        $dataO = [];
        foreach ($horas as $h) {
            $sqlO = "SELECT COUNT(*) FROM visitas 
                     WHERE DATE(fecha_entrada_real) = CURDATE() 
                     AND HOUR(fecha_entrada_real) <= ? 
                     AND (fecha_salida IS NULL OR (DATE(fecha_salida) = CURDATE() AND HOUR(fecha_salida) > ?))";
            $stmtO = $pdo->prepare($sqlO);
            $stmtO->execute([$h, $h]);
            $dataO[] = $stmtO->fetchColumn();
        }

        $results['labels'] = $labels;
        $results['datasets'] = [
            'visitas' => $dataV,
            'servicios' => $dataS,
            'ocupacion' => $dataO
        ];
    } else {
        // Para semana, simplificamos a conteos diarios
        $sql = "SELECT DAYNAME(fecha_entrada_real) as etiqueta, COUNT(*) as total 
                FROM visitas 
                WHERE YEARWEEK(fecha_entrada_real, 1) = YEARWEEK(CURDATE(), 1) 
                GROUP BY DAYOFWEEK(fecha_entrada_real) 
                ORDER BY DAYOFWEEK(fecha_entrada_real) ASC";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $results['labels'] = array_column($data, 'etiqueta');
        $results['datasets']['visitas'] = array_column($data, 'total');
        // Dummy data for others in weekly view for now
        $results['datasets']['servicios'] = array_fill(0, count($data), 0);
        $results['datasets']['ocupacion'] = array_fill(0, count($data), 0);
    }

    echo json_encode($results);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
