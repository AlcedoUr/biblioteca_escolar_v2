<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$id_prestamo = $_GET['id'] ?? 0;

if ($id_prestamo == 0) {
    echo json_encode([]);
    exit;
}

// Traemos los libros de ese préstamo y su estado actual
$sql = "
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

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_prestamo);
$stmt->execute();
$resultado = $stmt->get_result();

$detalles = [];
while($row = $resultado->fetch_assoc()) {
    $detalles[] = $row;
}

echo json_encode($detalles);
?>