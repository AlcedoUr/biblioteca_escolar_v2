<?php
header('Content-Type: application/json');
// Desactivar errores en pantalla para no romper JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

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
// 2. REPORTE DE EXTRAVIADOS (INCIDENCIAS)
// ==========================================
if ($tipo == 'extraviados') {
    $sql = "
        SELECT l.titulo, l.isbn, l.id as id_libro, dp.estado_devolucion, 
               DATE_FORMAT(p.fecha_prestamo, '%d/%m/%Y') as fecha_prestamo, 
               CONCAT(per.nombres, ' ', per.apellidos) as responsable,
               dp.estado_resolucion, dp.tipo_resolucion, dp.monto_compensacion
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
// 3. REPORTE DE USO (DASHBOARD)
// ==========================================
if ($tipo == 'uso') {
    $total_prestamos = $conn->query("SELECT COUNT(*) FROM prestamos")->fetch_row()[0] ?? 0;
    $activos = $conn->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'PENDIENTE'")->fetch_row()[0] ?? 0;
    $finalizados = $conn->query("SELECT COUNT(*) FROM prestamos WHERE estado = 'FINALIZADO'")->fetch_row()[0] ?? 0;
    $tasa_devolucion = ($total_prestamos > 0) ? round(($finalizados / $total_prestamos) * 100, 1) : 0;
    $total_alumnos = $conn->query("SELECT COUNT(*) FROM personas WHERE tipo = 'ESTUDIANTE'")->fetch_row()[0] ?? 1;
    $promedio = round($total_prestamos / ($total_alumnos > 0 ? $total_alumnos : 1), 1);

    // Tendencia
    $tendencia_labels = [];
    $tendencia_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $mes_sql = date('Y-m', strtotime("-$i months")); 
        $mes_label = date('M', strtotime("-$i months")); 
        $sql_t = "SELECT COUNT(*) FROM prestamos WHERE DATE_FORMAT(fecha_prestamo, '%Y-%m') = '$mes_sql'";
        $cnt = $conn->query($sql_t)->fetch_row()[0] ?? 0;
        $tendencia_labels[] = $mes_label;
        $tendencia_data[] = $cnt;
    }

    // Top Libros
    $sql_top = "SELECT l.titulo, COUNT(dp.id) as cantidad FROM detalle_prestamo dp JOIN libros l ON dp.id_libro = l.id GROUP BY dp.id_libro ORDER BY cantidad DESC LIMIT 5";
    $res_top = $conn->query($sql_top);
    $top_labels = []; $top_data = [];
    while($r = $res_top->fetch_assoc()) {
        $top_labels[] = substr($r['titulo'], 0, 20);
        $top_data[] = $r['cantidad'];
    }

    echo json_encode([
        'kpis' => ['total' => $total_prestamos, 'activos' => $activos, 'tasa' => $tasa_devolucion, 'promedio' => $promedio],
        'tendencia' => ['labels' => $tendencia_labels, 'data' => $tendencia_data],
        'top_libros' => ['labels' => $top_labels, 'data' => $top_data],
        'categorias' => [10, 5, 8, 2, 4] // Placeholder
    ]);
    exit;
}

