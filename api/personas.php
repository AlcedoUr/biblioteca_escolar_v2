<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// 1. LISTAR (GET)
if ($metodo == 'GET') {
    $sql = "SELECT * FROM personas ORDER BY apellidos ASC";
    $resultado = $conn->query($sql);
    
    $personas = [];
    while($row = $resultado->fetch_assoc()) {
        $personas[] = $row;
    }
    echo json_encode($personas);
    exit;
}

// 2. CREAR O EDITAR (POST)
if ($metodo == 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validaciones mínimas
    if (empty($data['nombres']) || empty($data['apellidos']) || empty($data['dni'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'Nombre, Apellido y DNI son obligatorios']);
        exit;
    }

    $id = $data['id'] ?? null;
    $nombres = $conn->real_escape_string($data['nombres']);
    $apellidos = $conn->real_escape_string($data['apellidos']);
    $dni = $conn->real_escape_string($data['dni']);
    $tipo = $data['tipo']; // ESTUDIANTE o DOCENTE
    $grado = $data['grado'] ?? '';
    $seccion = $data['seccion'] ?? '';
    $estado = $data['estado_biblioteca'] ?? 'ACTIVO';

    if ($id) {
        // MODO EDICIÓN
        $sql = "UPDATE personas SET nombres='$nombres', apellidos='$apellidos', dni='$dni', tipo='$tipo', grado='$grado', seccion='$seccion', estado_biblioteca='$estado' WHERE id=$id";
    } else {
        // MODO CREACIÓN
        // Verificar DNI duplicado
        $check = $conn->query("SELECT id FROM personas WHERE dni = '$dni'");
        if ($check->num_rows > 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'El DNI ya existe en el sistema']);
            exit;
        }
        $sql = "INSERT INTO personas (nombres, apellidos, dni, tipo, grado, seccion, estado_biblioteca) VALUES ('$nombres', '$apellidos', '$dni', '$tipo', '$grado', '$seccion', 'ACTIVO')";
    }

    if ($conn->query($sql)) {
        echo json_encode(['exito' => true, 'mensaje' => 'Datos guardados correctamente']);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error BD: ' . $conn->error]);
    }
    exit;
}
?>