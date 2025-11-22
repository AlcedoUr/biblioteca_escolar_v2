<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$id_prestamo = $_GET['id'] ?? 0;

if ($id_prestamo == 0) {
    echo json_encode(['detalles' => [], 'usuario' => null, 'cabecera' => null]);
    exit;
}

// 1. Obtener Datos del Préstamo Actual (Cabecera) y Usuario
// ... (Esta parte se mantiene IGUAL que tu código original) ...
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

// ... (Lógica de procesamiento de ubicación y hora se mantiene IGUAL) ...
// ... (Copia el bloque de "Procesar Ubicación y Hora" de tu archivo original aquí) ...
$ubicacion_texto = "Uso Personal / Domicilio";
$hora_limite = "";

if ($datos_prestamo) {
    if ($datos_prestamo['tipo'] == 'ESTUDIANTE') {
        $ubicacion_texto = $datos_prestamo['grado'] . ' "' . $datos_prestamo['seccion'] . '"';
    }
    if (strpos($datos_prestamo['observaciones'], 'Destino: Aula') !== false) {
        $parts = explode('|', $datos_prestamo['observaciones']);
        foreach($parts as $parte) {
            if(strpos($parte, 'Destino: Aula') !== false) {
                $ubicacion_texto = "Aula " . trim(str_replace('Destino: Aula', '', $parte));
                $ubicacion_texto = str_replace('"', '', $ubicacion_texto);
            }
        }
    }
    if (strpos($datos_prestamo['observaciones'], 'Devolución límite') !== false) {
        $parts = explode('|', $datos_prestamo['observaciones']);
        foreach($parts as $parte) {
            if(strpos($parte, 'Devolución límite') !== false) {
                $subparts = explode(' a las: ', $parte);
                if (isset($subparts[1])) $hora_limite = trim($subparts[1]);
            }
        }
    }
    $datos_prestamo['ubicacion_final'] = $ubicacion_texto;
    $datos_prestamo['hora_limite'] = $hora_limite;
}

// 2. Traer los libros de ESTE préstamo (IGUAL)
$sql_detalles = "
    SELECT 
        dp.id as id_detalle,
        l.titulo,
        l.id as id_libro,
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

// ==================================================================
// 3. NUEVA LÓGICA: Traer historial AGRUPADO por Préstamo
// ==================================================================
$historial_agrupado = []; // Array final

if ($datos_prestamo) {
    $id_persona = $datos_prestamo['id_persona_solicitante'];
    
    $sql_historial = "
        SELECT 
            p.id as id_prestamo,
            DATE_FORMAT(p.fecha_prestamo, '%d/%m/%Y %H:%i') as fecha,
            l.titulo,
            dp.cantidad
        FROM detalle_prestamo dp
        JOIN prestamos p ON dp.id_prestamo = p.id
        JOIN libros l ON dp.id_libro = l.id
        WHERE p.id_persona_solicitante = $id_persona
          AND dp.estado_devolucion = 'PENDIENTE'
          AND p.id != $id_prestamo
        ORDER BY p.fecha_prestamo DESC
    ";
    
    $res_hist = $conn->query($sql_historial);
    
    // Procesamiento para agrupar
    $temp_grupos = [];
    while($row = $res_hist->fetch_assoc()) {
        $pid = $row['id_prestamo'];
        
        // Si no existe el grupo, crearlo
        if (!isset($temp_grupos[$pid])) {
            $temp_grupos[$pid] = [
                'id_prestamo' => $pid,
                'fecha' => $row['fecha'],
                'libros' => []
            ];
        }
        
        // Agregar el libro al grupo
        $temp_grupos[$pid]['libros'][] = [
            'titulo' => $row['titulo'],
            'cantidad' => $row['cantidad']
        ];
    }
    
    // Reindexar array (quitar claves numéricas asociativas) para JSON limpio
    $historial_agrupado = array_values($temp_grupos);
}

echo json_encode([
    'cabecera' => $datos_prestamo,
    'detalles' => $detalles,
    'otros_pendientes' => $historial_agrupado // Ahora enviamos la estructura agrupada
]);
?>