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
// 2. CREAR, EDITAR O IMPORTAR (POST)
// ==========================================
if ($metodo == 'POST') {
    
    // --- A. IMPORTACIÓN MASIVA (CSV) ---
    if (isset($_FILES['archivo_csv'])) {
        $file = $_FILES['archivo_csv']['tmp_name'];
        $handle = fopen($file, "r");
        fgetcsv($handle); // Saltar cabecera
        $count = 0;
        $errores = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // CSV Estudiantes: DNI, Nombres, Apellidos, Grado, Sección
            // CSV Docentes: DNI, Nombres, Apellidos, Especialidad, Teléfono
            // Para simplificar, asumimos un formato estándar:
            // DNI, Nombres, Apellidos, Tipo(ESTUDIANTE/DOCENTE), Grado/Esp, Seccion/Tel
            
            $dni = $conn->real_escape_string($data[0] ?? '');
            $nombres = $conn->real_escape_string($data[1] ?? '');
            $apellidos = $conn->real_escape_string($data[2] ?? '');
            $tipo = strtoupper($conn->real_escape_string($data[3] ?? 'ESTUDIANTE'));
            $campo4 = $conn->real_escape_string($data[4] ?? ''); // Grado o Especialidad
            $campo5 = $conn->real_escape_string($data[5] ?? ''); // Sección o Teléfono

            if($dni && $nombres) {
                // Verificar si ya existe
                $check = $conn->query("SELECT id FROM personas WHERE dni = '$dni'");
                if ($check->num_rows == 0) {
                    $grado = ($tipo == 'ESTUDIANTE') ? $campo4 : NULL;
                    $seccion = ($tipo == 'ESTUDIANTE') ? $campo5 : NULL;
                    $especialidad = ($tipo == 'DOCENTE') ? $campo4 : NULL;
                    $telefono = ($tipo == 'DOCENTE') ? $campo5 : NULL;

                    $sql = "INSERT INTO personas (dni, nombres, apellidos, tipo, grado, seccion, especialidad, telefono, estado_biblioteca) 
                            VALUES ('$dni', '$nombres', '$apellidos', '$tipo', '$grado', '$seccion', '$especialidad', '$telefono', 'ACTIVO')";
                    $conn->query($sql);
                    $count++;
                } else {
                    $errores++; // Duplicado ignorado
                }
            }
        }
        fclose($handle);
        echo json_encode(['exito' => true, 'mensaje' => "Importados: $count. Duplicados ignorados: $errores"]);
        exit;
    }

    // --- B. GUARDADO INDIVIDUAL ---
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (empty($data['nombres']) || empty($data['dni'])) {
        echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
        exit;
    }

    $id = $data['id'] ?? null;
    $nombres = $conn->real_escape_string($data['nombres']);
    $apellidos = $conn->real_escape_string($data['apellidos']);
    $dni = $conn->real_escape_string($data['dni']);
    $tipo = $data['tipo']; 
    $grado = $data['grado'] ?? '';
    $seccion = $data['seccion'] ?? '';
    $especialidad = $conn->real_escape_string($data['especialidad'] ?? '');
    $telefono = $conn->real_escape_string($data['telefono'] ?? '');
    $estado = $data['estado_biblioteca'] ?? 'ACTIVO';

    if ($id) {
        $sql = "UPDATE personas SET 
                nombres='$nombres', apellidos='$apellidos', dni='$dni', tipo='$tipo', 
                grado='$grado', seccion='$seccion', especialidad='$especialidad', telefono='$telefono',
                estado_biblioteca='$estado' WHERE id=$id";
    } else {
        $check = $conn->query("SELECT id FROM personas WHERE dni = '$dni'");
        if ($check->num_rows > 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'El DNI ya está registrado']);
            exit;
        }
        $sql = "INSERT INTO personas (nombres, apellidos, dni, tipo, grado, seccion, especialidad, telefono, estado_biblioteca) 
                VALUES ('$nombres', '$apellidos', '$dni', '$tipo', '$grado', '$seccion', '$especialidad', '$telefono', 'ACTIVO')";
    }

    if ($conn->query($sql)) {
        echo json_encode(['exito' => true, 'mensaje' => 'Guardado correctamente']);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Error BD: ' . $conn->error]);
    }
    exit;
}
?>