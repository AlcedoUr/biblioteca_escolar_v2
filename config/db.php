<?php
/*
* Archivo de Conexión a Base de Datos
* Retorna una instancia de mysqli
*/

$host = 'localhost';
$user = 'root';
$password = ''; // Por defecto en XAMPP es vacío
$db = 'biblioteca_escolar';
$port = 3306; // CAMBIA ESTO A 3307 SI USASTE LA SOLUCIÓN 2

// Crear conexión
$conn = new mysqli($host, $user, $password, $db, $port);

// Verificar si hubo error
if ($conn->connect_error) {
    // En producción no se debe mostrar el error real, pero en desarrollo sí
    die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));
}

// Configurar caracteres a UTF-8 (Para que las ñ y tildes se vean bien)
$conn->set_charset("utf8mb4");
