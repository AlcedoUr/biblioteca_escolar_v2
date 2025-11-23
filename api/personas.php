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
    // --- MODO IMPORTACIÓN CSV ---
    if (isset($_FILES['archivo_csv'])) {
        $archivo = $_FILES['archivo_csv']['tmp_name'];
        if ($_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['exito' => false, 'mensaje' => 'Error al subir el archivo. Código: ' . $_FILES['archivo_csv']['error']]);
            exit;
        }

        $handle = fopen($archivo, "r");
        if ($handle === FALSE) {
            echo json_encode(['exito' => false, 'mensaje' => 'No se pudo abrir el archivo CSV.']);
            exit;
        }

        $importados = 0;
        $errores = 0;
        
        // Omitir la fila de cabecera
        fgetcsv($handle, 1000, ",");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Columnas esperadas: DNI, Nombres, Apellidos, Tipo, [Grado/Especialidad], [Seccion/Teléfono]
            if (count($data) < 4) {
                $errores++;
                continue; // Saltar fila si no tiene las columnas mínimas
            }

            $dni = trim($conn->real_escape_string($data[0]));
            $nombres = trim($conn->real_escape_string($data[1]));
            $apellidos = trim($conn->real_escape_string($data[2]));
            $tipo = strtoupper(trim($conn->real_escape_string($data[3]))); // ESTUDIANTE o DOCENTE

            if (empty($dni) || empty($nombres) || empty($apellidos) || !in_array($tipo, ['ESTUDIANTE', 'DOCENTE'])) {
                $errores++;
                continue;
            }

            // Verificar si el DNI ya existe para no duplicar
            $check = $conn->query("SELECT id FROM personas WHERE dni = '$dni'");
            if ($check->num_rows > 0) {
                $errores++;
                continue; 
            }
            
            $grado = "NULL";
            $seccion = "NULL";
            $especialidad = "NULL";
            $telefono = "NULL";

            if ($tipo == 'ESTUDIANTE') {
                $grado = isset($data[4]) && !empty(trim($data[4])) ? "'" . $conn->real_escape_string(trim($data[4])) . "'" : "NULL";
                $seccion = isset($data[5]) && !empty(trim($data[5])) ? "'" . $conn->real_escape_string(trim($data[5])) . "'" : "NULL";
            } else if ($tipo == 'DOCENTE') {
                $especialidad = isset($data[4]) && !empty(trim($data[4])) ? "'" . $conn->real_escape_string(trim($data[4])) . "'" : "NULL";
                $telefono = isset($data[5]) && !empty(trim($data[5])) ? "'" . $conn->real_escape_string(trim($data[5])) . "'" : "NULL";
            }

            $sql = "INSERT INTO personas (dni, nombres, apellidos, tipo, grado, seccion, especialidad, telefono, estado_biblioteca) 
                    VALUES ('$dni', '$nombres', '$apellidos', '$tipo', $grado, $seccion, $especialidad, $telefono, 'ACTIVO')";

            if ($conn->query($sql)) {
                $importados++;
            } else {
                $errores++;
            }
        }
        fclose($handle);

        echo json_encode(['exito' => true, 'mensaje' => "Proceso finalizado: $importados usuarios importados, $errores filas omitidas (ya existentes o con datos incorrectos)."]);
        exit;
    }

    // --- MODO CREACIÓN/EDICIÓN INDIVIDUAL (JSON) ---
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!is_array($data)) {
        echo json_encode(['exito' => false, 'mensaje' => 'Error: Los datos de entrada no son válidos.']);
        exit;
    }

    // Validaciones mínimas
    if (empty($data['nombres']) || empty($data['apellidos']) || empty($data['dni'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'Nombre, Apellido y DNI son obligatorios']);
        exit;
    }

    $id = $data['id'] ?? null;
    $nombres = $conn->real_escape_string($data['nombres']);
    $apellidos = $conn->real_escape_string($data['apellidos']);
    $dni = $conn->real_escape_string($data['dni']);
    $tipo = $data['tipo'];
    
    $grado = !empty($data['grado']) ? "'" . $conn->real_escape_string($data['grado']) . "'" : "NULL";
    $seccion = !empty($data['seccion']) ? "'" . $conn->real_escape_string($data['seccion']) . "'" : "NULL";
    $especialidad = !empty($data['especialidad']) ? "'" . $conn->real_escape_string($data['especialidad']) . "'" : "NULL";
    $telefono = !empty($data['telefono']) ? "'" . $conn->real_escape_string($data['telefono']) . "'" : "NULL";
    $estado = isset($data['estado_biblioteca']) ? "'" . $conn->real_escape_string($data['estado_biblioteca']) . "'" : "'ACTIVO'";

    if ($id) {
        // --- MODO EDICIÓN ---
        $sql = "UPDATE personas SET nombres='$nombres', apellidos='$apellidos', dni='$dni', tipo='$tipo', grado=$grado, seccion=$seccion, especialidad=$especialidad, telefono=$telefono, estado_biblioteca=$estado WHERE id=$id";
    } else {
        // --- MODO CREACIÓN ---
        $check = $conn->query("SELECT id FROM personas WHERE dni = '$dni'");
        if ($check->num_rows > 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'El DNI ya está registrado']);
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