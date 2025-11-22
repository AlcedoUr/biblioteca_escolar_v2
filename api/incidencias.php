<?php
header('Content-Type: application/json');
// Desactivar visualización de errores en pantalla para no romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// =================================================================
// 1. LISTAR INCIDENCIAS (GET)
// =================================================================
if ($metodo == 'GET') {
    $estado = $_GET['estado'] ?? ''; 
    $busqueda = $_GET['q'] ?? '';
    
    // Solo buscamos libros marcados como DAÑADO o PERDIDO
    $where = "WHERE (dp.estado_devolucion = 'DAÑADO' OR dp.estado_devolucion = 'PERDIDO')";
    
    if ($estado && $estado !== 'TODOS') {
        $where .= " AND dp.estado_resolucion = '$estado'";
    }
    
    if ($busqueda) {
        $where .= " AND (l.titulo LIKE '%$busqueda%' OR l.isbn LIKE '%$busqueda%' OR 
                         p_sol.nombres LIKE '%$busqueda%' OR p_sol.apellidos LIKE '%$busqueda%' OR
                         p_cau.nombres LIKE '%$busqueda%' OR p_cau.apellidos LIKE '%$busqueda%')";
    }

    $sql = "
        SELECT 
            dp.id as id_detalle,
            l.titulo,
            l.isbn,
            l.id as id_libro,
            DATE_FORMAT(p.fecha_prestamo, '%d/%m/%Y') as fecha_incidente,
            dp.estado_devolucion as tipo_incidente,
            
            -- Solicitante
            CONCAT(p_sol.nombres, ' ', p_sol.apellidos) as solicitante,
            p_sol.tipo as rol_solicitante,
            
            -- Causante
            CONCAT(p_cau.nombres, ' ', p_cau.apellidos) as causante,
            p_cau.tipo as rol_causante,
            
            -- Resolución
            dp.estado_resolucion,
            dp.tipo_resolucion,
            dp.monto_compensacion,
            dp.observaciones_incidencia
            
        FROM detalle_prestamo dp
        JOIN prestamos p ON dp.id_prestamo = p.id
        JOIN libros l ON dp.id_libro = l.id
        JOIN personas p_sol ON p.id_persona_solicitante = p_sol.id
        LEFT JOIN personas p_cau ON dp.id_persona_causante = p_cau.id
        $where
        ORDER BY p.fecha_prestamo DESC
    ";

    $res = $conn->query($sql);

    if (!$res) {
        // Si hay error SQL, devolvemos el error en JSON para verlo en la consola
        echo json_encode(['error' => true, 'mensaje' => $conn->error]);
        exit;
    }

    $data = [];
    $stats = ['pendientes' => 0, 'resueltos' => 0, 'dinero' => 0];

    while($row = $res->fetch_assoc()) {
        // Lógica visual: Causante
        if (empty($row['causante']) || $row['causante'] === $row['solicitante']) {
            $row['causante_display'] = '-';
        } else {
            $row['causante_display'] = $row['causante'];
        }

        // Estadísticas
        if ($row['estado_resolucion'] == 'PENDIENTE') $stats['pendientes']++;
        else $stats['resueltos']++;
        
        $stats['dinero'] += (float)$row['monto_compensacion'];

        $data[] = $row;
    }

    echo json_encode(['data' => $data, 'stats' => $stats]);
    exit;
}

// =================================================================
// 2. RESOLVER INCIDENCIA (POST)
// =================================================================
if ($metodo == 'POST') {
    $json = file_get_contents('php://input');
    $d = json_decode($json, true);

    if (empty($d['id_detalle']) || empty($d['tipo_resolucion'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
        exit;
    }

    $id = $d['id_detalle'];
    $tipo = $d['tipo_resolucion'];
    $monto = (float)($d['monto'] ?? 0);
    $obs = $conn->real_escape_string($d['observaciones'] ?? '');
    
    $sql = "UPDATE detalle_prestamo SET 
            estado_resolucion = 'RESUELTO',
            tipo_resolucion = '$tipo',
            monto_compensacion = $monto,
            observaciones_incidencia = '$obs',
            fecha_resolucion = NOW()
            WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(['exito' => true]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => $conn->error]);
    }
    exit;
}
?>