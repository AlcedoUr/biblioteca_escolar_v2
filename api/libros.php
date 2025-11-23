<?php
header('Content-Type: application/json');
// Desactivar visualización de errores HTML para no romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// =============================================================================
// 0. RUTINA AUTOMÁTICA: LIMPIEZA DE RESERVAS VENCIDAS
// =============================================================================
// Si la hora actual es mayor a la hora de inicio + 15 min de tolerancia, se vence.
if ($metodo == 'GET') {
    $sql_expire = "
        UPDATE reservas 
        SET estado = 'VENCIDA' 
        WHERE estado = 'PENDIENTE' 
          AND fecha_uso <= CURDATE() 
          AND ADDTIME(hora_inicio, '00:15:00') < CURTIME()
    ";
    $conn->query($sql_expire);
}

// =============================================================================
// 1. OBTENER CATEGORÍAS
// =============================================================================
if ($metodo == 'GET' && isset($_GET['get_categorias'])) {
    $sql = "SELECT DISTINCT categoria FROM libros WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC";
    $res = $conn->query($sql);
    $cats = [];
    while($row = $res->fetch_assoc()) $cats[] = $row['categoria'];
    echo json_encode($cats);
    exit;
}

// =============================================================================
// 2. LISTAR LIBROS (CON FILTRO DE HORARIO INTELIGENTE)
// =============================================================================
if ($metodo == 'GET') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000; // Default alto para catálogo dinámico
    $offset = ($page - 1) * $limit;
    
    $busqueda = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
    $categoria_filtro = isset($_GET['categoria']) ? $conn->real_escape_string($_GET['categoria']) : '';
    
    // --- NUEVOS PARÁMETROS DE FILTRO TEMPORAL ---
    // Si no se envían, asume el día de hoy completo
    $fecha_uso = isset($_GET['fecha']) && !empty($_GET['fecha']) ? $conn->real_escape_string($_GET['fecha']) : date('Y-m-d');
    $hora_ini = isset($_GET['hora_ini']) ? $conn->real_escape_string($_GET['hora_ini']) : '00:00:00';
    $hora_fin = isset($_GET['hora_fin']) ? $conn->real_escape_string($_GET['hora_fin']) : '23:59:59';

    // Ordenamiento
    $sort = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'titulo';
    $order = isset($_GET['order']) ? $conn->real_escape_string($_GET['order']) : 'ASC';
    
    $columnas_permitidas = ['id', 'titulo', 'autor', 'stock_total', 'isbn'];
    if (!in_array($sort, $columnas_permitidas)) $sort = 'titulo';
    if ($order !== 'ASC' && $order !== 'DESC') $order = 'ASC';

    $where = "WHERE 1=1";
    if ($busqueda) {
        $where .= " AND (titulo LIKE '%$busqueda%' OR autor LIKE '%$busqueda%' OR isbn LIKE '%$busqueda%' OR editorial LIKE '%$busqueda%')";
    }
    if ($categoria_filtro && $categoria_filtro != 'TODO') {
        $where .= " AND categoria = '$categoria_filtro'";
    }

    // Contar total
    $sql_count = "SELECT COUNT(*) as total FROM libros $where";
    $total_items = $conn->query($sql_count)->fetch_assoc()['total'];
    $total_pages = ceil($total_items / $limit);

    // --- CONSULTA PRINCIPAL CON LÓGICA DE CRUCE DE HORARIOS ---
    $sql = "
        SELECT 
            l.*,
            (
                SELECT COALESCE(SUM(cantidad), 0) 
                FROM reservas 
                WHERE id_libro = l.id 
                  AND estado = 'PENDIENTE' 
                  AND fecha_uso = '$fecha_uso'
                  AND (
                      (hora_inicio < '$hora_fin' AND hora_fin > '$hora_ini') 
                  )
            ) as reservados_en_horario
        FROM libros l
        $where 
        ORDER BY $sort $order 
        LIMIT $limit OFFSET $offset
    ";

    $resultado = $conn->query($sql);
    $libros = [];
    
    while($row = $resultado->fetch_assoc()) {
        // CÁLCULO DE DISPONIBILIDAD REAL
        $stock_fisico = (int)$row['stock_disponible']; // Lo que hay en estantería hoy
        $reservados_bloque = (int)$row['reservados_en_horario']; // Lo comprometido para ese rato
        
        // El disponible real es: Físico - Reservados en ese bloque
        $disponible_real = max(0, $stock_fisico - $reservados_bloque);
        
        // Inyectamos los datos calculados
        $row['stock_disponible_real'] = $disponible_real; 
        $row['stock_disponible_fisico'] = $stock_fisico;  
        $row['reservados_info'] = $reservados_bloque; // Para mostrar alerta visual si hay reservas
        
        // Sobreescribimos 'stock_disponible' para compatibilidad con vistas antiguas
        $row['stock_disponible'] = $disponible_real; 

        $libros[] = $row;
    }

    echo json_encode([
        'data' => $libros,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_items
        ]
    ]);
    exit;
}

