<?php
header('Content-Type: application/json');
require_once '../config/db.php';
session_start();

$metodo = $_SERVER['REQUEST_METHOD'];

// =============================================================================
// 1. LISTAR RESERVAS (GET) - HISTORIAL COMPLETO
// =============================================================================
if ($metodo == 'GET') {
    $id_usuario = $_SESSION['user_id'] ?? 0;
    $rol = $_SESSION['user_rol'] ?? '';
    
    // Si es DOCENTE, solo ve sus reservas. Si es ADMIN/BIBLIO, ve todas.
    $filtro_usuario = ($rol == 'DOCENTE') ? "AND r.id_usuario_solicitante = (SELECT id FROM usuarios WHERE id = $id_usuario)" : "";

    $sql = "
        SELECT 
            r.id, 
            r.fecha_uso, 
            DATE_FORMAT(r.hora_inicio, '%H:%i') as h_inicio,
            DATE_FORMAT(r.hora_fin, '%H:%i') as h_fin,
            r.cantidad, 
            r.grado, 
            r.seccion,
            r.estado,
            l.titulo,
            l.imagen_portada,
            CONCAT(p.nombres, ' ', p.apellidos) as solicitante
        FROM reservas r
        JOIN libros l ON r.id_libro = l.id
        JOIN usuarios u ON r.id_usuario_solicitante = u.id
        LEFT JOIN personas p ON u.id_persona = p.id
        WHERE 1=1 $filtro_usuario
        ORDER BY r.fecha_uso DESC, r.hora_inicio ASC
    ";
    
    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()) {
        $fecha = new DateTime($row['fecha_uso']);
        $row['fecha_fmt'] = $fecha->format('d/m/Y');
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// =============================================================================
// 2. CREAR RESERVA (POST)
// =============================================================================
if ($metodo == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id_libro']) || empty($data['fecha']) || empty($data['hora_inicio']) || empty($data['hora_fin']) || empty($data['cantidad'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos obligatorios']);
        exit;
    }

    $id_libro = $data['id_libro'];
    $fecha = $data['fecha'];
    $h_inicio = $data['hora_inicio'];
    $h_fin = $data['hora_fin'];
    $cantidad_solicitada = (int)$data['cantidad'];
    $id_usuario = $_SESSION['user_id'];

    // --- SEGURIDAD: VALIDACIÓN DE CANTIDAD ---
    if ($cantidad_solicitada <= 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'La cantidad debe ser mayor a 0.']);
        exit;
    }
    // -----------------------------------------

    $sql_libro = "SELECT stock_total, titulo FROM libros WHERE id = $id_libro";
    $res_libro = $conn->query($sql_libro);
    $libro = $res_libro->fetch_assoc();
    
    if (!$libro) {
        echo json_encode(['exito' => false, 'mensaje' => 'Libro no encontrado']);
        exit;
    }
    
    $stock_total = (int)$libro['stock_total'];

    // Cálculos de disponibilidad (Conflictos con préstamos y otras reservas)
    $sql_prestamos = "SELECT SUM(dp.cantidad) FROM detalle_prestamo dp JOIN prestamos p ON dp.id_prestamo = p.id WHERE dp.id_libro = $id_libro AND dp.estado_devolucion = 'PENDIENTE' AND p.fecha_devolucion_pactada >= '$fecha'";
    $ocupados_prestamos = $conn->query($sql_prestamos)->fetch_row()[0] ?? 0;

    $sql_reservas = "SELECT SUM(cantidad) FROM reservas WHERE id_libro = $id_libro AND fecha_uso = '$fecha' AND estado = 'PENDIENTE' AND (hora_inicio < '$h_fin' AND hora_fin > '$h_inicio')";
    $ocupados_reservas = $conn->query($sql_reservas)->fetch_row()[0] ?? 0;

    $disponibles_futuro = $stock_total - ($ocupados_prestamos + $ocupados_reservas);

    if ($disponibles_futuro < $cantidad_solicitada) {
        echo json_encode(['exito' => false, 'mensaje' => "Stock insuficiente. Disponibles: $disponibles_futuro."]);
        exit;
    }

    $grado = $data['grado'] ?? '';
    $seccion = $data['seccion'] ?? '';

    $stmt = $conn->prepare("INSERT INTO reservas (id_libro, id_usuario_solicitante, fecha_uso, hora_inicio, hora_fin, cantidad, grado, seccion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')");
    $stmt->bind_param("iisssiss", $id_libro, $id_usuario, $fecha, $h_inicio, $h_fin, $cantidad_solicitada, $grado, $seccion);

    if ($stmt->execute()) {
        echo json_encode(['exito' => true, 'mensaje' => 'Reserva registrada']);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error BD: ' . $conn->error]);
    }
    exit;
}

// =============================================================================
// 3. CANCELAR RESERVA (DELETE)
// =============================================================================
if ($metodo == 'DELETE') {
    $id = $_GET['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['exito' => false, 'mensaje' => 'ID no proporcionado']);
        exit;
    }

    // Solo cancelamos si está PENDIENTE
    $sql = "UPDATE reservas SET estado = 'CANCELADA' WHERE id = $id AND estado = 'PENDIENTE'";
    
    if ($conn->query($sql)) {
        if ($conn->affected_rows > 0) {
            echo json_encode(['exito' => true, 'mensaje' => 'Reserva cancelada correctamente.']);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'No se pudo cancelar (tal vez ya no está pendiente).']);
        }
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error BD: ' . $conn->error]);
    }
    exit;
}
?>