<?php 
// 1. INICIAR SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. SEGURIDAD Y VARIABLES DE USUARIO
$rol_usuario = $_SESSION['rol_id'] ?? 0;
$nombre_usuario = $_SESSION['usuario'] ?? 'Usuario';

// --- LÓGICA DE NOMBRES DE ROLES ---
// Aquí definimos qué texto mostrar según el ID
$nombre_rol = 'Usuario';

// Asegúrate que estos IDs coincidan con tu tabla 'roles' en la base de datos
switch($rol_usuario) {
    case 1: 
    case 'admin':
        $nombre_rol = 'Administrador'; 
        break;
    case 2: 
    case 'ventas':
        $nombre_rol = 'Ventas'; 
        break;
    case 3: 
    case 'despacho':
        $nombre_rol = 'Despacho'; 
        break;
    case 4: 
    case 'tecnico':
        $nombre_rol = 'Técnico'; 
        break;
    default:
        $nombre_rol = 'Vendedor'; // Por defecto si no coincide
        break;
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

// 4. DEFINIR AVATAR
$avatar_db = $_SESSION['avatar'] ?? 'assets/img/avatar/OIP.webp';
$avatar_final = $ruta_assets . str_replace('../', '', basename($avatar_db) == $avatar_db ? 'assets/img/avatars/'.$avatar_db : $avatar_db);

// Corrección extra para rutas limpias
if(strpos($avatar_final, 'assets/img/avatars/') === false && strpos($avatar_final, 'assets/img/avatar/') === false){
     $avatar_final = $ruta_assets . 'assets/img/avatars/default.png';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGITAL - Sistema de Ventas</title>
    
    <link rel="stylesheet" href="<?php echo $ruta_assets; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $ruta_assets; ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.2);
        }
        .admin-separator {
            color: #adb5bd;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px 20px 5px;
            font-weight: bold;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 10px;
        }
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #fff;
        }
    </style>
</head>
<body>
    <div class="main-layout">
        <nav class="sidebar">
           <div class="sidebar-brand" style="padding: 15px; text-align: center;">
    <div style="background: rgba(202, 216, 215, 0.95); padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
        <img src="<?php echo $ruta_assets; ?>assets/img-logo/bgital_logo_moderno.png" 
             alt="BGITAL" 
             style="width: 100%; height: auto; max-height:60px; object-fit: contain; display: block;">
    </div>
</div>

            <a href="<?php echo $ruta_modules; ?>dashboard.php" 
               class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            
            <a href="<?php echo $ruta_modules; ?>nueva-venta.php" 
               class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'nueva-venta.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-contract"></i> Nueva Venta
            </a>
            
            <a href="<?php echo $ruta_modules; ?>ver-ventas.php" 
               class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ver-ventas.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> Historial
            </a>

            <?php if($rol_usuario == 1 || $rol_usuario == 'admin'): ?>
                <div class="admin-separator">Administración</div>
                
                <a href="<?php echo $ruta_admin; ?>gestionar-usuarios.php" 
                   class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'gestionar-usuarios.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i> Usuarios
                </a>
            <?php endif; ?>

            <div style="margin-top: auto;">
                <div class="user-info-container">
                    <img src="<?php echo $avatar_final; ?>" 
                         alt="Avatar" 
                         class="user-avatar"
                         onerror="this.src='<?php echo $ruta_assets; ?>assets/img/avatar/default.png'">
                    
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