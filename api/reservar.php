<?php
header('Content-Type: application/json');
require_once '../config/db.php';
session_start();

$metodo = $_SERVER['REQUEST_METHOD'];

// =============================================================================
// 1. LISTAR RESERVAS (GET)
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
        WHERE r.estado IN ('PENDIENTE', 'ENTREGADA') $filtro_usuario
        ORDER BY r.fecha_uso DESC, r.hora_inicio ASC
    ";
    
    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()) {
        // Formatear fecha amigable
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

    // 1. Validar Datos Básicos
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

    // 2. Obtener STOCK TOTAL FÍSICO del libro (Inventario Real)
    $sql_libro = "SELECT stock_total, titulo FROM libros WHERE id = $id_libro";
    $res_libro = $conn->query($sql_libro);
    $libro = $res_libro->fetch_assoc();
    
    if (!$libro) {
        echo json_encode(['exito' => false, 'mensaje' => 'Libro no encontrado']);
        exit;
    }
    
    $stock_total = (int)$libro['stock_total'];

    // 3. CALCULAR LIBROS NO DISPONIBLES (La Lógica Maestra)
    
    // A. Libros PERDIDOS o DAÑADOS sin resolver (siempre restan stock)
    // (Asumimos que el stock_total cuenta todo, incluidos los rotos hasta que se den de baja)
    // Para simplificar, usaremos el stock_disponible actual como base pesimista O hacemos el cálculo detallado.
    // Vamos por el cálculo detallado:
    
    // A. Préstamos Activos que NO volverán a tiempo (Conflictos Domicilio)
    // Buscamos préstamos PENDIENTES cuya fecha de devolución sea MAYOR o IGUAL a la fecha de reserva.
    $sql_prestamos = "
        SELECT SUM(dp.cantidad) as ocupados
        FROM detalle_prestamo dp
        JOIN prestamos p ON dp.id_prestamo = p.id
        WHERE dp.id_libro = $id_libro 
          AND dp.estado_devolucion = 'PENDIENTE'
          AND p.fecha_devolucion_pactada >= '$fecha'
    ";
    $ocupados_prestamos = $conn->query($sql_prestamos)->fetch_row()[0] ?? 0;

    // B. Reservas Confirmadas que se cruzan en horario
    // (StartA < EndB) and (EndA > StartB)
    $sql_reservas = "
        SELECT SUM(cantidad) as ocupados
        FROM reservas
        WHERE id_libro = $id_libro
          AND fecha_uso = '$fecha'
          AND estado = 'PENDIENTE'
          AND (hora_inicio < '$h_fin' AND hora_fin > '$h_inicio')
    ";
    $ocupados_reservas = $conn->query($sql_reservas)->fetch_row()[0] ?? 0;

    // C. Cálculo Final
    $ocupados_totales = $ocupados_prestamos + $ocupados_reservas;
    $disponibles_futuro = $stock_total - $ocupados_totales;

    if ($disponibles_futuro < $cantidad_solicitada) {
        echo json_encode([
            'exito' => false, 
            'mensaje' => "Stock insuficiente para ese horario. Total: $stock_total. Ocupados: $ocupados_totales. Disponibles: $disponibles_futuro."
        ]);
        exit;
    }

    // 4. Guardar Reserva
    $grado = $data['grado'] ?? '';
    $seccion = $data['seccion'] ?? '';

    $stmt = $conn->prepare("INSERT INTO reservas (id_libro, id_usuario_solicitante, fecha_uso, hora_inicio, hora_fin, cantidad, grado, seccion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')");
    $stmt->bind_param("iisssiss", $id_libro, $id_usuario, $fecha, $h_inicio, $h_fin, $cantidad_solicitada, $grado, $seccion);

    if ($stmt->execute()) {
        echo json_encode(['exito' => true, 'mensaje' => 'Reserva registrada correctamente']);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error BD: ' . $conn->error]);
    }
    exit;
}
?>