<?php
header('Content-Type: application/json');
require_once '../config/db.php';
session_start();

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data['id_reserva'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'ID de reserva no proporcionado']);
    exit;
}

$id_reserva = $data['id_reserva'];
$id_bibliotecario = $_SESSION['user_id'] ?? 1; // Fallback a admin si no hay sesión (raro)

$conn->begin_transaction();

try {
    // 1. Obtener datos de la reserva
    $sql_r = "SELECT * FROM reservas WHERE id = $id_reserva AND estado = 'PENDIENTE' FOR UPDATE";
    $res_r = $conn->query($sql_r);
    
    if ($res_r->num_rows === 0) {
        throw new Exception("La reserva no está pendiente o no existe.");
    }
    
    $r = $res_r->fetch_assoc();
    $id_libro = $r['id_libro'];
    $cantidad = $r['cantidad'];
    $solicitante = $r['id_usuario_solicitante'];
    
    // Validar Fecha (Solo se puede entregar el día de la reserva)
    if ($r['fecha_uso'] !== date('Y-m-d')) {
        throw new Exception("Solo se puede entregar el material el día programada ({$r['fecha_uso']}).");
    }

    // 2. Verificar Stock Físico Real
    $sql_stock = "SELECT stock_disponible FROM libros WHERE id = $id_libro";
    $stock_actual = $conn->query($sql_stock)->fetch_row()[0];

    if ($stock_actual < $cantidad) {
        throw new Exception("Error crítico: No hay stock físico suficiente ($stock_actual) para cubrir esta reserva ($cantidad).");
    }

    // 3. Crear Préstamo (Cabecera)
    // Obtenemos el id_persona del usuario solicitante
    $q_persona = $conn->query("SELECT id_persona FROM usuarios WHERE id = $solicitante");
    $id_persona_solicitante = $q_persona->fetch_row()[0];

    // Armamos la observación
    $obs = "Reserva #$id_reserva | Horario: {$r['hora_inicio']} - {$r['hora_fin']} | Aula: {$r['grado']} {$r['seccion']}";
    
    $stmt_prestamo = $conn->prepare("INSERT INTO prestamos (id_persona_solicitante, id_usuario_bibliotecario, fecha_devolucion_pactada, estado, observaciones) VALUES (?, ?, CURDATE(), 'PENDIENTE', ?)");
    $stmt_prestamo->bind_param("iis", $id_persona_solicitante, $id_bibliotecario, $obs);
    
    if (!$stmt_prestamo->execute()) throw new Exception("Error creando préstamo.");
    $id_prestamo = $conn->insert_id;

    // 4. Crear Detalle Préstamo
    $stmt_det = $conn->prepare("INSERT INTO detalle_prestamo (id_prestamo, id_libro, cantidad) VALUES (?, ?, ?)");
    $stmt_det->bind_param("iii", $id_prestamo, $id_libro, $cantidad);
    if (!$stmt_det->execute()) throw new Exception("Error creando detalle.");

    // 5. Actualizar Stock Libro (Resta física)
    $conn->query("UPDATE libros SET stock_disponible = stock_disponible - $cantidad WHERE id = $id_libro");

    // 6. Actualizar Reserva a ENTREGADA
    $conn->query("UPDATE reservas SET estado = 'ENTREGADA' WHERE id = $id_reserva");

    $conn->commit();
    echo json_encode(['exito' => true, 'mensaje' => 'Material entregado correctamente.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
?>