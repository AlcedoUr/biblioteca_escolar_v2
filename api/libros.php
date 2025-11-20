<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// 1. LISTAR LIBROS (GET)
if ($metodo == 'GET') {
    $sql = "SELECT * FROM libros ORDER BY id DESC";
    $resultado = $conn->query($sql);
    
    $libros = [];
    while($row = $resultado->fetch_assoc()) {
        $libros[] = $row;
    }
    echo json_encode($libros);
    exit;
}

// 2. CREAR LIBRO (POST)
if ($metodo == 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validar datos mínimos
    if (empty($data['titulo']) || empty($data['stock'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'Falta título o stock']);
        exit;
    }

    $titulo = $data['titulo'];
    $autor = $data['autor'];
    $ubicacion = $data['ubicacion'];
    $stock = (int)$data['stock'];

    // Preparar inserción (Segura)
    $stmt = $conn->prepare("INSERT INTO libros (titulo, autor, ubicacion, stock_total, stock_disponible) VALUES (?, ?, ?, ?, ?)");
    // "sssii" significa: String, String, String, Int, Int
    $stmt->bind_param("sssii", $titulo, $autor, $ubicacion, $stock, $stock);

    if ($stmt->execute()) {
        echo json_encode(['exito' => true, 'mensaje' => 'Libro registrado']);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error al guardar']);
    }
    exit;
}
?>