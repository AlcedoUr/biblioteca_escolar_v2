<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. VALIDAR SESIÓN
if (!isset($_SESSION['user_id'])) { 
    header('Location: ../index.php'); 
    exit; 
}

$rol = $_SESSION['user_rol'];
$nombre_usuario = $_SESSION['user_nombre'] ?? 'Usuario';
$pagina_actual = basename($_SERVER['PHP_SELF']);

// 2. SEGURIDAD DE NAVEGACIÓN
// Si es ESTUDIANTE y trata de entrar a algo que NO sea biblioteca_virtual.php, lo regresamos.
if ($rol == 'ESTUDIANTE' && $pagina_actual != 'biblioteca_virtual.php') {
    header('Location: biblioteca_virtual.php');
    exit;
}

// Si es DOCENTE, restringimos acceso (Lista blanca)
$paginas_docente = ['catalogo.php', 'biblioteca_virtual.php', 'reservas.php'];
if ($rol == 'DOCENTE' && !in_array($pagina_actual, $paginas_docente)) {
    header('Location: catalogo.php'); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BiblioSystem - I.E. 3054</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-vino: #8B1538; 
            --color-dorado: #D4AF37; 
            --ancho-sidebar: 260px;
        }
        body { font-family: 'Inter', sans-serif; background-color: #F5F6FA; overflow-x: hidden; }
        
        /* --- ESTILOS PARA ADMIN Y DOCENTE (CON SIDEBAR) --- */
        #sidebar { width: var(--ancho-sidebar); height: 100vh; position: fixed; left: 0; top: 0; background-color: var(--color-vino); color: white; display: flex; flex-direction: column; z-index: 1000; transition: all 0.3s; }
        .sidebar-header { padding: 20px; display: flex; align-items: center; gap: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-box { width: 40px; height: 40px; background: var(--color-dorado); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--color-vino); font-size: 1.2rem; }
        .sidebar-menu { padding: 20px 10px; flex-grow: 1; overflow-y: auto; }
        
        .nav-link { color: rgba(255,255,255,0.8); padding: 12px 15px; margin-bottom: 5px; border-radius: 8px; display: flex; align-items: center; gap: 10px; transition: all 0.2s; text-decoration: none; }
        .nav-link:hover { color: white; background-color: rgba(255,255,255,0.1); }
        .nav-link.active { background-color: var(--color-dorado); color: #333; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        #content { margin-left: var(--ancho-sidebar); padding: 20px; width: calc(100% - var(--ancho-sidebar)); min-height: 100vh; }
        .topbar { background: white; padding: 15px 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items-center; margin-bottom: 30px; }
        
        @media (max-width: 768px) {
            #sidebar { margin-left: calc(var(--ancho-sidebar) * -1); }
            #sidebar.active { margin-left: 0; }
            #content { margin-left: 0; width: 100%; }
        }

        /* --- ESTILOS ESPECÍFICOS PARA ESTUDIANTE (SIN SIDEBAR) --- */
        .student-navbar { background-color: var(--color-vino); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .student-container { max-width: 1200px; margin: 0 auto; padding: 30px 15px; }
    </style>
</head>
<body>

<?php if ($rol == 'ESTUDIANTE'): ?>
    
    <nav class="navbar navbar-expand-lg navbar-dark student-navbar px-3">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="#">
                <div class="bg-white text-danger rounded d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                    <i class="bi bi-book-half"></i>
                </div>
                <span>Biblioteca Virtual</span>
            </a>
            
            <div class="d-flex align-items-center gap-3">
                <div class="text-end text-white d-none d-sm-block">
                    <div class="small opacity-75">Bienvenido</div>
                    <div class="fw-bold" style="line-height: 1;"><?php echo explode(' ', $nombre_usuario)[0]; ?></div>
                </div>
                <div class="vr text-white opacity-50"></div>
                <a href="../api/logout.php" class="btn btn-outline-light btn-sm fw-bold">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="student-container">

<?php else: ?>

<div id="sidebar">
        <div class="sidebar-header">
            <div class="logo-box"><i class="bi bi-book"></i></div>
            <div style="line-height: 1.1;">
                <div class="fw-bold small">Biblioteca</div>
                <div class="small" style="opacity: 0.7; font-size: 0.75rem;">I.E. N.° 3054</div>
            </div>
        </div>
        
        <div class="p-3">
            <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: rgba(0,0,0,0.2);">
                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center fw-bold text-dark" style="width: 35px; height: 35px;">
                    <?php echo strtoupper(substr($nombre_usuario, 0, 1)); ?>
                </div>
                <div>
                    <div class="small fw-bold text-white text-truncate" style="max-width: 120px;">
                        Hola, <?php echo explode(' ', $nombre_usuario)[0]; ?>
                    </div>
                    <span class="badge bg-warning text-dark" style="font-size: 0.6rem;"><?php echo $rol; ?></span>
                </div>
            </div>
        </div>

        <nav class="nav flex-column sidebar-menu">
            
            <?php if ($rol == 'DOCENTE'): ?>
                <div class="text-white-50 small fw-bold mt-3 mb-1 ps-3">DOCENTES</div>
                <a href="catalogo.php" class="nav-link <?php echo ($pagina_actual == 'catalogo.php') ? 'active' : ''; ?>">
                    <i class="bi bi-search"></i> Catálogo Físico
                </a>
                <a href="biblioteca_virtual.php" class="nav-link <?php echo ($pagina_actual == 'biblioteca_virtual.php') ? 'active' : ''; ?>">
                    <i class="bi bi-cloud-download"></i> Recursos Digitales
                </a>
                <a href="reservas.php" class="nav-link <?php echo ($pagina_actual == 'reservas.php') ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-plus"></i> Reservar Material
                </a>
            <?php endif; ?>

            <?php if ($rol == 'ADMINISTRADOR' || $rol == 'BIBLIOTECARIO'): ?>
                <a href="dashboard.php" class="nav-link <?php echo ($pagina_actual == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="bi bi-grid"></i> Dashboard
                </a>
                
                <div class="text-white-50 small fw-bold mt-3 mb-1 ps-3">GESTIÓN</div>
                <a href="libros.php" class="nav-link <?php echo ($pagina_actual == 'libros.php') ? 'active' : ''; ?>">
                    <i class="bi bi-book"></i> Catálogo Libros
                </a>
                <a href="biblioteca_virtual.php" class="nav-link <?php echo ($pagina_actual == 'biblioteca_virtual.php') ? 'active' : ''; ?>">
                    <i class="bi bi-cloud-download"></i> Biblio. Virtual
                </a>
                <a href="personas.php" class="nav-link <?php echo ($pagina_actual == 'personas.php') ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i> Usuarios
                </a>

<a href="accesos.php" class="nav-link <?php echo ($pagina_actual == 'accesos.php') ? 'active' : ''; ?>">
    <i class="bi bi-key"></i> Accesos Docentes
</a>
                <div class="text-white-50 small fw-bold mt-3 mb-1 ps-3">CIRCULACIÓN</div>
                <a href="historial.php" class="nav-link <?php echo ($pagina_actual == 'historial.php') ? 'active' : ''; ?>">
                    <i class="bi bi-arrow-left-right"></i> Préstamos
                </a>
                <a href="reservas.php" class="nav-link <?php echo ($pagina_actual == 'reservas.php') ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-check"></i> Reservas
                </a>
                <a href="incidencias.php" class="nav-link <?php echo ($pagina_actual == 'incidencias.php') ? 'active' : ''; ?>">
                    <i class="bi bi-exclamation-triangle"></i> Incidencias
                </a>
                <a href="reportes.php" class="nav-link <?php echo ($pagina_actual == 'reportes.php') ? 'active' : ''; ?>">
                    <i class="bi bi-bar-chart"></i> Reportes
                </a>
            <?php endif; ?>

        </nav>
        
        <div class="p-3 border-top border-white border-opacity-10">
            <a href="../api/logout.php" class="btn btn-outline-light w-100 btn-sm d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
            </a>
        </div>
    </div>

    <div id="content">
        <div class="topbar">
            <div>
                <h5 class="m-0 fw-bold" style="color: var(--color-vino)">Virgen de las Mercedes</h5>
                <small class="text-muted">Sistema de Gestión Bibliotecaria</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end text-muted lh-1 d-none d-sm-block">
                    <div class="fw-bold fs-5"><?php echo date('H:i'); ?></div>
                    <small style="font-size: 0.75rem;"><?php echo date('d/m/Y'); ?></small>
                </div>
                <button class="btn btn-light d-md-none border" onclick="document.getElementById('sidebar').classList.toggle('active')">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>

<?php endif; ?>