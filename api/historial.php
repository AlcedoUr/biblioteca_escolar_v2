<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$sql = "
    SELECT 
        p.id, 
        p.fecha_prestamo, 
        p.fecha_devolucion_pactada, 
        p.estado,
        p.observaciones, 
        CONCAT(per.nombres, ' ', per.apellidos) as solicitante,
        per.tipo as tipo_solicitante,
        per.grado,
        per.seccion,
        u.username as bibliotecario,
        (SELECT COUNT(*) FROM detalle_prestamo dp WHERE dp.id_prestamo = p.id) as total_libros,
        (SELECT GROUP_CONCAT(CONCAT(l.titulo, ' (', dp.cantidad, ')') SEPARATOR ', ') 
         FROM detalle_prestamo dp 
         JOIN libros l ON dp.id_libro = l.id 
         WHERE dp.id_prestamo = p.id) as resumen_libros
    FROM prestamos p
    INNER JOIN personas per ON p.id_persona_solicitante = per.id
    INNER JOIN usuarios u ON p.id_usuario_bibliotecario = u.id
    ORDER BY p.id DESC
";

$resultado = $conn->query($sql);
$prestamos = [];

while($row = $resultado->fetch_assoc()) {
    $row['fecha_prestamo'] = date("d/m/Y H:i", strtotime($row['fecha_prestamo']));
    $row['fecha_devolucion_pactada'] = date("d/m/Y", strtotime($row['fecha_devolucion_pactada']));
    
    // 1. Extraer Aula
    $row['aula_info'] = null;
    if (strpos($row['observaciones'], 'Destino: Aula') !== false) {
        $partes = explode('|', $row['observaciones']);
        foreach($partes as $parte) {
            if(strpos($parte, 'Destino: Aula') !== false) {
                $texto = trim(str_replace('Destino: Aula', '', $parte));
                $row['aula_info'] = str_replace('"', '', $texto);
            }
        }
    }

    // 2. Extraer Hora Límite (CRUCIAL PARA VALIDAR)
    $row['hora_limite'] = null;
    if (strpos($row['observaciones'], 'Devolución límite') !== false) {
        $partes = explode('|', $row['observaciones']);
        foreach($partes as $parte) {
            // Formato en DB: "Devolución límite... a las: HH:mm"
            if(strpos($parte, 'Devolución límite') !== false && strpos($parte, ' a las: ') !== false) {
                $sub = explode(' a las: ', $parte);
                if(isset($sub[1])) {
                    $row['hora_limite'] = trim($sub[1]);
                }
            }
        }
    }

    $prestamos[] = $row;
}

echo json_encode($prestamos);
?>