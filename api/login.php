<?php
// api/login.php
header('Content-Type: application/json');
require_once '../config/db.php';

// Recibir JSON del frontend
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user = $data['username'] ?? '';
$pass = $data['password'] ?? '';

if (empty($user) || empty($pass)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos']);
    exit;
}

// Buscar usuario en BD (Usamos Prepared Statements por seguridad)
$stmt = $conn->prepare("SELECT id, password, rol, nombre_completo FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
    // Verificar contraseña
    // NOTA: En producción usaríamos password_verify(). 
    // Como en el seed pusimos texto plano '123456', comparamos directo por ahora.
    if ($pass === $row['password']) {
        
        // ¡LOGIN EXITOSO! Iniciamos sesión
        session_start();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_rol'] = $row['rol'];
        $_SESSION['user_nombre'] = $row['nombre_completo'];

        echo json_encode(['exito' => true]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'Usuario no encontrado']);
}
?>