<?php
session_start();
// --- SEGURIDAD: NO CACHÉ ---
// Esto obliga al navegador a pedir los datos al servidor siempre,
// evitando que alguien vea pantallas viejas al dar "Atrás".
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
// --- 1. VERIFICACIÓN DE SESIÓN ACTIVA ---
// Si no hay un rol definido en la sesión, significa que el usuario no ha iniciado sesión.
// Lo redirigimos a la página de login (index.php).
if (!isset($_SESSION['user_rol'])) {
    // La ruta debe ser absoluta desde la raíz del sitio web.
    // Asumimos que la carpeta del proyecto es la raíz.
    $login_path = '/bibliotecav2/index.php'; 
    header("Location: $login_path");
    exit;
}

// --- 2. DEFINICIÓN DE PERMISOS POR ROL ---
// Se define qué vistas puede ver cada rol.
$permisos = [
    'ADMINISTRADOR' => [
        // El admin tiene acceso a todo, no necesita una lista explícita,
        // pero lo dejamos por claridad. Se manejará como un caso especial.
    ],
    'BIBLIOTECARIO' => [
        'dashboard.php',
        'catalogo.php',
        'libros.php',
        'personas.php',
        'prestamo_lote.php', // Asumo este para préstamos
        'incidencias.php',   // Para material extraviado
        'reportes.php',
        'reporte_inventario.php',
        'reporte_morosos.php',
        'reporte_uso.php',
        'reservas.php',
        'historial.php',
        'detalle_prestamo.php',
        'biblioteca_virtual.php' // <-- AÑADIDO
    ],
    'DOCENTE' => [
        'reservas.php',
        'catalogo.php', // Es útil que pueda ver el catálogo para reservar
        'mi_historial.php'
    ],
    'ESTUDIANTE' => [
        'biblioteca_virtual.php'
    ]
];

// --- 3. LÓGICA DE VALIDACIÓN DE ACCESO ---
$rol_usuario = $_SESSION['user_rol'];
$pagina_actual = basename($_SERVER['PHP_SELF']); // Obtiene el nombre del archivo actual (ej: 'dashboard.php')

// Caso especial: El rol ADMINISTRADOR siempre tiene acceso.
if ($rol_usuario == 'ADMINISTRADOR') {
    return; // No se hace ninguna validación, se le permite continuar.
}

// Para los otros roles, verificamos si tienen permisos.
if (isset($permisos[$rol_usuario])) {
    // Si la página actual NO ESTÁ en la lista de permisos del rol...
    if (!in_array($pagina_actual, $permisos[$rol_usuario])) {
        // ...mostramos un mensaje de acceso denegado y detenemos la carga de la página.
        http_response_code(403); // Código de Acceso Prohibido
        echo "<!DOCTYPE html>
              <html lang='es'>
              <head>
                <meta charset='UTF-8'>
                <title>Acceso Denegado</title>
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
              </head>
              <body class='bg-light'>
                <div class='container'>
                  <div class='row justify-content-center align-items-center vh-100'>
                    <div class='col-md-6 text-center'>
                      <h1 class='display-1 fw-bold text-danger'>403</h1>
                      <h2 class='fw-bold'>Acceso Denegado</h2>
                      <p class='text-muted'>No tienes permiso para acceder a esta página. Contacta al administrador si crees que es un error.</p>
                      <a href='javascript:history.back()' class='btn btn-primary mt-3'>Volver atrás</a>
                    </div>
                  </div>
                </div>
              </body>
              </html>";
        exit;
    }
} else {
    // Si el rol que viene de la sesión no está ni siquiera definido en los permisos (ej: 'VISITANTE')
    http_response_code(403);
    echo "<h1>Acceso Denegado: Rol no reconocido.</h1>";
    exit;
}
?>