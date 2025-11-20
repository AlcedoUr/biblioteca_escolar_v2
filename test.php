<?php
require_once 'config/db.php';

// Consulta simple para ver si traemos al admin
$sql = "SELECT * FROM usuarios";
$resultado = $conn->query($sql);

if ($resultado) {
    echo "<h1>¡CONEXIÓN EXITOSA! 🚀</h1>";
    echo "<h3>Usuarios encontrados en la base de datos:</h3>";
    echo "<ul>";
    while ($fila = $resultado->fetch_assoc()) {
        echo "<li>" . $fila['username'] . " (" . $fila['rol'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "Error en la consulta: " . $conn->error;
}
?>