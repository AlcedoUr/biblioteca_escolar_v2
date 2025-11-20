<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }

// Detectamos en qué página estamos para pintar el botón de dorado
$pagina_actual = basename($_SERVER['PHP_SELF']);
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
            --color-vino: #8B1538; /* Tu color corporativo */
            --color-dorado: #D4AF37; 
            --ancho-sidebar: 260px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F6FA;
            overflow-x: hidden; /* Evita scroll horizontal */
        }

        /* MENU LATERAL (IZQUIERDA) */
        #sidebar {
            width: var(--ancho-sidebar);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: var(--color-vino);
            color: white;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo-box {
            width: 40px;
            height: 40px;
            background: var(--color-dorado);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-vino);
            font-size: 1.2rem;
        }

        .sidebar-menu {
            padding: 20px 10px;
            flex-grow: 1;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }

        /* BOTÓN ACTIVO (Dorado) */
        .nav-link.active {
            background-color: var(--color-dorado);
            color: #333;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* CONTENIDO (DERECHA) */
        #content {
            margin-left: var(--ancho-sidebar);
            padding: 20px;
            width: calc(100% - var(--ancho-sidebar));
            min-height: 100vh;
        }

        /* BARRA SUPERIOR BLANCA */
        .topbar {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

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
                <?php echo strtoupper(substr($_SESSION['user_nombre'] ?? 'U', 0, 1)); ?>
            </div>
            <div>
                <div class="small fw-bold text-white">Hola, <?php echo explode(' ', $_SESSION['user_nombre'] ?? 'Usuario')[0]; ?></div>
                <span class="badge bg-warning text-dark" style="font-size: 0.6rem;"><?php echo $_SESSION['user_rol'] ?? 'User'; ?></span>
            </div>
        </div>
    </div>

    <nav class="nav flex-column sidebar-menu">
        <a href="dashboard.php" class="nav-link <?php echo ($pagina_actual == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="bi bi-grid"></i> Dashboard
        </a>
        <a href="libros.php" class="nav-link <?php echo ($pagina_actual == 'libros.php') ? 'active' : ''; ?>">
            <i class="bi bi-book"></i> Gestión de Libros
        <a href="personas.php" class="nav-link ...">
    <i class="bi bi-people"></i> Gestión de Usuarios
</a>
        <a href="historial.php" class="nav-link <?php echo ($pagina_actual == 'historial.php') ? 'active' : ''; ?>">
            <i class="bi bi-clock-history"></i> Historial / Devolución
        </a>
        <a href="reportes.php" class="nav-link ...">
    <i class="bi bi-bar-chart"></i> Reportes
</a>
    </nav>
</div>

<div id="content">
    
    <div class="topbar">
        <div>
            <h5 class="m-0 fw-bold" style="color: var(--color-vino)">Virgen de las Mercedes</h5>
            <small class="text-muted">Sistema de Gestión Bibliotecaria</small>
        </div>
        <div>
            <span class="text-muted small me-3"><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y'); ?></span>
            <a href="../api/logout.php" class="btn btn-sm btn-outline-danger">Salir</a>
        </div>
    </div>