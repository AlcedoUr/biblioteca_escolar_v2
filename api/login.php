<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Recibir JSON del frontend
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user = $conn->real_escape_string($data['username'] ?? '');
$pass = $data['password'] ?? '';

if (empty($user) || empty($pass)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Faltan datos']);
    exit;
}

// Buscar usuario
$stmt = $conn->prepare("SELECT id, password, rol, nombre_completo FROM usuarios WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $user);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
    // 1. Verificación de Contraseña
    // Aceptamos hash (seguro) O texto plano (legacy/pruebas)
    // En producción, SOLO deberías usar password_verify
    $password_valida = password_verify($pass, $row['password']) || $pass === $row['password'];

    if ($password_valida) {
        // Login Exitoso
        session_start();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_rol'] = $row['rol'];
        $_SESSION['user_nombre'] = $row['nombre_completo'];
        
        // Definir a dónde va según su rol
        $destino = 'vistas/dashboard.php'; // Por defecto
        
        if ($row['rol'] == 'DOCENTE' || $row['rol'] == 'ESTUDIANTE') {
            // Los usuarios normales no ven el panel de control, ven el catálogo
            // Como aún no existe catalogo.php, los mandamos a libros.php pero en modo "solo lectura" (futuro)
            $destino = 'vistas/catalogo.php'; 
        }

        echo json_encode([
            'exito' => true, 
            'rol' => $row['rol'],
            'redirect' => $destino
        ]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'Usuario no encontrado']);
}
?>