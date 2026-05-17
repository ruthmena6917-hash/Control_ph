<?php
require_once 'config/database.php';

echo "Starting Professional Seeder...\n";

// Helper for random names
function randomName() {
    $first = ['Juan', 'Pedro', 'Maria', 'Ana', 'Luis', 'Carlos', 'Sofia', 'Jorge', 'Elena', 'Ricardo', 'Beatriz', 'Diego', 'Lucia', 'Mateo', 'Valentina', 'Gabriel', 'Martina', 'Fernando', 'Camila', 'Sebastian'];
    $last = ['Perez', 'Garcia', 'Martinez', 'Rodriguez', 'Lopez', 'Hernandez', 'Gonzalez', 'Torres', 'Ramirez', 'Flores', 'Rivera', 'Castillo', 'Rios', 'Guzman', 'Mendoza', 'Nava', 'Calderon', 'Velasquez', 'Salazar', 'Bermudez'];
    return $first[array_rand($first)] . ' ' . $last[array_rand($last)];
}

function randomCedula() {
    return rand(1, 9) . '-' . rand(100, 999) . '-' . rand(100, 999);
}

function randomPlate() {
    $letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    return $letters[rand(0, 25)] . $letters[rand(0, 25)] . rand(1000, 9999);
}

try {
    $pdo->beginTransaction();

    // 0. Clean old data (except the first few essential users)
    // We'll keep usuario 1 (Carlos Mendez - Gerente)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DELETE FROM bitacora_accesos");
    $pdo->exec("DELETE FROM visitas");
    $pdo->exec("DELETE FROM turnos");
    $pdo->exec("DELETE FROM empleados WHERE usuario_id > 3");
    $pdo->exec("DELETE FROM residentes WHERE usuario_id > 5");
    $pdo->exec("DELETE FROM autenticacion WHERE usuario_id > 5");
    $pdo->exec("DELETE FROM usuarios WHERE id > 5");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Old data cleared.\n";

    // 1. Ensure enough apartments exist
    $pdo->exec("DELETE FROM apartamentos WHERE id > 5");
    for ($t = 1; $t <= 3; $t++) {
        for ($p = 1; $p <= 5; $p++) {
            for ($n = 1; $n <= 4; $n++) {
                $num = ($p * 100) + $n;
                $stmt = $pdo->prepare("INSERT IGNORE INTO apartamentos (numero, torre, piso, tipo) VALUES (?, ?, ?, ?)");
                $stmt->execute([$num, $t, $p, 'Residencial']);
            }
        }
    }
    
    $apts = $pdo->query("SELECT id FROM apartamentos")->fetchAll(PDO::FETCH_COLUMN);
    echo "Apartments generated: " . count($apts) . "\n";

    // 2. Generate Residents (Roles are 1:gerente, 2:seguridad, 3:residente)
    // We already have usuarios 4 and 5 as residents. Let's add 20 more.
    for ($i = 0; $i < 20; $i++) {
        $name = randomName();
        $cedula = randomCedula();
        $email = str_replace(' ', '.', strtolower($name)) . rand(1,99) . "@email.com";
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (rol_id, nombre, cedula, email, telefono, tipo_sangre, contacto_emergencia, placa_principal) VALUES (3, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $name, $cedula, $email, '6000-' . rand(1000, 9999), 
            ['A+', 'O+', 'B+', 'AB+'][rand(0,3)], 
            randomName() . ' (6000-' . rand(1000, 9999) . ')',
            randomPlate()
        ]);
        $uid = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("INSERT INTO residentes (usuario_id, apartamento_id, fecha_ingreso) VALUES (?, ?, ?)");
        $stmt->execute([$uid, $apts[array_rand($apts)], date('Y-m-d', strtotime('-' . rand(30, 365) . ' days'))]);
        
        $stmt = $pdo->prepare("INSERT INTO autenticacion (usuario_id, username, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$uid, str_replace(' ', '.', strtolower($name)) . rand(1,99), password_hash('123456', PASSWORD_DEFAULT)]);
    }
    echo "Residents generated.\n";

    // 3. Generate Security Guards
    $securityIds = [2, 3]; // Existing guards Luis and Ana
    for ($i = 0; $i < 4; $i++) {
        $name = "Guardia " . randomName();
        $cedula = randomCedula();
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (rol_id, nombre, cedula, email, telefono, tipo_sangre) VALUES (2, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $cedula, strtolower(str_replace(' ', '', $name)) . "@security.com", '6777-' . rand(1000, 9999), 'O+']);
        $uid = $pdo->lastInsertId();
        $securityIds[] = $uid;

        $stmt = $pdo->prepare("INSERT INTO empleados (usuario_id, cargo, fecha_ingreso, observaciones_seguridad) VALUES (?, 'Guardia de Seguridad', ?, 'Personal capacitado en primeros auxilios')");
        $stmt->execute([$uid, date('Y-m-d', strtotime('-' . rand(10, 100) . ' days'))]);
        
        $stmt = $pdo->prepare("INSERT INTO autenticacion (usuario_id, username, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$uid, "guardia." . rand(100, 999), password_hash('123456', PASSWORD_DEFAULT)]);
    }
    echo "Security guards generated.\n";

    // 4. Generate External Employees
    $pdo->exec("DELETE FROM empleados_externos");
    $services = ['Delivery', 'Limpieza', 'Mantenimiento', 'Plomería', 'Jardinería'];
    for ($i = 0; $i < 8; $i++) {
        $stmt = $pdo->prepare("INSERT INTO empleados_externos (nombre, cedula, empresa, servicio_tipo, codigo_qr) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            randomName(), randomCedula(), 
            ['Independiente', 'ServiLogist', 'CoolApt', 'CleanWay'][rand(0,3)],
            $services[array_rand($services)],
            'QR-' . strtoupper(substr(md5(rand()), 0, 8))
        ]);
    }
    echo "External services generated.\n";

    // 5. Generate Visits for the last 14 days
    $residentIds = $pdo->query("SELECT id FROM residentes")->fetchAll(PDO::FETCH_COLUMN);
    $aptIds = $pdo->query("SELECT id, apartamento_id FROM residentes")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    for ($d = 0; $d < 14; $d++) {
        $dateStr = date('Y-m-d', strtotime("-$d days"));
        
        // Distribution of visits based on time of day
        $visitsPerDay = rand(15, 30);
        for ($v = 0; $v < $visitsPerDay; $v++) {
            // Logic for peak hours
            // 0-8: night, 8-10: peak, 10-16: midday, 16-19: peak, 19-24: evening
            $r = rand(0, 100);
            if ($r < 10) $hour = rand(0, 7);
            elseif ($r < 40) $hour = rand(8, 10);
            elseif ($r < 60) $hour = rand(11, 15);
            elseif ($r < 90) $hour = rand(16, 19);
            else $hour = rand(20, 23);
            
            $timeStr = sprintf("%02d:%02d:00", $hour, rand(0, 59));
            $dateTime = "$dateStr $timeStr";
            
            $resId = $residentIds[array_rand($residentIds)];
            $aptId = $aptIds[$resId];
            
            $isService = (rand(0, 5) == 0) ? "[SERVICIO] " : "";
            $estado = ($d == 0 && $hour >= date('H')) ? 'pendiente' : (rand(0, 5) == 0 && $d == 0 ? 'en_edificio' : 'finalizada');
            
            $entrada = $dateTime;
            $salida = ($estado == 'finalizada') ? date('Y-m-d H:i:s', strtotime($entrada . ' +' . rand(30, 180) . ' minutes')) : null;
            
            $stmt = $pdo->prepare("INSERT INTO visitas (visitante_nombre, visitante_cedula, placa_vehiculo, residente_id, apartamento_id, fecha_programada, fecha_entrada_real, fecha_salida, estado, registrado_por, validado_por, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                randomName(), randomCedula(), (rand(0, 1) ? randomPlate() : null),
                $resId, $aptId, $dateTime, $entrada, $salida, $estado,
                1, // Registrado por Gerente (simplificado)
                $securityIds[array_rand($securityIds)],
                $isService . "Revision rutinaria / Visita autorizada."
            ]);
        }
    }
    
    // 6. Generate Shifts for Security
    for ($d = 0; $d < 7; $d++) {
        $dateStr = date('Y-m-d', strtotime("+$d days"));
        foreach ($securityIds as $sid) {
            // get security employee id
            $stmt = $pdo->prepare("SELECT id FROM empleados WHERE usuario_id = ?");
            $stmt->execute([$sid]);
            $eid = $stmt->fetchColumn();
            if (!$eid) continue;

            $shifts = [['06:00:00', '14:00:00'], ['14:00:00', '22:00:00'], ['22:00:00', '06:00:00']];
            $s = $shifts[array_rand($shifts)];
            
            $stmt = $pdo->prepare("INSERT INTO turnos (empleado_id, fecha, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)");
            $stmt->execute([$eid, $dateStr, $s[0], $s[1]]);
        }
    }

    $pdo->commit();
    echo "Seeding complete! Visual data is now rich and realistic.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
