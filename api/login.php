<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user = $conn->real_escape_string($data['username'] ?? '');
$pass = $data['password'] ?? '';

if (empty($user) || empty($pass)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos']);
    exit;
}

$stmt = $conn->prepare("SELECT id, password, rol, nombre_completo FROM usuarios WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $user);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
    $password_valida = password_verify($pass, $row['password']) || $pass === $row['password'];

    if ($password_valida) {
        session_start();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_rol'] = $row['rol'];
        $_SESSION['user_nombre'] = $row['nombre_completo'];
        
        // --- REDIRECCIÓN SEGÚN ROL ---
        $destino = 'vistas/dashboard.php'; // Default Admin
        
        if ($row['rol'] == 'ESTUDIANTE') {
            // Estudiante va directo a su única vista
            $destino = 'vistas/biblioteca_virtual.php'; 
        } 
        else if ($row['rol'] == 'DOCENTE') {
            // Docente va al catálogo o reservas
            $destino = 'vistas/catalogo.php'; 
        }

        echo json_encode([
            'exito' => true, 
            'redirect' => $destino
        ]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'Usuario no encontrado']);
}
?>