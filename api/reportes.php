<?php
header('Content-Type: application/json');
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
// 2. REPORTE DE EXTRAVIADOS
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
// 3. REPORTE DE USO (DASHBOARD AVANZADO)
// ==========================================
if ($tipo == 'uso') {
    // --- FILTROS ---
    $inicio = $conn->real_escape_string($_GET['fecha_inicio'] ?? date('Y-m-01', strtotime('-5 months')));
    $fin = $conn->real_escape_string($_GET['fecha_fin'] ?? date('Y-m-d'));
    $cat = $_GET['categoria'] ?? '';
    $rol = $_GET['rol'] ?? '';

    // Construcción del WHERE dinámico
    $where_p = "WHERE p.fecha_prestamo BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
    
    if ($rol) {
        $where_p .= " AND per.tipo = '$rol'";
    }
    // El filtro de categoría requiere JOIN con libros, se aplica en las subconsultas específicas

    // --- A. KPIs GENERALES ---
    // Total Préstamos en el periodo
    $sql_kpi = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN p.estado = 'PENDIENTE' THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN p.estado = 'FINALIZADO' THEN 1 ELSE 0 END) as finalizados
                FROM prestamos p
                JOIN personas per ON p.id_persona_solicitante = per.id
                JOIN detalle_prestamo dp ON p.id = dp.id_prestamo
                JOIN libros l ON dp.id_libro = l.id
                $where_p";
    
    if ($cat) $sql_kpi .= " AND l.categoria = '$cat'";
    
    $kpis = $conn->query($sql_kpi)->fetch_assoc();
    
    // Tasa de Devolución
    $tasa = ($kpis['total'] > 0) ? round(($kpis['finalizados'] / $kpis['total']) * 100, 1) : 0;
    
    // Promedio por usuario (Simple: Total préstamos / Total usuarios activos en ese periodo)
    $sql_users = "SELECT COUNT(DISTINCT p.id_persona_solicitante) FROM prestamos p JOIN personas per ON p.id_persona_solicitante = per.id $where_p";
    $total_usuarios_activos = $conn->query($sql_users)->fetch_row()[0] ?? 1;
    $promedio = ($total_usuarios_activos > 0) ? round($kpis['total'] / $total_usuarios_activos, 1) : 0;

    // --- B. TENDENCIA (GRÁFICO DE LÍNEAS) ---
    // Agrupado por Mes: Préstamos, Devoluciones, Vencidos (Detectados por fecha pactada)
    $sql_tendencia = "
        SELECT 
            DATE_FORMAT(p.fecha_prestamo, '%Y-%m') as mes,
            COUNT(*) as total_prestamos,
            SUM(CASE WHEN p.estado = 'FINALIZADO' THEN 1 ELSE 0 END) as devueltos,
            SUM(CASE WHEN p.estado = 'PENDIENTE' AND p.fecha_devolucion_pactada < CURDATE() THEN 1 ELSE 0 END) as vencidos
        FROM prestamos p
        JOIN personas per ON p.id_persona_solicitante = per.id
        JOIN detalle_prestamo dp ON p.id = dp.id_prestamo
        JOIN libros l ON dp.id_libro = l.id
        $where_p
    ";
    if ($cat) $sql_tendencia .= " AND l.categoria = '$cat'";
    $sql_tendencia .= " GROUP BY mes ORDER BY mes ASC";
    
    $res_t = $conn->query($sql_tendencia);
    $tendencia = ['labels' => [], 'prestamos' => [], 'devueltos' => [], 'vencidos' => []];
    
    while($r = $res_t->fetch_assoc()) {
        $dateObj   = DateTime::createFromFormat('!Y-m', $r['mes']);
        $mesNombre = $dateObj->format('M Y'); // Ej: Nov 2025
        
        $tendencia['labels'][] = $mesNombre;
        $tendencia['prestamos'][] = $r['total_prestamos'];
        $tendencia['devueltos'][] = $r['devueltos'];
        $tendencia['vencidos'][] = $r['vencidos'];
    }

    // --- C. DISTRIBUCIÓN POR CATEGORÍA (PASTEL) ---
    $sql_cat = "
        SELECT l.categoria, COUNT(*) as cantidad
        FROM detalle_prestamo dp
        JOIN prestamos p ON dp.id_prestamo = p.id
        JOIN libros l ON dp.id_libro = l.id
        JOIN personas per ON p.id_persona_solicitante = per.id
        $where_p
        GROUP BY l.categoria
    ";
    $res_cat = $conn->query($sql_cat);
    $categorias = [];
    while($r = $res_cat->fetch_assoc()) {
        if(empty($r['categoria'])) $r['categoria'] = 'Sin Categoría';
        $categorias[] = $r;
    }

    // --- D. TOP 5 LIBROS (BARRAS) ---
    $sql_top = "
        SELECT l.titulo, l.categoria, COUNT(dp.id) as cantidad
        FROM detalle_prestamo dp 
        JOIN libros l ON dp.id_libro = l.id 
        JOIN prestamos p ON dp.id_prestamo = p.id
        JOIN personas per ON p.id_persona_solicitante = per.id
        $where_p
    ";
    if ($cat) $sql_top .= " AND l.categoria = '$cat'";
    $sql_top .= " GROUP BY dp.id_libro ORDER BY cantidad DESC LIMIT 5";
    
    $res_top = $conn->query($sql_top);
    $top_libros = [];
    while($r = $res_top->fetch_assoc()) {
        $r['titulo_corto'] = substr($r['titulo'], 0, 25) . (strlen($r['titulo'])>25 ? '...' : '');
        $top_libros[] = $r;
    }

    echo json_encode([
        'kpis' => [
            'total' => $kpis['total'] ?? 0, 
            'activos' => $kpis['activos'] ?? 0, 
            'tasa' => $tasa, 
            'promedio' => $promedio
        ],
        'tendencia' => $tendencia,
        'categorias' => $categorias,
        'top_libros' => $top_libros
    ]);
    exit;
}

