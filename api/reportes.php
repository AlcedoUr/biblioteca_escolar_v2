<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$tipo = $_GET['tipo'] ?? '';

// ==========================================
// 1. REPORTE DE INVENTARIO
// ==========================================
if ($tipo == 'inventario') {
    $sql = "SELECT * FROM libros ORDER BY titulo ASC";
    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    exit;
}

// ==========================================
// 2. REPORTE DE EXTRAVIADOS / INCIDENCIAS
// ==========================================
if ($tipo == 'extraviados') {
    $sql = "
        SELECT l.titulo, l.autor, dp.estado_devolucion, 
               DATE_FORMAT(p.fecha_prestamo, '%d/%m/%Y') as fecha_prestamo, 
               CONCAT(per.nombres, ' ', per.apellidos) as responsable
        FROM detalle_prestamo dp
        INNER JOIN libros l ON dp.id_libro = l.id
        INNER JOIN prestamos p ON dp.id_prestamo = p.id
        INNER JOIN personas per ON p.id_persona_solicitante = per.id
        WHERE dp.estado_devolucion = 'PERDIDO' OR dp.estado_devolucion = 'DAÑADO'
        ORDER BY p.fecha_prestamo DESC
    ";
    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    exit;
}

// ==========================================
// 3. REPORTE DE DEUDORES (MOROSOS)
// ==========================================
if ($tipo == 'deudores') {
    $sql = "
        SELECT per.dni, CONCAT(per.apellidos, ', ', per.nombres) as nombre, 
               per.tipo, per.grado, per.seccion, 
               COUNT(dp.id) as libros_pendientes
        FROM prestamos p
        INNER JOIN personas per ON p.id_persona_solicitante = per.id
        INNER JOIN detalle_prestamo dp ON p.id = dp.id_prestamo
        WHERE dp.estado_devolucion = 'PENDIENTE'
        GROUP BY per.id
        HAVING libros_pendientes > 0
        ORDER BY libros_pendientes DESC
    ";
    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    echo json_encode($data);
    exit;
}

// ==========================================
// 4. REPORTE DE USO (ESTADÍSTICAS)
// ==========================================
if ($tipo == 'uso') {
    // A. KPIs Generales
    // Total histórico de préstamos
    $total_prestamos = $conn->query("SELECT COUNT(*) FROM prestamos")->fetch_row()[0] ?? 0;
    
    // Préstamos activos hoy
    $activos = $conn->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'PENDIENTE'")->fetch_row()[0] ?? 0;
    
    // Tasa de devolución (Finalizados / Total)
    $finalizados = $conn->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'FINALIZADO'")->fetch_row()[0] ?? 0;
    $tasa_devolucion = ($total_prestamos > 0) ? round(($finalizados / $total_prestamos) * 100, 1) : 0;

    // Promedio por estudiante (Total préstamos / Total estudiantes)
    $total_alumnos = $conn->query("SELECT COUNT(*) FROM personas WHERE tipo = 'ESTUDIANTE'")->fetch_row()[0] ?? 1;
    $promedio = round($total_prestamos / ($total_alumnos > 0 ? $total_alumnos : 1), 1);

    // B. Tendencia (Últimos 6 meses)
    // Generamos los últimos 6 meses y contamos cuántos préstamos hubo en cada uno
    $tendencia_labels = [];
    $tendencia_data = [];
    
    for ($i = 5; $i >= 0; $i--) {
        // Generar fecha ej: '2023-11'
        $mes_sql = date('Y-m', strtotime("-$i months")); 
        // Generar etiqueta ej: 'Nov'
        $mes_label = date('M', strtotime("-$i months")); 
        
        // Consulta por mes
        $sql_t = "SELECT COUNT(*) FROM prestamos WHERE DATE_FORMAT(fecha_prestamo, '%Y-%m') = '$mes_sql'";
        $cnt = $conn->query($sql_t)->fetch_row()[0] ?? 0;
        
        $tendencia_labels[] = $mes_label;
        $tendencia_data[] = $cnt;
    }

    // C. Top 5 Libros Más Prestados
    $sql_top = "
        SELECT l.titulo, COUNT(dp.id) as cantidad 
        FROM detalle_prestamo dp 
        JOIN libros l ON dp.id_libro = l.id 
        GROUP BY dp.id_libro 
        ORDER BY cantidad DESC 
        LIMIT 5
    ";
    $res_top = $conn->query($sql_top);
    $top_labels = [];
    $top_data = [];
    while($r = $res_top->fetch_assoc()) {
        // Cortamos el título si es muy largo para que el gráfico se vea bien
        $titulo = strlen($r['titulo']) > 20 ? substr($r['titulo'], 0, 20) . '...' : $r['titulo'];
        $top_labels[] = $titulo;
        $top_data[] = $r['cantidad'];
    }

    // D. Categorías (Simulado)
    // Como la BD actual no tiene campo 'categoria' en la tabla libros,
    // enviamos datos simulados para que el gráfico de pastel no salga roto.
    // En el futuro, puedes agregar el campo 'categoria' a la tabla 'libros'.
    $categorias_data = [15, 10, 5, 8, 2]; 

    echo json_encode([
        'kpis' => [
            'total' => $total_prestamos,
            'activos' => $activos,
            'tasa' => $tasa_devolucion,
            'promedio' => $promedio
        ],
        'tendencia' => [
            'labels' => $tendencia_labels, 
            'data' => $tendencia_data
        ],
        'top_libros' => [
            'labels' => $top_labels, 
            'data' => $top_data
        ],
        'categorias' => $categorias_data
    ]);
    exit;
}

// Si no coincide nada, devolvemos array vacío
echo json_encode([]);
?>