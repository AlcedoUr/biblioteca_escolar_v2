<?php
header('Content-Type: application/json');
// Desactivar errores visibles para evitar romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../config/db.php';

try {
    // =================================================================================
    // CONSULTAS UNIFICADAS (PRÉSTAMOS + RESERVAS)
    // Usamos CONVERT(... USING utf8) para evitar errores de codificación en MySQL
    // =================================================================================

    // --- PARTE 1: PRÉSTAMOS ---
    $sql_prestamos = "
        SELECT 
            p.id, 
            'PRESTAMO' as tipo_registro,
            CONVERT(p.fecha_prestamo USING utf8) as fecha_inicio, 
            CONVERT(p.fecha_devolucion_pactada USING utf8) as fecha_fin, 
            CONVERT(p.estado USING utf8) as estado,
            CONVERT(IFNULL(p.observaciones, '') USING utf8) as observaciones, 
            CONVERT(CONCAT(per.nombres, ' ', per.apellidos) USING utf8) as solicitante,
            CONVERT(per.tipo USING utf8) as tipo_solicitante,
            CONVERT(IFNULL(per.grado, '') USING utf8) as grado,
            CONVERT(IFNULL(per.seccion, '') USING utf8) as seccion,
            (SELECT COUNT(*) FROM detalle_prestamo dp WHERE dp.id_prestamo = p.id) as total_libros,
            (SELECT GROUP_CONCAT(CONCAT(l.titulo, ' (', dp.cantidad, ')') SEPARATOR ', ') 
             FROM detalle_prestamo dp 
             JOIN libros l ON dp.id_libro = l.id 
             WHERE dp.id_prestamo = p.id) as resumen_libros
        FROM prestamos p
        INNER JOIN personas per ON p.id_persona_solicitante = per.id
    ";

    // --- PARTE 2: RESERVAS (Solo Pendientes) ---
    $sql_reservas = "
        SELECT 
            r.id,
            'RESERVA' as tipo_registro,
            CONVERT(CONCAT(r.fecha_uso, ' ', r.hora_inicio) USING utf8) as fecha_inicio,
            CONVERT(CONCAT(r.fecha_uso, ' ', r.hora_fin) USING utf8) as fecha_fin,
            'RESERVADO' as estado,
            CONVERT(CONCAT('Reserva | Aula: ', IFNULL(r.grado,''), ' ', IFNULL(r.seccion,'')) USING utf8) as observaciones,
            CONVERT(CONCAT(p.nombres, ' ', p.apellidos) USING utf8) as solicitante,
            CONVERT(p.tipo USING utf8) as tipo_solicitante,
            CONVERT(IFNULL(r.grado, '') USING utf8) as grado,
            CONVERT(IFNULL(r.seccion, '') USING utf8) as seccion,
            r.cantidad as total_libros,
            CONVERT(l.titulo USING utf8) as resumen_libros
        FROM reservas r
        JOIN libros l ON r.id_libro = l.id
        JOIN usuarios u ON r.id_usuario_solicitante = u.id
        LEFT JOIN personas p ON u.id_persona = p.id
        WHERE r.estado = 'PENDIENTE'
    ";

    // UNIÓN
    $sql_final = "SELECT * FROM (($sql_prestamos) UNION ALL ($sql_reservas)) AS unidos ORDER BY fecha_inicio DESC";

    $resultado = $conn->query($sql_final);

    if (!$resultado) {
        throw new Exception("Error SQL: " . $conn->error);
    }

    $lista = [];

    while($row = $resultado->fetch_assoc()) {
        // --- Procesamiento de Fechas ---
        $ts_inicio = strtotime($row['fecha_inicio'] ?? 'now');
        $ts_fin = strtotime($row['fecha_fin'] ?? 'now');
        
        $row['fecha_inicio_fmt'] = date("d/m/Y H:i", $ts_inicio);
        
        // CORRECCIÓN AQUÍ: Ahora mostramos la fecha completa también para reservas
        if ($row['tipo_registro'] == 'RESERVA') {
            $row['fecha_fin_fmt'] = date("d/m/Y H:i", $ts_fin); // Antes era solo H:i
        } else {
            $row['fecha_fin_fmt'] = date("d/m/Y", $ts_fin);
        }

        // --- Banderas Lógicas ---
        $obs = $row['observaciones'] ?? '';
        $row['es_reserva'] = ($row['tipo_registro'] == 'RESERVA');
        $row['es_reserva_convertida'] = (strpos($obs, 'Reserva #') !== false);

        // --- Datos Adicionales ---
        $row['hora_limite'] = null;
        $row['aula_info'] = null;

        if ($row['tipo_registro'] == 'PRESTAMO') {
            // Hora límite
            if (strpos($obs, 'Devolución límite') !== false) {
                $partes = explode('|', $obs);
                foreach($partes as $parte) {
                    if(strpos($parte, ' a las: ') !== false) {
                        $sub = explode(' a las: ', $parte);
                        if(isset($sub[1])) $row['hora_limite'] = trim($sub[1]);
                    }
                }
            }
            // Aula
            if (strpos($obs, 'Destino: Aula') !== false) {
                $partes = explode('|', $obs);
                foreach($partes as $parte) {
                    if(strpos($parte, 'Destino: Aula') !== false) {
                        $row['aula_info'] = trim(str_replace(['Destino: Aula', '"'], '', $parte));
                    }
                }
            }
        }

        $lista[] = $row;
    }

    echo json_encode($lista);

} catch (Exception $e) {
    // Devuelve un JSON de error válido
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}
?>