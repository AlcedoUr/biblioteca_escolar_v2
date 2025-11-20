<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// 1. CONTADORES PRINCIPALES (KPIs Superiores)
$total_libros = $conn->query("SELECT SUM(stock_total) FROM libros")->fetch_row()[0] ?? 0;
$prestados = $conn->query("SELECT COUNT(*) FROM detalle_prestamo WHERE estado_devolucion = 'PENDIENTE'")->fetch_row()[0] ?? 0;
$extraviados = $conn->query("SELECT COUNT(*) FROM detalle_prestamo WHERE estado_devolucion = 'PERDIDO'")->fetch_row()[0] ?? 0;
$vencidos = $conn->query("SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion_pactada < CURDATE() AND estado = 'PENDIENTE'")->fetch_row()[0] ?? 0;

// 2. DATOS NUEVOS (Fila Inferior)
// A. Estudiantes Registrados (Usuarios Activos)
$estudiantes = $conn->query("SELECT COUNT(*) FROM personas WHERE tipo = 'ESTUDIANTE' AND estado_biblioteca = 'ACTIVO'")->fetch_row()[0] ?? 0;

// B. Libros Disponibles (Stock real en estantería)
$disponibles = $conn->query("SELECT SUM(stock_disponible) FROM libros")->fetch_row()[0] ?? 0;

// C. Tasa de Uso (Porcentaje de libros que NO están en estantería)
$tasa_uso = 0;
if ($total_libros > 0) {
    $libros_fuera = $total_libros - $disponibles;
    $tasa_uso = round(($libros_fuera / $total_libros) * 100);
}

// 3. ACTIVIDAD RECIENTE
$sql_actividad = "
    SELECT 
        p.fecha_prestamo, per.nombres, per.apellidos, l.titulo, dp.estado_devolucion 
    FROM detalle_prestamo dp
    INNER JOIN prestamos p ON dp.id_prestamo = p.id
    INNER JOIN libros l ON dp.id_libro = l.id
    INNER JOIN personas per ON p.id_persona_solicitante = per.id
    ORDER BY p.fecha_prestamo DESC LIMIT 5";

$res_act = $conn->query($sql_actividad);
$actividades = [];
while($row = $res_act->fetch_assoc()) {
    $actividades[] = $row;
}

echo json_encode([
    'total_libros' => $total_libros,
    'prestados' => $prestados,
    'extraviados' => $extraviados,
    'vencidos' => $vencidos,
    'estudiantes' => $estudiantes, // Nuevo
    'disponibles' => $disponibles, // Nuevo
    'tasa_uso' => $tasa_uso,       // Nuevo
    'actividades' => $actividades
]);
?>