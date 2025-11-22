<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data['id_persona']) || empty($data['dni']) || empty($data['tipo'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos']);
    exit;
}

$id_persona = $data['id_persona'];
$dni = $conn->real_escape_string($data['dni']);
$rol = $data['tipo']; // 'DOCENTE' o 'ESTUDIANTE'
$nombre = $conn->real_escape_string($data['nombres'] . ' ' . $data['apellidos']);

// 1. Verificar si ya tiene usuario
$check = $conn->query("SELECT id FROM usuarios WHERE id_persona = $id_persona");
if ($check->num_rows > 0) {
    echo json_encode(['exito' => false, 'mensaje' => 'Esta persona ya tiene acceso al sistema.']);
    exit;
}

// 2. Verificar si el username (DNI) ya está en uso por otro
$checkUser = $conn->query("SELECT id FROM usuarios WHERE username = '$dni'");
if ($checkUser->num_rows > 0) {
    echo json_encode(['exito' => false, 'mensaje' => 'El nombre de usuario (DNI) ya está en uso.']);
    exit;
}

// 3. Crear Usuario
// NOTA: Por defecto la contraseña será el mismo DNI
// En un entorno real, usar password_hash($dni, PASSWORD_DEFAULT)
$password = $dni; 

$sql = "INSERT INTO usuarios (id_persona, username, password, rol, nombre_completo) 
        VALUES ($id_persona, '$dni', '$password', '$rol', '$nombre')";

if ($conn->query($sql)) {
    echo json_encode(['exito' => true, 'mensaje' => 'Acceso creado. Usuario y Clave son el DNI.']);
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'Error SQL: ' . $conn->error]);
}
?>