// ==========================================
// 4. REPORTE DE DEUDORES (Mantenido)
// ==========================================
// ... (Código existente de deudores) ...
if ($tipo == 'deudores') {
    // ... (Tu código actual de deudores va aquí, si no lo has cambiado, déjalo igual)
    // Para no alargar la respuesta, asumo que mantienes el código de deudores
    // que ya tenías en el archivo anterior. Si lo necesitas, avísame.
    $sql = "
        SELECT 
            per.id, per.dni, CONCAT(per.apellidos, ', ', per.nombres) as nombre, 
            per.tipo, per.grado, per.seccion, per.telefono,
            
            SUM(CASE WHEN dp.estado_devolucion = 'PENDIENTE' AND p.fecha_devolucion_pactada < CURDATE() THEN 1 ELSE 0 END) as cant_vencidos,
            SUM(CASE WHEN dp.estado_devolucion = 'PERDIDO' AND dp.estado_resolucion = 'PENDIENTE' THEN 1 ELSE 0 END) as cant_extravios,
            SUM(CASE WHEN dp.estado_devolucion = 'DAÑADO' AND dp.estado_resolucion = 'PENDIENTE' THEN 1 ELSE 0 END) as cant_danios,
            
            GROUP_CONCAT(DISTINCT 
                CASE 
                    WHEN dp.id_persona_causante IS NOT NULL AND dp.id_persona_causante != per.id 
                    THEN CONCAT(cau.apellidos, ' ', cau.nombres)
                    ELSE NULL 
                END 
            SEPARATOR ', ') as causantes_externos,

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
        
        if ($row['fecha_mas_antigua']) {
            $fecha_antigua = new DateTime($row['fecha_mas_antigua']);
            $hoy = new DateTime();
            $row['dias_retraso'] = $hoy->diff($fecha_antigua)->days;
        } else {
            $row['dias_retraso'] = 0;
        }
        
        $tipos = [];
        if ($row['cant_vencidos'] > 0) $tipos[] = 'Vencido';
        if ($row['cant_extravios'] > 0) $tipos[] = 'Extravío';
        if ($row['cant_danios'] > 0) $tipos[] = 'Daño';
        $row['tipo_deuda'] = implode(', ', $tipos);

        $obs = [];
        if ($row['causantes_externos']) $obs[] = "Responsable solidario: " . $row['causantes_externos'];
        if ($row['cant_vencidos'] > 0) $obs[] = "Devolución pendiente ({$row['cant_vencidos']})";
        
        $row['observacion_texto'] = implode('. ', $obs);
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// ==========================================
// 5. DETALLE DEUDA USUARIO
// ==========================================
if ($tipo == 'detalles_usuario') {
    $id_persona = $_GET['id'] ?? 0;
    
    $sql = "
        SELECT 
            l.titulo, l.isbn,
            DATE_FORMAT(p.fecha_prestamo, '%d/%m/%Y') as fecha_prestamo,
            DATE_FORMAT(p.fecha_devolucion_pactada, '%d/%m/%Y') as fecha_vence,
            dp.estado_devolucion,
            
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
        if ($row['estado_devolucion'] == 'PENDIENTE') $row['motivo'] = 'Vencido';
        else $row['motivo'] = ucfirst(strtolower($row['estado_devolucion'])); 
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

echo json_encode([]);
?>