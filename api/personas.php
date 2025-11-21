<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// ==========================================
// 1. LISTAR (GET)
// ==========================================
if ($metodo == 'GET') {
    // Filtro opcional por tipo (si quisieras cargar solo estudiantes, por ejemplo)
    $tipo = isset($_GET['tipo']) ? $conn->real_escape_string($_GET['tipo']) : '';
    
    $where = "WHERE 1=1";
    if ($tipo) {
        $where .= " AND tipo = '$tipo'";
    }

    // Ordenamos por apellidos para que la lista sea fácil de leer
    $sql = "SELECT * FROM personas $where ORDER BY apellidos ASC";
    $resultado = $conn->query($sql);
    
    $personas = [];
    while($row = $resultado->fetch_assoc()) {
        $personas[] = $row;
    }
    echo json_encode($personas);
    exit;
}

// ==========================================
// 2. CREAR O EDITAR (POST)
// ==========================================
if ($metodo == 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validaciones mínimas: Nombre, Apellido y DNI son obligatorios para todos
    if (empty($data['nombres']) || empty($data['apellidos']) || empty($data['dni'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'Nombre, Apellido y DNI son obligatorios']);
        exit;
    }

    $id = $data['id'] ?? null;
    $nombres = $conn->real_escape_string($data['nombres']);
    $apellidos = $conn->real_escape_string($data['apellidos']);
    $dni = $conn->real_escape_string($data['dni']);
    $tipo = $data['tipo']; // ESTUDIANTE o DOCENTE
    
    // Campos dinámicos (se guardan como NULL si no aplican)
    $grado = !empty($data['grado']) ? "'" . $conn->real_escape_string($data['grado']) . "'" : "NULL";
    $seccion = !empty($data['seccion']) ? "'" . $conn->real_escape_string($data['seccion']) . "'" : "NULL";
    $especialidad = !empty($data['especialidad']) ? "'" . $conn->real_escape_string($data['especialidad']) . "'" : "NULL";
    $telefono = !empty($data['telefono']) ? "'" . $conn->real_escape_string($data['telefono']) . "'" : "NULL";
    $estado = isset($data['estado_biblioteca']) ? "'" . $conn->real_escape_string($data['estado_biblioteca']) . "'" : "'ACTIVO'";

    if ($id) {
        // --- MODO EDICIÓN ---
        $sql = "UPDATE personas SET 
                nombres='$nombres', 
                apellidos='$apellidos', 
                dni='$dni', 
                tipo='$tipo', 
                grado=$grado, 
                seccion=$seccion, 
                especialidad=$especialidad, 
                telefono=$telefono,
                estado_biblioteca=$estado 
                WHERE id=$id";
    } else {
        // --- MODO CREACIÓN ---
        // 1. Verificar que el DNI no exista ya para evitar duplicados
        $check = $conn->query("SELECT id FROM personas WHERE dni = '$dni'");
        if ($check->num_rows > 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'El DNI ya está registrado en el sistema']);
            exit;
        }
        
        $sql = "INSERT INTO personas (nombres, apellidos, dni, tipo, grado, seccion, especialidad, telefono, estado_biblioteca) 
                VALUES ('$nombres', '$apellidos', '$dni', '$tipo', $grado, $seccion, $especialidad, $telefono, $estado)";
    }

    if ($conn->query($sql)) {
        echo json_encode(['exito' => true, 'mensaje' => 'Datos guardados correctamente']);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error de Base de Datos: ' . $conn->error]);
    }
    exit;
}

// ==========================================
// 3. ELIMINAR (DELETE)
// ==========================================
if ($metodo == 'DELETE') {
    $id = $_GET['id'] ?? 0;
    
    // Seguridad: No borrar si tiene historial de préstamos para no romper reportes pasados
    $check = $conn->query("SELECT count(*) FROM prestamos WHERE id_persona_solicitante=$id");
    if ($check->fetch_row()[0] > 0) {
        echo json_encode(['exito' => false, 'mensaje' => 'No se puede eliminar: El usuario tiene historial de préstamos. Se recomienda cambiar su estado a BLOQUEADO.']);
        exit;
    }

    if ($conn->query("DELETE FROM personas WHERE id=$id")) {
        echo json_encode(['exito' => true]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error al eliminar']);
    }
    exit;
}
?>