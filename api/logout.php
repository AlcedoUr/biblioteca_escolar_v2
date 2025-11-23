<?php
// 1. Iniciar la sesión para poder acceder a ella.
session_start();

// 2. Limpiar todas las variables de sesión.
$_SESSION = array();

// 3. Destruir la sesión por completo.
// Si se usa session.use_cookies, es recomendable borrar también la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 4. Redirigir al usuario a la página de login.
// La ruta debe ser relativa al archivo actual (api/logout.php).
// Subimos un nivel (../) para llegar a la raíz del proyecto y luego a index.php.
header('Location: ../index.php');
exit; // Asegurarse de que el script se detiene después de la redirección.
?>