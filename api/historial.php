<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Consulta Vitaminada: Trae datos del salón y un resumen de los títulos prestados
$sql = "
    SELECT 
        p.id, 
        p.fecha_prestamo, 
        p.fecha_devolucion_pactada, 
        p.estado,
        CONCAT(per.nombres, ' ', per.apellidos) as solicitante,
        per.tipo as tipo_solicitante,
        per.grado,
        per.seccion,
        u.username as bibliotecario,
        (SELECT COUNT(*) FROM detalle_prestamo dp WHERE dp.id_prestamo = p.id) as total_libros,
        -- Truco: Concatenamos los títulos de los libros en un solo string
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
    // Formatear fechas
    $row['fecha_prestamo'] = date("d/m/Y H:i", strtotime($row['fecha_prestamo']));
    $row['fecha_devolucion_pactada'] = date("d/m/Y", strtotime($row['fecha_devolucion_pactada']));
    $prestamos[] = $row;
}

echo json_encode($prestamos);
?>