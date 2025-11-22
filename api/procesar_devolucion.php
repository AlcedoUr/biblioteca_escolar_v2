<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$id_detalle = $data['id_detalle'];
$id_libro = $data['id_libro'];
$cant_total = (int)$data['cantidad_total'];
$cant_proc = (int)$data['cantidad_procesar'];
$estado = $data['estado']; 
// NUEVO: Recibimos el causante. Si viene nulo, usamos el solicitante original (lógica se maneja abajo)
$id_causante = $data['id_causante'] ?? null; 

$conn->begin_transaction();

try {
    // Obtener el ID del solicitante original por si no se envió un causante específico
    // (Para asegurar que siempre haya un responsable registrado en la incidencia)
    $qry_solicitante = $conn->query("
        SELECT p.id_persona_solicitante, dp.id_prestamo 
        FROM detalle_prestamo dp 
        JOIN prestamos p ON dp.id_prestamo = p.id 
        WHERE dp.id = $id_detalle
    ");
    $row_sol = $qry_solicitante->fetch_assoc();
    $id_prestamo = $row_sol['id_prestamo'];
    
    // Si no se seleccionó un "Culpable distinto", el responsable es el solicitante
    if (empty($id_causante)) {
        $id_causante = $row_sol['id_persona_solicitante'];
    }

    // --- LOGICA DE ACTUALIZACIÓN (ROW SPLITTING) ---

    // CASO A: Procesamos TODO lo restante
    if ($cant_proc == $cant_total) {
        $stmt = $conn->prepare("UPDATE detalle_prestamo SET estado_devolucion = ?, id_persona_causante = ? WHERE id = ?");
        $stmt->bind_param("sii", $estado, $id_causante, $id_detalle);
        $stmt->execute();
    } 
    // CASO B: Procesamos PARCIALMENTE (dividir fila)
    else {
        // 1. Restar a la fila original (se queda como PENDIENTE y sin causante aún)
        $nuevo_pendiente = $cant_total - $cant_proc;
        $stmt_upd = $conn->prepare("UPDATE detalle_prestamo SET cantidad = ? WHERE id = ?");
        $stmt_upd->bind_param("ii", $nuevo_pendiente, $id_detalle);
        $stmt_upd->execute();

        // 2. Insertar NUEVA fila con la incidencia y el CAUSANTE
        $stmt_ins = $conn->prepare("INSERT INTO detalle_prestamo (id_prestamo, id_libro, cantidad, estado_devolucion, id_persona_causante) VALUES (?, ?, ?, ?, ?)");
        $stmt_ins->bind_param("iiisi", $id_prestamo, $id_libro, $cant_proc, $estado, $id_causante);
        $stmt_ins->execute();
    }

    // LÓGICA DE STOCK (Solo devolvemos al stock si está BUENO)
    if ($estado === 'BUENO') {
        $stmt_stock = $conn->prepare("UPDATE libros SET stock_disponible = stock_disponible + ? WHERE id = ?");
        $stmt_stock->bind_param("ii", $cant_proc, $id_libro);
        $stmt_stock->execute();
    }

    // VERIFICAR SI SE CIERRA EL PRÉSTAMO
    $chk = $conn->query("SELECT COUNT(*) as pendientes FROM detalle_prestamo WHERE id_prestamo = $id_prestamo AND estado_devolucion = 'PENDIENTE'");
    $pendientes = $chk->fetch_assoc()['pendientes'];

    if ($pendientes == 0) {
        $conn->query("UPDATE prestamos SET estado = 'FINALIZADO', fecha_devolucion_real = NOW() WHERE id = $id_prestamo");
    }

    $conn->commit();
    echo json_encode(['exito' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
?>