// ==========================================
// 4. REPORTE DE DEUDORES (LISTA PRINCIPAL)
// ==========================================
if ($tipo == 'deudores') {
    $sql = "
        SELECT 
            per.id, per.dni, CONCAT(per.apellidos, ', ', per.nombres) as nombre, 
            per.tipo, per.grado, per.seccion, per.telefono,
            
            -- Contadores
            SUM(CASE WHEN dp.estado_devolucion = 'PENDIENTE' AND p.fecha_devolucion_pactada < CURDATE() THEN 1 ELSE 0 END) as cant_vencidos,
            SUM(CASE WHEN dp.estado_devolucion = 'PERDIDO' AND dp.estado_resolucion = 'PENDIENTE' THEN 1 ELSE 0 END) as cant_extravios,
            SUM(CASE WHEN dp.estado_devolucion = 'DAÑADO' AND dp.estado_resolucion = 'PENDIENTE' THEN 1 ELSE 0 END) as cant_danios,
            
            -- Causantes Externos
            GROUP_CONCAT(DISTINCT 
                CASE 
                    WHEN dp.id_persona_causante IS NOT NULL AND dp.id_persona_causante != per.id 
                    THEN CONCAT(cau.apellidos, ' ', cau.nombres)
                    ELSE NULL 
                END 
            SEPARATOR ', ') as causantes_externos,

            -- Fecha más antigua
            MIN(CASE 
                WHEN dp.estado_devolucion = 'PENDIENTE' THEN p.fecha_devolucion_pactada
                ELSE p.fecha_prestamo 
            END) as fecha_mas_antigua

        FROM detalle_prestamo dp
        JOIN prestamos p ON dp.id_prestamo = p.id
        JOIN personas per ON p.id_persona_solicitante = per.id
        LEFT JOIN personas cau ON dp.id_persona_causante = cau.id
        WHERE 
            (dp.estado_devolucion = 'PENDIENTE' AND p.fecha_devolucion_pactada < CURDATE())
            OR
            (dp.estado_devolucion IN ('DAÑADO', 'PERDIDO') AND dp.estado_resolucion = 'PENDIENTE')
        GROUP BY per.id
        ORDER BY per.tipo ASC, per.apellidos ASC
    ";

    $res = $conn->query($sql);
    $data = [];
    
    while($row = $res->fetch_assoc()) {
        $row['total_deuda'] = $row['cant_vencidos'] + $row['cant_extravios'] + $row['cant_danios'];
        
        // Antigüedad
        if ($row['fecha_mas_antigua']) {
            $fecha_antigua = new DateTime($row['fecha_mas_antigua']);
            $hoy = new DateTime();
            $row['dias_retraso'] = $hoy->diff($fecha_antigua)->days;
        } else {
            $row['dias_retraso'] = 0;
        }
        
        // Tipos de deuda
        $tipos = [];
        if ($row['cant_vencidos'] > 0) $tipos[] = 'Vencido';
        if ($row['cant_extravios'] > 0) $tipos[] = 'Extravío';
        if ($row['cant_danios'] > 0) $tipos[] = 'Daño';
        $row['tipo_deuda'] = implode(', ', $tipos);

        // Observación inteligente
        $obs = [];
        if ($row['causantes_externos']) {
            $obs[] = "Responsable solidario: " . $row['causantes_externos'];
        }
        if ($row['cant_vencidos'] > 0) $obs[] = "Devolución pendiente ({$row['cant_vencidos']})";
        if ($row['cant_extravios'] > 0) $obs[] = "Regularizar pérdida ({$row['cant_extravios']})";
        
        $row['observacion_texto'] = implode('. ', $obs);

        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// ==========================================
// 5. REPORTE DETALLADO (CSV)
// ==========================================
if ($tipo == 'deudores_detalle') {
    $sql = "
        SELECT 
            per.dni, CONCAT(per.apellidos, ', ', per.nombres) as nombre_usuario, 
            per.tipo as rol,
            l.isbn, l.titulo,
            p.fecha_prestamo, p.fecha_devolucion_pactada,
            dp.estado_devolucion
        FROM detalle_prestamo dp
        JOIN prestamos p ON dp.id_prestamo = p.id
        JOIN personas per ON p.id_persona_solicitante = per.id
        JOIN libros l ON dp.id_libro = l.id
        WHERE 
            (dp.estado_devolucion = 'PENDIENTE' AND p.fecha_devolucion_pactada < CURDATE())
            OR
            (dp.estado_devolucion IN ('DAÑADO', 'PERDIDO') AND dp.estado_resolucion = 'PENDIENTE')
    ";
    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()) { $data[] = $row; }
    echo json_encode($data);
    exit;
}

// ==========================================
// 6. DETALLE DE DEUDA POR USUARIO (MODAL)
// ==========================================
if ($tipo == 'detalles_usuario') {
    $id_persona = $_GET['id'] ?? 0;
    
    $sql = "
        SELECT 
            l.titulo, l.isbn,
            DATE_FORMAT(p.fecha_prestamo, '%d/%m/%Y') as fecha_prestamo,
            DATE_FORMAT(p.fecha_devolucion_pactada, '%d/%m/%Y') as fecha_vence,
            dp.estado_devolucion,
            
            -- Lógica de Causante
            CASE 
                WHEN dp.id_persona_causante IS NOT NULL AND dp.id_persona_causante != p.id_persona_solicitante 
                THEN CONCAT(cau.nombres, ' ', cau.apellidos)
                ELSE 'El Solicitante'
            END as responsable_real

        FROM detalle_prestamo dp
        JOIN prestamos p ON dp.id_prestamo = p.id
        JOIN libros l ON dp.id_libro = l.id
        LEFT JOIN personas cau ON dp.id_persona_causante = cau.id
        WHERE 
            p.id_persona_solicitante = $id_persona
            AND (
                (dp.estado_devolucion = 'PENDIENTE' AND p.fecha_devolucion_pactada < CURDATE())
                OR
                (dp.estado_devolucion IN ('DAÑADO', 'PERDIDO') AND dp.estado_resolucion = 'PENDIENTE')
            )
    ";

    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()) {
        // Etiqueta amigable
        if ($row['estado_devolucion'] == 'PENDIENTE') $row['motivo'] = 'Vencido';
        else $row['motivo'] = ucfirst(strtolower($row['estado_devolucion'])); 
        
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// Si no coincide ninguno
echo json_encode([]);
?>