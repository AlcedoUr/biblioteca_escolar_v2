<?php
header('Content-Type: application/json');
require_once '../config/db.php';
session_start();

// 1. Recibir y decodificar el JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// 2. Validaciones básicas
if (empty($data['persona_id']) || empty($data['libros'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos del solicitante o libros']);
    exit;
}

$persona_id = $data['persona_id'];
$libros = $data['libros']; 
$usuario_id = $_SESSION['user_id']; 

// 3. Lógica de Fechas y Tipos
// Recibimos la fecha de devolución del frontend. Si no viene, calculamos 3 días por defecto.
$fecha_dev = $data['fecha_devolucion'] ?? date('Y-m-d', strtotime('+3 days'));
$tipo_prestamo = $data['tipo_prestamo'] ?? 'DOMICILIO';
$hora_limite = $data['hora_limite'] ?? null;

// Construir la observación con los detalles del préstamo
$obs_parts = [];

if ($tipo_prestamo === 'AULA') {
    $obs_parts[] = "Tipo: En Aula";
} else {
    $obs_parts[] = "Tipo: Domicilio";
}

// CORRECCIÓN: Agregar la hora límite SIEMPRE que exista, no solo para Aula
if ($hora_limite) {
    // Usamos el formato estándar que espera el parser de historial.php
    $obs_parts[] = "Devolución límite a las: " . $hora_limite;
}

// Si el usuario es docente y envió datos de aula, los agregamos
if (!empty($data['aula_grado']) && !empty($data['aula_seccion'])) {
    $obs_parts[] = "Destino: Aula " . $data['aula_grado'] . ' "' . $data['aula_seccion'] . '"';
}

$observaciones = implode(" | ", $obs_parts);


// 4. Iniciar Transacción
$conn->begin_transaction();

try {
    // A. Insertar Cabecera del Préstamo
    $stmt = $conn->prepare("INSERT INTO prestamos (id_persona_solicitante, id_usuario_bibliotecario, fecha_devolucion_pactada, estado, observaciones) VALUES (?, ?, ?, 'PENDIENTE', ?)");
    $stmt->bind_param("iiss", $persona_id, $usuario_id, $fecha_dev, $observaciones);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al crear el préstamo: " . $stmt->error);
    }
    
    $id_prestamo = $conn->insert_id;

    // Preparar sentencias para el detalle y actualización de stock
    $stmt_detalle = $conn->prepare("INSERT INTO detalle_prestamo (id_prestamo, id_libro, cantidad) VALUES (?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE libros SET stock_disponible = stock_disponible - ? WHERE id = ? AND stock_disponible >= ?");

    // B. Procesar cada libro del carrito
    foreach ($libros as $item) {
        $libro_id = $item['id'];
        $cantidad = $item['cantidad'];

        // 1. Insertar en detalle
        $stmt_detalle->bind_param("iii", $id_prestamo, $libro_id, $cantidad);
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al agregar libro al detalle.");
        }

        // 2. Restar stock (Validando que no quede negativo con la cláusula WHERE)
        $stmt_stock->bind_param("iii", $cantidad, $libro_id, $cantidad);
        $stmt_stock->execute();

        if ($stmt_stock->affected_rows === 0) {
            // Si no afectó filas, es que no había suficiente stock disponible en ese instante
            // (puede pasar si otro usuario se llevó el libro milisegundos antes)
            throw new Exception("Stock insuficiente para el libro ID: " . $libro_id . ". Verifique disponibilidad.");
        }
    }

    // Si todo salió bien, confirmamos
    $conn->commit();
    echo json_encode(['exito' => true, 'mensaje' => 'Préstamo registrado con éxito']);

} catch (Exception $e) {
    // Si algo falló, deshacemos todo
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>