<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$id_prestamo = $_GET['id'] ?? 0;

if ($id_prestamo == 0) {
    echo json_encode(['detalles' => [], 'usuario' => null, 'cabecera' => null]);
    exit;
}

// 1. Obtener Datos del Préstamo (Cabecera) y Usuario
$sql_head = "
    SELECT 
        p.id, 
        DATE_FORMAT(p.fecha_prestamo, '%d/%m/%Y %H:%i') as fecha_inicio, 
        DATE_FORMAT(p.fecha_devolucion_pactada, '%d/%m/%Y') as fecha_fin, 
        p.observaciones,
        p.id_persona_solicitante,
        CONCAT(per.nombres, ' ', per.apellidos) as nombre, 
        per.dni, 
        per.tipo,
        per.grado,
        per.seccion
    FROM prestamos p
    INNER JOIN personas per ON p.id_persona_solicitante = per.id
    WHERE p.id = $id_prestamo
";
$res_head = $conn->query($sql_head);
$datos_prestamo = $res_head->fetch_assoc();

// Procesar Ubicación y Hora desde Observaciones
$ubicacion_texto = "Uso Personal / Domicilio";
$hora_limite = ""; // Vacio por defecto

if ($datos_prestamo) {
    // Si es estudiante, por defecto su ubicación es su salón
    if ($datos_prestamo['tipo'] == 'ESTUDIANTE') {
        $ubicacion_texto = $datos_prestamo['grado'] . ' "' . $datos_prestamo['seccion'] . '"';
    }

    // Si hay datos de Aula en observaciones, eso tiene prioridad (para docentes)
    // Buscamos 'Destino: Aula' o 'Tipo: En Aula'
    if (strpos($datos_prestamo['observaciones'], 'Destino: Aula') !== false) {
        $parts = explode('|', $datos_prestamo['observaciones']);
        foreach($parts as $parte) {
            if(strpos($parte, 'Destino: Aula') !== false) {
                $ubicacion_texto = trim(str_replace('Destino: Aula', '', $parte));
                $ubicacion_texto = str_replace('"', '', $ubicacion_texto);
                // Agregamos prefijo para que se entienda
                $ubicacion_texto = "Aula " . $ubicacion_texto;
            }
        }
    }
    
    // Extraer hora límite si existe en las observaciones
    if (strpos($datos_prestamo['observaciones'], 'Devolución límite') !== false) {
        $parts = explode('|', $datos_prestamo['observaciones']);
        foreach($parts as $parte) {
            if(strpos($parte, 'Devolución límite') !== false) {
                // Formato esperado: "Devolución límite hoy a las: 13:05"
                $subparts = explode(' a las: ', $parte);
                if (isset($subparts[1])) {
                    $hora_limite = trim($subparts[1]);
                }
            }
        }
    }
    
    // Guardamos los datos procesados en el array para enviarlos al frontend
    $datos_prestamo['ubicacion_final'] = $ubicacion_texto;
    $datos_prestamo['hora_limite'] = $hora_limite;
}

// 2. Traer los libros de ESTE préstamo
$sql_detalles = "
    SELECT 
        dp.id as id_detalle,
        l.titulo,
        l.id as id_libro,
        l.imagen_portada,
        dp.cantidad,
        dp.estado_devolucion
    FROM detalle_prestamo dp
    INNER JOIN libros l ON dp.id_libro = l.id
    WHERE dp.id_prestamo = ?
";
$stmt = $conn->prepare($sql_detalles);
$stmt->bind_param("i", $id_prestamo);
$stmt->execute();
$res_detalles = $stmt->get_result();

$detalles = [];
while($row = $res_detalles->fetch_assoc()) {
    $detalles[] = $row;
}

// 3. Traer historial de DEUDAS (Otros préstamos)
$historial_pendientes = [];
if ($datos_prestamo) {
    $id_persona = $datos_prestamo['id_persona_solicitante'];
    $sql_historial = "
        SELECT 
            l.titulo,
            dp.cantidad,
            DATE_FORMAT(p.fecha_prestamo, '%d/%m/%Y') as fecha,
            p.id as id_otro_prestamo
        FROM detalle_prestamo dp
        JOIN prestamos p ON dp.id_prestamo = p.id
        JOIN libros l ON dp.id_libro = l.id
        WHERE p.id_persona_solicitante = $id_persona
          AND dp.estado_devolucion = 'PENDIENTE'
          AND p.id != $id_prestamo
        ORDER BY p.fecha_prestamo ASC
    ";
    $res_hist = $conn->query($sql_historial);
    while($row = $res_hist->fetch_assoc()) {
        $historial_pendientes[] = $row;
    }
}

echo json_encode([
    'cabecera' => $datos_prestamo,
    'detalles' => $detalles,
    'otros_pendientes' => $historial_pendientes
]);
?>