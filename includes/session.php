<?php
/**
 * Gestión segura de sesiones
 * Bdigital Ventas
 */

require_once __DIR__ . '/../config/config.php';

// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Regenerar ID de sesión periódicamente para prevenir session fixation
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['usuario'])) {
    // Guardar la URL actual para redirigir después del login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    header("Location: ../modules/login.php");
    exit();
}

// Verificar timeout de sesión
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    // Sesión expirada
    session_unset();
    session_destroy();
    header("Location: ../modules/login.php?timeout=1");
    exit();
}

// Actualizar última actividad
$_SESSION['last_activity'] = time();

// Validar IP del usuario (opcional, comentar si causa problemas con IPs dinámicas)
/*
if (isset($_SESSION['user_ip'])) {
    if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header("Location: ../modules/login.php?security=1");
        exit();
    }
} else {
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
}
*/

/**
 * Función para verificar permisos de usuario
 */
function check_permission($required_role = 'vendedor') {
    $user_role = $_SESSION['rol'] ?? 'vendedor';
    
    $roles_hierarchy = [
        'admin' => 3,
        'supervisor' => 2,
        'vendedor' => 1
    ];
    
    $user_level = $roles_hierarchy[$user_role] ?? 0;
    $required_level = $roles_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

/**
 * Función para verificar si es admin
 */
function is_admin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}
?>