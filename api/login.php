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

// CONSULTA ACTUALIZADA: Ahora hacemos JOIN con personas para verificar el estado
$sql = "SELECT u.id, u.password, u.rol, u.nombre_completo, p.estado_biblioteca 
        FROM usuarios u 
        LEFT JOIN personas p ON u.id_persona = p.id 
        WHERE u.username = ? LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
    
    // 1. VERIFICAR BLOQUEO
    if ($row['estado_biblioteca'] === 'BLOQUEADO') {
        echo json_encode(['exito' => false, 'mensaje' => 'Acceso denegado: Usuario bloqueado por administración.']);
        exit;
    }

    // 2. VERIFICAR PASSWORD
    $password_valida = password_verify($pass, $row['password']) || $pass === $row['password'];

    if ($password_valida) {
        session_start();
        
        // --- MEDIDA DE SEGURIDAD ANTI-MEZCLA DE ROLES ---
        // 1. Destruimos la sesión vieja si existía alguna basura en el navegador
        session_regenerate_id(true); 
        
        // 2. Establecemos los datos nuevos
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_rol'] = $row['rol'];
        $_SESSION['user_nombre'] = $row['nombre_completo'];
        
        // ... resto del código de redirección igual ...
        $destino = 'vistas/dashboard.php';
        if ($row['rol'] == 'ESTUDIANTE') {
            $destino = 'vistas/biblioteca_virtual.php'; 
        } 
        else if ($row['rol'] == 'DOCENTE') {
            $destino = 'vistas/catalogo.php'; 
        }

        echo json_encode(['exito' => true, 'redirect' => $destino]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'Usuario no encontrado']);
}
?>