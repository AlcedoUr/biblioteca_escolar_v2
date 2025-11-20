<?php
header('Content-Type: application/json');
require_once '../config/db.php';
session_start();

$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validaciones básicas
if (empty($data['persona_id']) || empty($data['libros'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos']);
    exit;
}

$persona_id = $data['persona_id'];
$libros = $data['libros']; // Array de libros [{id: 1, cantidad: 5}, ...]
$usuario_id = $_SESSION['user_id']; // El bibliotecario logueado

// INICIAR TRANSACCIÓN (Todo o Nada)
$conn->begin_transaction();

try {
    // 1. Crear Cabecera del Préstamo
    // Calculamos fecha devolución (ej: 7 días después)
    $fecha_dev = date('Y-m-d', strtotime('+7 days'));
    
    $stmt = $conn->prepare("INSERT INTO prestamos (id_persona_solicitante, id_usuario_bibliotecario, fecha_devolucion_pactada, estado) VALUES (?, ?, ?, 'PENDIENTE')");
    $stmt->bind_param("iis", $persona_id, $usuario_id, $fecha_dev);
    $stmt->execute();
    $id_prestamo = $conn->insert_id; // Obtenemos el ID generado

    // 2. Insertar Detalles y Restar Stock
    $stmt_detalle = $conn->prepare("INSERT INTO detalle_prestamo (id_prestamo, id_libro, cantidad) VALUES (?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE libros SET stock_disponible = stock_disponible - ? WHERE id = ? AND stock_disponible >= ?");

    foreach ($libros as $item) {
        $libro_id = $item['id'];
        $cantidad = $item['cantidad'];

        // A. Insertar en detalle
        $stmt_detalle->bind_param("iii", $id_prestamo, $libro_id, $cantidad);
        $stmt_detalle->execute();

        // B. Restar stock (Validando que no quede negativo)
        $stmt_stock->bind_param("iii", $cantidad, $libro_id, $cantidad);
        $stmt_stock->execute();

        if ($stmt_stock->affected_rows === 0) {
            // Si no afectó filas, es que no había suficiente stock
            throw new Exception("No hay suficiente stock para el libro ID: " . $libro_id);
        }
    }

    // Si llegamos aquí, todo salió bien
    $conn->commit();
    echo json_encode(['exito' => true, 'mensaje' => 'Préstamo registrado correctamente']);

} catch (Exception $e) {
    // Si algo falló, deshacemos todo
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>