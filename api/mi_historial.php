<?php
header('Content-Type: application/json');
require_once '../config/db.php';
session_start();

// --- 1. VERIFICAR SESIÓN Y OBTENER ID DE PERSONA ---
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    http_response_code(401);
    exit;
}
$user_id = $_SESSION['user_id'];

// Buscamos el id_persona correspondiente al user_id de la sesión
$stmt_persona = $conn->prepare("SELECT id_persona FROM usuarios WHERE id = ?");
$stmt_persona->bind_param("i", $user_id);
$stmt_persona->execute();
$result_persona = $stmt_persona->get_result();
if ($result_persona->num_rows == 0) {
    echo json_encode(['exito' => false, 'mensaje' => 'Usuario no vinculado a una persona']);
    http_response_code(404);
    exit;
}
$id_persona = $result_persona->fetch_assoc()['id_persona'];


// --- 2. OBTENER HISTORIAL DE PRÉSTAMOS ---
$sql_prestamos = "SELECT 
                    p.id,
                    l.titulo,
                    l.autor,
                    p.fecha_prestamo,
                    p.fecha_devolucion_estimada,
                    p.fecha_devolucion_real,
                    p.estado_prestamo
                  FROM prestamos p
                  JOIN libros l ON p.id_libro = l.id
                  WHERE p.id_persona_solicitante = ?
                  ORDER BY p.fecha_prestamo DESC";
$stmt_prestamos = $conn->prepare($sql_prestamos);
$stmt_prestamos->bind_param("i", $id_persona);
$stmt_prestamos->execute();
$historial = $stmt_prestamos->get_result()->fetch_all(MYSQLI_ASSOC);


// --- 3. CALCULAR ESTADÍSTICAS ---

// 3.1. Libro más prestado
$sql_libro_fav = "SELECT l.titulo, COUNT(p.id) as veces_prestado
                  FROM prestamos p
                  JOIN libros l ON p.id_libro = l.id
                  WHERE p.id_persona_solicitante = ?
                  GROUP BY l.titulo
                  ORDER BY veces_prestado DESC
                  LIMIT 1";
$stmt_libro_fav = $conn->prepare($sql_libro_fav);
$stmt_libro_fav->bind_param("i", $id_persona);
$stmt_libro_fav->execute();
$libro_favorito = $stmt_libro_fav->get_result()->fetch_assoc();


// 3.2. Contadores para Score de Reputación
$total_prestamos = count($historial);
$vencidos = 0;
$a_tiempo = 0;

foreach ($historial as $prestamo) {
    if ($prestamo['estado_prestamo'] == 'DEVUELTO') {
        if ($prestamo['fecha_devolucion_real'] > $prestamo['fecha_devolucion_estimada']) {
            $vencidos++;
        } else {
            $a_tiempo++;
        }
    } elseif ($prestamo['estado_prestamo'] == 'PRESTADO' && date('Y-m-d') > $prestamo['fecha_devolucion_estimada']) {
        $vencidos++;
    }
}

// 3.3. Reservas canceladas (asumimos que existe una tabla 'reservas' con un estado)
// Nota: Si la tabla o el estado es diferente, esto necesitará ajuste.
$sql_reservas_canceladas = "SELECT COUNT(id) as canceladas FROM reservas WHERE id_persona_reserva = ? AND estado = 'CANCELADA'";
$stmt_reservas = $conn->prepare($sql_reservas_canceladas);
$stmt_reservas->bind_param("i", $id_persona);
$stmt_reservas->execute();
$reservas_canceladas = $stmt_reservas->get_result()->fetch_assoc()['canceladas'] ?? 0;


// --- 4. CALCULAR SCORE DE REPUTACIÓN ---
$score = 100;
if ($total_prestamos > 0) {
    // Cada préstamo vencido resta 5 puntos
    $score -= ($vencidos * 5);
    // Cada reserva cancelada resta 2 puntos
    $score -= ($reservas_canceladas * 2);
    // Cada préstamo devuelto a tiempo suma 1 punto (para incentivar)
    $score += $a_tiempo;
} else {
    $score = 100; // Si no tiene historial, su reputación está intacta.
}
// El score no puede ser menor que 0 o mayor que 100
$score = max(0, min(100, $score)); 


// --- 5. CONSOLIDAR Y DEVOLVER RESPUESTA ---
$respuesta = [
    'exito' => true,
    'historial' => $historial,
    'estadisticas' => [
        'libro_favorito' => $libro_favorito ? $libro_favorito['titulo'] : 'N/A',
        'total_prestamos' => $total_prestamos,
        'vencidos' => $vencidos,
        'a_tiempo' => $a_tiempo,
        'reservas_canceladas' => (int)$reservas_canceladas,
        'score_reputacion' => round($score)
    ]
];

echo json_encode($respuesta);
?>