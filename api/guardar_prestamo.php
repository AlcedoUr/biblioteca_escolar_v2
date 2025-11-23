<?php
header('Content-Type: application/json');
require_once '../config/db.php';
session_start();

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data['persona_id']) || empty($data['libros'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos básicos']);
    exit;
}

$persona_id = $data['persona_id'];
$usuario_id = $_SESSION['user_id'] ?? 1;
$libros = $data['libros'];

// 1. PROCESAR FECHAS Y HORARIOS
$tipo = $data['tipo_prestamo']; // 'HORAS' o 'DIAS'
$fecha_dev = $data['fecha_devolucion'];

// Obtener horas explícitas del frontend
// Si es DIAS, hora_inicio suele ser la hora actual, pero lo importante es la hora_fin
$hora_inicio_raw = $data['hora_inicio'] ?? date('H:i');
$hora_fin_raw = $data['hora_fin']; 

// Calcular hora límite real (Hora Fin + 10 minutos de tolerancia)
$timeObj = new DateTime($fecha_dev . ' ' . $hora_fin_raw);
$timeObj->modify('+10 minutes');
$hora_limite_fmt = $timeObj->format('H:i'); 
$fecha_hora_fin_real = $timeObj->format('Y-m-d H:i:s'); // Timestamp completo para validación SQL

// Construir observación detallada
$obs_parts = [];
if ($tipo == 'HORAS') {
    $obs_parts[] = "Tipo: En Aula";
    $obs_parts[] = "Horario: " . $hora_inicio_raw . " - " . $hora_fin_raw;
} else {
    $obs_parts[] = "Tipo: Domicilio";
}

$obs_parts[] = "Destino: Aula " . ($data['aula_grado'] ?? '-') . ' "' . ($data['aula_seccion'] ?? '-') . '"';
$obs_parts[] = "Devolución límite a las: " . $hora_limite_fmt;

$observaciones = implode(" | ", $obs_parts);

$conn->begin_transaction();

try {
    // 2. VALIDAR CONFLICTOS CON RESERVAS FUTURAS
    // Verifica si hay reservas confirmadas que inicien ANTES de que este préstamo sea devuelto.
    
    foreach ($libros as $item) {
        $id_libro = $item['id'];
        $cant_solicitada = $item['cantidad'];

        // A. Obtener stock físico total (Inventario)
        $res_stock = $conn->query("SELECT stock_total FROM libros WHERE id = $id_libro");
        $stock_total = $res_stock->fetch_row()[0];

        // B. Obtener stock disponible actual en estantería
        $res_disp = $conn->query("SELECT stock_disponible FROM libros WHERE id = $id_libro");
        $en_estanteria = $res_disp->fetch_row()[0];

        // C. Calcular reservas futuras que chocan con este préstamo
        // Buscamos reservas PENDIENTES cuyo inicio sea <= a mi fecha de devolución real
        $sql_conflicto = "
            SELECT SUM(cantidad) 
            FROM reservas 
            WHERE id_libro = $id_libro 
              AND estado = 'PENDIENTE'
              AND CONCAT(fecha_uso, ' ', hora_inicio) <= '$fecha_hora_fin_real'
              AND CONCAT(fecha_uso, ' ', hora_inicio) >= NOW() 
        ";
        
        $res_conf = $conn->query($sql_conflicto);
        $libros_reservados_futuro = $res_conf->fetch_row()[0] ?? 0;

        // D. Validación Final:
        // Disponibles Reales = (Físicos en Estantería) - (Reservas que entran en conflicto)
        if (($en_estanteria - $libros_reservados_futuro) < $cant_solicitada) {
            throw new Exception("Conflicto de Stock: El libro '{$item['titulo']}' tiene reservas futuras ($libros_reservados_futuro) que impiden este préstamo hasta las $hora_limite_fmt.");
        }
    }

    // 3. INSERTAR PRÉSTAMO (CABECERA)
    $stmt = $conn->prepare("INSERT INTO prestamos (id_persona_solicitante, id_usuario_bibliotecario, fecha_devolucion_pactada, estado, observaciones) VALUES (?, ?, ?, 'PENDIENTE', ?)");
    $stmt->bind_param("iiss", $persona_id, $usuario_id, $fecha_dev, $observaciones);
    
    if (!$stmt->execute()) throw new Exception("Error al guardar el préstamo: " . $stmt->error);
    $id_prestamo = $conn->insert_id;

    // 4. INSERTAR DETALLES Y ACTUALIZAR STOCK
    $stmt_det = $conn->prepare("INSERT INTO detalle_prestamo (id_prestamo, id_libro, cantidad) VALUES (?, ?, ?)");
    $stmt_upd = $conn->prepare("UPDATE libros SET stock_disponible = stock_disponible - ? WHERE id = ?");

    foreach ($libros as $item) {
        // Verificación final de stock (concurrencia)
        $check = $conn->query("SELECT stock_disponible FROM libros WHERE id = " . $item['id']);
        if ($check->fetch_row()[0] < $item['cantidad']) {
             throw new Exception("Stock insuficiente para '{$item['titulo']}' al momento de guardar.");
        }

        // Guardar detalle
        $stmt_det->bind_param("iii", $id_prestamo, $item['id'], $item['cantidad']);
        $stmt_det->execute();

        // Restar stock
        $stmt_upd->bind_param("ii", $item['cantidad'], $item['id']);
        $stmt_upd->execute();
    }

    $conn->commit();
    echo json_encode(['exito' => true, 'mensaje' => 'Préstamo registrado correctamente.']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
?>