<?php
header('Content-Type: application/json');
// Desactivar errores en pantalla para no corromper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../config/db.php';

// 1. CONSULTA DE PRÉSTAMOS
// Usamos alias para estandarizar nombres con la tabla de reservas
$sql_prestamos = "
    SELECT 
        p.id, 
        'PRESTAMO' as tipo_registro,
        p.fecha_prestamo as fecha_inicio, 
        p.fecha_devolucion_pactada as fecha_fin, 
        p.estado,
        p.observaciones, 
        CONCAT(per.nombres, ' ', per.apellidos) as solicitante,
        per.tipo as tipo_solicitante,
        per.grado,
        per.seccion,
        (SELECT COUNT(*) FROM detalle_prestamo dp WHERE dp.id_prestamo = p.id) as total_libros,
        (SELECT GROUP_CONCAT(CONCAT(l.titulo, ' (', dp.cantidad, ')') SEPARATOR ', ') 
         FROM detalle_prestamo dp 
         JOIN libros l ON dp.id_libro = l.id 
         WHERE dp.id_prestamo = p.id) as resumen_libros
    FROM prestamos p
    INNER JOIN personas per ON p.id_persona_solicitante = per.id
";

// 2. CONSULTA DE RESERVAS (Solo PENDIENTES)
// Transformamos los datos para que encajen en la estructura de préstamos
$sql_reservas = "
    SELECT 
        r.id,
        'RESERVA' as tipo_registro,
        CONCAT(r.fecha_uso, ' ', r.hora_inicio) as fecha_inicio,
        CONCAT(r.fecha_uso, ' ', r.hora_fin) as fecha_fin,
        'RESERVADO' as estado,
        CONCAT('Reserva para: ', r.hora_inicio, ' - ', r.hora_fin) as observaciones,
        CONCAT(p.nombres, ' ', p.apellidos) as solicitante,
        p.tipo as tipo_solicitante,
        r.grado,
        r.seccion,
        r.cantidad as total_libros,
        l.titulo as resumen_libros
    FROM reservas r
    JOIN libros l ON r.id_libro = l.id
    JOIN usuarios u ON r.id_usuario_solicitante = u.id
    LEFT JOIN personas p ON u.id_persona = p.id
    WHERE r.estado = 'PENDIENTE'
";

// Unimos y ordenamos por fecha más reciente
$sql_final = "SELECT * FROM (($sql_prestamos) UNION ALL ($sql_reservas)) AS unidos ORDER BY fecha_inicio DESC";

$resultado = $conn->query($sql_final);

if (!$resultado) {
    // Si falla la consulta, devolvemos el error para verlo en la consola del navegador
    echo json_encode(['error' => true, 'mensaje' => $conn->error]);
    exit;
}

$lista = [];

while($row = $resultado->fetch_assoc()) {
    // Formato Fecha Inicio
    $row['fecha_inicio_fmt'] = date("d/m/Y H:i", strtotime($row['fecha_inicio']));
    
    // Formato Fecha Fin / Vencimiento
    if ($row['tipo_registro'] == 'RESERVA') {
        $row['fecha_fin_fmt'] = date("H:i", strtotime($row['fecha_fin'])); // Solo hora para reservas
    } else {
        $row['fecha_fin_fmt'] = date("d/m/Y", strtotime($row['fecha_fin'])); // Fecha completa para préstamos
    }

    // Detectar si es un préstamo que nació de una reserva
    $row['es_reserva_convertida'] = (strpos($row['observaciones'] ?? '', 'Reserva #') !== false);

    // Extraer Hora Límite (Solo Préstamos)
    $row['hora_limite'] = null;
    if ($row['tipo_registro'] == 'PRESTAMO' && strpos($row['observaciones'], 'Devolución límite') !== false) {
        $partes = explode('|', $row['observaciones']);
        foreach($partes as $parte) {
            if(strpos($parte, ' a las: ') !== false) {
                $sub = explode(' a las: ', $parte);
                if(isset($sub[1])) $row['hora_limite'] = trim($sub[1]);
            }
        }
    }

    // Extraer Aula (Solo Préstamos)
    $row['aula_info'] = null;
    if ($row['tipo_registro'] == 'PRESTAMO' && strpos($row['observaciones'], 'Destino: Aula') !== false) {
        $partes = explode('|', $row['observaciones']);
        foreach($partes as $parte) {
            if(strpos($parte, 'Destino: Aula') !== false) {
                $row['aula_info'] = trim(str_replace(['Destino: Aula', '"'], '', $parte));
            }
        }
    }

    $lista[] = $row;
}

echo json_encode($lista);
?>