// =============================================================================
// 3. GUARDAR (POST) - (MANTENIDO INTEGRO)
// =============================================================================
if ($metodo == 'POST') {
    try {
        // A. Importación CSV
        if (isset($_FILES['archivo_csv'])) {
            $file = $_FILES['archivo_csv']['tmp_name'];
            $handle = fopen($file, "r");
            fgetcsv($handle); 
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $isbn = $conn->real_escape_string($data[0] ?? '');
                $titulo = $conn->real_escape_string($data[1] ?? '');
                $autor = $conn->real_escape_string($data[2] ?? '');
                $editorial = $conn->real_escape_string($data[3] ?? '');
                $categoria = $conn->real_escape_string($data[4] ?? 'General');
                $stock = (int)($data[5] ?? 0);
                $url = $conn->real_escape_string($data[6] ?? '');

                if($titulo && $stock > 0) {
                    $sql = "INSERT INTO libros (isbn, titulo, autor, editorial, categoria, stock_total, stock_disponible, url_digital) 
                            VALUES ('$isbn', '$titulo', '$autor', '$editorial', '$categoria', $stock, $stock, '$url')";
                    $conn->query($sql);
                    $count++;
                }
            }
            fclose($handle);
            echo json_encode(['exito' => true, 'mensaje' => "Se importaron $count libros"]);
            exit;
        }

        // B. Guardado Manual
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (empty($data['titulo']) || empty($data['stock'])) {
            throw new Exception('Faltan datos obligatorios (Título o Stock).');
        }

        $id = $data['id'] ?? null;
        $isbn = $conn->real_escape_string($data['isbn'] ?? '');
        $titulo = $conn->real_escape_string($data['titulo']);
        $autor = $conn->real_escape_string($data['autor']);
        $editorial = $conn->real_escape_string($data['editorial'] ?? '');
        $categoria = $conn->real_escape_string($data['categoria']);
        $url_digital = $conn->real_escape_string($data['url_digital'] ?? '');
        $nuevo_stock_total = (int)$data['stock'];

        if ($id) {
            // Modo EDICIÓN
            $qry = $conn->query("SELECT stock_total, stock_disponible FROM libros WHERE id=$id");
            $libro_actual = $qry->fetch_assoc();
            
            $diferencia = $nuevo_stock_total - (int)$libro_actual['stock_total'];
            $nuevo_disponible = (int)$libro_actual['stock_disponible'] + $diferencia;

            if ($nuevo_disponible < 0) {
                throw new Exception('Stock total no puede ser menor a la cantidad prestada actual.');
            }

            $sql = "UPDATE libros SET isbn='$isbn', titulo='$titulo', autor='$autor', editorial='$editorial', categoria='$categoria', stock_total=$nuevo_stock_total, stock_disponible=$nuevo_disponible, url_digital='$url_digital' WHERE id=$id";
        } else {
            // Modo CREACIÓN
            $sql = "INSERT INTO libros (isbn, titulo, autor, editorial, categoria, stock_total, stock_disponible, url_digital) 
                    VALUES ('$isbn', '$titulo', '$autor', '$editorial', '$categoria', $nuevo_stock_total, $nuevo_stock_total, '$url_digital')";
        }

        // Ejecución controlada
        if (!$conn->query($sql)) {
            throw new Exception("Error SQL: " . $conn->error);
        }

        echo json_encode(['exito' => true]);

    } catch (Exception $e) {
        // Capturamos cualquier error y lo devolvemos como JSON válido
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}

// =============================================================================
// 4. ELIMINAR (DELETE) - (MANTENIDO INTEGRO)
// =============================================================================
if ($metodo == 'DELETE') {
    try {
        $id = $_GET['id'] ?? 0;
        $check = $conn->query("SELECT count(*) FROM detalle_prestamo WHERE id_libro=$id AND estado_devolucion='PENDIENTE'");
        
        if ($check && $check->fetch_row()[0] > 0) {
            throw new Exception('No se puede eliminar: Tiene préstamos activos.');
        }
        if (!$conn->query("DELETE FROM libros WHERE id=$id")) {
            throw new Exception("Error al eliminar: " . $conn->error);
        }
        echo json_encode(['exito' => true]);
    } catch (Exception $e) {
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
    }
    exit;
}
?>