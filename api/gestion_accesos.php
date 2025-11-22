<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// --- 1. LISTAR DOCENTES Y SU ESTADO DE CUENTA ---
if ($metodo == 'GET') {
    // Obtenemos todos los docentes y vemos si tienen usuario creado (LEFT JOIN)
    $sql = "
        SELECT 
            p.id as id_persona, 
            p.nombres, 
            p.apellidos, 
            p.dni, 
            p.estado_biblioteca,
            u.id as id_usuario,
            u.username
        FROM personas p
        LEFT JOIN usuarios u ON p.id = u.id_persona
        WHERE p.tipo = 'DOCENTE'
        ORDER BY p.apellidos ASC
    ";
    
    $res = $conn->query($sql);
    $data = [];
    while($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// --- 2. ACCIONES MASIVAS (POST) ---
if ($metodo == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $accion = $input['accion'] ?? ''; // 'CREAR', 'ELIMINAR', 'BLOQUEAR', 'DESBLOQUEAR'
    $ids = $input['ids'] ?? []; // Array de IDs de Personas

    if (empty($ids) || empty($accion)) {
        echo json_encode(['exito' => false, 'mensaje' => 'No se seleccionaron usuarios.']);
        exit;
    }

    $conn->begin_transaction();
    try {
        $cont = 0;

        foreach ($ids as $id_persona) {
            // Obtener datos frescos de la persona
            $q = $conn->query("SELECT * FROM personas WHERE id = $id_persona");
            $p = $q->fetch_assoc();
            
            if (!$p) continue;

            // === ACCIÓN: CREAR USUARIO ===
            if ($accion == 'CREAR') {
                // 1. Formatear Username: 1ra Letra Apellido + 1er Nombre
                $primer_apellido = explode(' ', trim($p['apellidos']))[0];
                $primer_nombre = explode(' ', trim($p['nombres']))[0];
                
                // Limpieza básica de caracteres y minúsculas
                $letra_apellido = substr($primer_apellido, 0, 1);
                $base_user = strtolower($letra_apellido . $primer_nombre);
                // Eliminar tildes/ñ si es necesario (básico)
                $base_user = preg_replace('/[^a-z0-9]/', '', $base_user);

                $username = $base_user;
                $password = $p['dni']; // Contraseña es DNI

                // Verificar si ya tiene usuario
                $check = $conn->query("SELECT id FROM usuarios WHERE id_persona = $id_persona");
                if ($check->num_rows == 0) {
                    
                    // Verificar si el username ya existe (para otro usuario), si existe le agregamos números
                    $dup = $conn->query("SELECT id FROM usuarios WHERE username = '$username'");
                    if ($dup->num_rows > 0) {
                        $username .= substr($p['dni'], -3); // Agregamos 3 últimos dígitos del DNI si hay duplicado
                    }

                    $stmt = $conn->prepare("INSERT INTO usuarios (id_persona, username, password, rol, nombre_completo) VALUES (?, ?, ?, 'DOCENTE', ?)");
                    $nombre_completo = $p['nombres'] . ' ' . $p['apellidos'];
                    $stmt->bind_param("isss", $id_persona, $username, $password, $nombre_completo);
                    $stmt->execute();
                    $cont++;
                }
            }

            // === ACCIÓN: ELIMINAR ACCESO ===
            if ($accion == 'ELIMINAR') {
                $conn->query("DELETE FROM usuarios WHERE id_persona = $id_persona");
                $cont++;
            }

            // === ACCIÓN: BLOQUEAR / DESBLOQUEAR ===
            if ($accion == 'BLOQUEAR' || $accion == 'DESBLOQUEAR') {
                $estado = ($accion == 'BLOQUEAR') ? 'BLOQUEADO' : 'ACTIVO';
                $conn->query("UPDATE personas SET estado_biblioteca = '$estado' WHERE id = $id_persona");
                $cont++;
            }
        }

        $conn->commit();
        echo json_encode(['exito' => true, 'mensaje' => "Acción '$accion' realizada en $cont registros."]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
    }
}
?>