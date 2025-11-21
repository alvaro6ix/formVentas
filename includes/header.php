<?php require_once 'session.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDIGITAL - Sistema de Ventas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="main-layout">
        <nav class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-network-wired"></i> BDIGITAL
            </div>
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="nueva-venta.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'nueva-venta.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-contract"></i> Nueva Venta
            </a>
            <a href="ver-ventas.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ver-ventas.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> Historial
            </a>
            <div style="margin-top: auto;">
                <div style="padding: 15px; font-size: 0.8rem; opacity: 0.7;">
                    Usuario: <?php echo $_SESSION['usuario']; ?>
                </div>
                <a href="logout.php" class="nav-link" style="color: #ff6b6b;">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </nav>
        <main class="content-area">