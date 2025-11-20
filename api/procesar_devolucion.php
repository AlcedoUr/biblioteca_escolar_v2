<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$id_detalle = $data['id_detalle'];
$estado = $data['estado']; // 'BUENO', 'DAÑADO', 'PERDIDO'
$id_libro = $data['id_libro'];
$cantidad = $data['cantidad'];

$conn->begin_transaction();

try {
    // 1. Actualizar el estado en el detalle
    $stmt = $conn->prepare("UPDATE detalle_prestamo SET estado_devolucion = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $id_detalle);
    $stmt->execute();

    // 2. Lógica de Stock (Punto Ciego resuelto)
    // Solo devolvemos al stock si el libro está BUENO.
    if ($estado === 'BUENO') {
        $stmt_stock = $conn->prepare("UPDATE libros SET stock_disponible = stock_disponible + ? WHERE id = ?");
        $stmt_stock->bind_param("ii", $cantidad, $id_libro);
        $stmt_stock->execute();
    }
    // Si está DAÑADO o PERDIDO, el stock físico se reduce permanentemente (ya se restó al prestar).

    // 3. Verificar si TODOS los libros de este préstamo ya fueron devueltos
    // Buscamos el ID del préstamo padre
    $stmt_padre = $conn->prepare("SELECT id_prestamo FROM detalle_prestamo WHERE id = ?");
    $stmt_padre->bind_param("i", $id_detalle);
    $stmt_padre->execute();
    $res_padre = $stmt_padre->get_result();
    $row_padre = $res_padre->fetch_assoc();
    $id_prestamo = $row_padre['id_prestamo'];

    // Contamos cuántos quedan pendientes en ese préstamo
    $stmt_pendientes = $conn->prepare("SELECT COUNT(*) as total FROM detalle_prestamo WHERE id_prestamo = ? AND estado_devolucion = 'PENDIENTE'");
    $stmt_pendientes->bind_param("i", $id_prestamo);
    $stmt_pendientes->execute();
    $res_pend = $stmt_pendientes->get_result();
    $pendientes = $res_pend->fetch_assoc()['total'];

    if ($pendientes == 0) {
        // Si ya no queda nada pendiente, cerramos el préstamo cabecera
        $conn->query("UPDATE prestamos SET estado = 'FINALIZADO' WHERE id = $id_prestamo");
    }

    $conn->commit();
    echo json_encode(['exito' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
?>