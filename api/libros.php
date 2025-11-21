<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 0. LISTAR CATEGORÍAS (Para el filtro)
// ==========================================
if ($metodo == 'GET' && isset($_GET['get_categorias'])) {
    $sql = "SELECT DISTINCT categoria FROM libros WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC";
    $res = $conn->query($sql);
    $cats = [];
    while($row = $res->fetch_assoc()) {
        $cats[] = $row['categoria'];
    }
    echo json_encode($cats);
    exit;
}

// ==========================================
// 1. LISTAR LIBROS (GET)
// ==========================================
if ($metodo == 'GET') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    $busqueda = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
    $categoria_filtro = isset($_GET['categoria']) ? $conn->real_escape_string($_GET['categoria']) : '';
    
    $where = "WHERE 1=1";
    
    // Filtro de texto
    if ($busqueda) {
        $where .= " AND (titulo LIKE '%$busqueda%' OR autor LIKE '%$busqueda%' OR editorial LIKE '%$busqueda%')";
    }
    
    // Filtro de categoría
    if ($categoria_filtro && $categoria_filtro != 'TODO') {
        $where .= " AND categoria = '$categoria_filtro'";
    }

    // Paginación
    $sql_count = "SELECT COUNT(*) as total FROM libros $where";
    $total_items = $conn->query($sql_count)->fetch_assoc()['total'];
    $total_pages = ceil($total_items / $limit);

    // Consulta correcta a la tabla LIBROS
    $sql = "SELECT * FROM libros $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $resultado = $conn->query($sql);
    
    $libros = [];
    while($row = $resultado->fetch_assoc()) {
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

// ==========================================
// 2. GUARDAR (POST)
// ==========================================
if ($metodo == 'POST') {
    
    // [A] IMPORTACIÓN CSV
    if (isset($_FILES['archivo_csv'])) {
        $file = $_FILES['archivo_csv']['tmp_name'];
        $handle = fopen($file, "r");
        fgetcsv($handle); 
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $titulo = $conn->real_escape_string($data[0] ?? '');
            $autor = $conn->real_escape_string($data[1] ?? '');
            $editorial = $conn->real_escape_string($data[2] ?? '');
            $categoria = $conn->real_escape_string($data[3] ?? 'General');
            $stock = (int)($data[4] ?? 0);
            
            if($titulo && $stock > 0) {
                $sql = "INSERT INTO libros (titulo, autor, editorial, categoria, stock_total, stock_disponible) 
                        VALUES ('$titulo', '$autor', '$editorial', '$categoria', $stock, $stock)";
                $conn->query($sql);
                $count++;
            }
        }
        fclose($handle);
        echo json_encode(['exito' => true, 'mensaje' => "Se importaron $count libros"]);
        exit;
    }

    // [B] GUARDADO INDIVIDUAL
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['titulo']) || empty($data['stock'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos']);
        exit;
    }

    $id = $data['id'] ?? null;
    $titulo = $conn->real_escape_string($data['titulo']);
    $autor = $conn->real_escape_string($data['autor']);
    $editorial = $conn->real_escape_string($data['editorial'] ?? '');
    $categoria = $conn->real_escape_string($data['categoria']);
    $nuevo_stock_total = (int)$data['stock'];

    if ($id) {
        // Edición inteligente de stock
        $qry = $conn->query("SELECT stock_total, stock_disponible FROM libros WHERE id=$id");
        $libro_actual = $qry->fetch_assoc();
        
        $diferencia = $nuevo_stock_total - (int)$libro_actual['stock_total'];
        $nuevo_disponible = (int)$libro_actual['stock_disponible'] + $diferencia;

        if ($nuevo_disponible < 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'Error: Stock menor a prestados.']);
            exit;
        }

        $sql = "UPDATE libros SET 
                titulo='$titulo', autor='$autor', editorial='$editorial',
                categoria='$categoria', stock_total=$nuevo_stock_total,
                stock_disponible=$nuevo_disponible WHERE id=$id";
    } else {
        // Creación
        $sql = "INSERT INTO libros (titulo, autor, editorial, categoria, stock_total, stock_disponible) 
                VALUES ('$titulo', '$autor', '$editorial', '$categoria', $nuevo_stock_total, $nuevo_stock_total)";
    }

    if ($conn->query($sql)) {
        echo json_encode(['exito' => true]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => $conn->error]);
    }
    exit;
}

// ==========================================
// 3. ELIMINAR (DELETE)
// ==========================================
if ($metodo == 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $check = $conn->query("SELECT count(*) FROM detalle_prestamo WHERE id_libro=$id AND estado_devolucion='PENDIENTE'");
    
    if ($check && $check->fetch_row()[0] > 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'Tiene préstamos activos.']);
        exit;
    }

    if ($conn->query("DELETE FROM libros WHERE id=$id")) {
        echo json_encode(['exito' => true]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error al eliminar']);
    }
    exit;
}
?>