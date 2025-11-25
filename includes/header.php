<?php 
// 1. INICIAR SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. SEGURIDAD Y VARIABLES DE USUARIO
$rol_usuario = $_SESSION['rol_id'] ?? 0;
$nombre_usuario = $_SESSION['usuario'] ?? 'Usuario';

// --- LÓGICA DE NOMBRES DE ROLES ---
$nombre_rol = 'Usuario';
switch($rol_usuario) {
    case 1: $nombre_rol = 'Administrador'; break;
    case 2: $nombre_rol = 'Ventas'; break;
    case 3: $nombre_rol = 'Despacho'; break;
    case 4: $nombre_rol = 'Técnico'; break;
    default: $nombre_rol = 'Invitado'; break;
}

// 3. LÓGICA DE RUTAS DINÁMICAS
$en_carpeta_admin = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;

if ($en_carpeta_admin) {
    $ruta_assets  = '../../';
    $ruta_modules = '../';
    $ruta_admin   = '';
} else {
    $ruta_assets  = '../';
    $ruta_modules = '';
    $ruta_admin   = 'admin/';
}

// 4. AVATAR
$avatar_db = $_SESSION['avatar'] ?? 'assets/img/avatar/default.png';
$avatar_name = basename($avatar_db);
$avatar_final = $ruta_assets . 'assets/img/avatars/' . $avatar_name;

// Fallback por si la imagen no carga
$avatar_error = $ruta_assets . 'assets/img/avatars/default.png';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGITAL - <?php echo $nombre_rol; ?></title>
    
    <link rel="stylesheet" href="<?php echo $ruta_assets; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $ruta_assets; ?>assets/css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Ajustes visuales del menú */
        .menu-section-label {
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 15px 20px 5px;
            letter-spacing: 1px;
        }
        .user-info-container {
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.05);
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
            border: 2px solid rgba(255,255,255,0.2);
        }
        .nav-link.active {
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.15) 0%, transparent 100%);
            border-left: 4px solid #3b82f6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="main-layout">
        <nav class="sidebar">
            <div class="sidebar-brand" style="padding: 15px; text-align: center;">
                <div style="background: rgba(255, 255, 255, 0.95); padding: 8px; border-radius: 8px;">
                    <img src="<?php echo $ruta_assets; ?>assets/img-logo/bgital_logo_moderno.png" 
                         alt="BGITAL" style="width: 100%; max-height: 45px; object-fit: contain;">
                </div>
            </div>

            <div class="menu-section-label">Principal</div>
            <a href="<?php echo $ruta_modules; ?>dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <?php switch($rol_usuario): 
                case 1: // ADMIN ?>
                    <div class="menu-section-label text-warning">Administración</div>
                    <a href="<?php echo $ruta_admin; ?>gestionar-usuarios.php" class="nav-link"><i class="fas fa-users-cog"></i> Usuarios</a>
                    <a href="<?php echo $ruta_modules; ?>ver-ventas.php" class="nav-link"><i class="fas fa-list-alt"></i> Todas las Ventas</a>
                    <a href="#" class="nav-link"><i class="fas fa-chart-pie"></i> Reportes</a>
                    <a href="#" class="nav-link"><i class="fas fa-cogs"></i> Configuración</a>
                    <a href="#" class="nav-link"><i class="fas fa-shield-alt"></i> Logs</a>
                <?php break; ?>

                <?php case 2: // VENTAS ?>
                    <div class="menu-section-label text-info">Ventas</div>
                    <a href="<?php echo $ruta_modules; ?>nueva-venta.php" class="nav-link"><i class="fas fa-plus-circle"></i> Nueva Venta</a>
                    <a href="<?php echo $ruta_modules; ?>ver-ventas.php" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> Mis Ventas</a>
                    <a href="#" class="nav-link"><i class="fas fa-users"></i> Clientes</a>
                    <a href="#" class="nav-link"><i class="fas fa-clipboard-list"></i> Mis Reportes</a>
                <?php break; ?>

                <?php case 3: // DESPACHO ?>
                    <div class="menu-section-label text-success">Logística</div>
                    <a href="<?php echo $ruta_modules; ?>dashboard.php" class="nav-link"><i class="fas fa-bell"></i> Órdenes Pendientes</a>
                    <a href="#" class="nav-link"><i class="fas fa-map-marked-alt"></i> Asignar Técnicos</a>
                    <a href="#" class="nav-link"><i class="fas fa-boxes"></i> Inventario</a>
                    <a href="#" class="nav-link"><i class="fas fa-route"></i> Rutas</a>
                <?php break; ?>

                <?php case 4: // TÉCNICO ?>
                    <div class="menu-section-label text-danger">Campo</div>
                    <a href="<?php echo $ruta_modules; ?>dashboard.php" class="nav-link"><i class="fas fa-tasks"></i> Mis Asignaciones</a>
                    <a href="#" class="nav-link"><i class="fas fa-calendar-check"></i> Instalaciones Hoy</a>
                    <a href="#" class="nav-link"><i class="fas fa-tools"></i> Materiales</a>
                    <a href="#" class="nav-link"><i class="fas fa-camera"></i> Reportar Trabajo</a>
                <?php break; ?>

            <?php endswitch; ?>

            <div style="margin-top: auto;">
                <div class="user-info-container">
                    <img src="<?php echo $avatar_final; ?>" alt="Avatar" class="user-avatar" onerror="this.src='<?php echo $avatar_error; ?>'">
                    
                    <div style="font-size: 0.85rem; overflow: hidden;">
                        <a href="<?php echo $ruta_modules; ?>perfil.php" style="text-decoration: none; color: inherit;">
                            <div style="font-weight: bold; color: white; cursor: pointer;">
                                <?php echo htmlspecialchars($nombre_usuario); ?> <i class="fas fa-pen-square" style="font-size: 0.7rem; color: #aaa;"></i>
                            </div>
                        </a>
                        <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">
                            <?php echo $nombre_rol; ?>
                        </div>
                    </div>
                </div>

                <a href="<?php echo $ruta_modules; ?>logout.php" class="nav-link" style="color: #ff4d4d;">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </nav>
        
        <main class="content-area">