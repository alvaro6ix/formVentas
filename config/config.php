<?php
/**
 * Configuraciones globales del sistema
 * Bdigital Ventas
 */

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de sesión segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Tiempo de sesión (30 minutos)
define('SESSION_TIMEOUT', 1800);

// Rutas del sistema
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads/');
define('TEMP_PATH', BASE_PATH . '/assets/img/temp/');
define('PDF_PATH', BASE_PATH . '/pdfs/');

// Crear directorios si no existen
if (!file_exists(TEMP_PATH)) mkdir(TEMP_PATH, 0755, true);
if (!file_exists(PDF_PATH)) mkdir(PDF_PATH, 0755, true);
if (!file_exists(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);

// Configuración de errores (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/php_errors.log');

// Configuración de aplicación
define('APP_NAME', 'Bdigital Ventas');
define('APP_VERSION', '2.0');
define('COMPANY_NAME', 'BDIGITAL TELECOMUNICACIONES');

// Límites de archivos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

/**
 * Función para sanitizar inputs
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Función para validar email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Función para generar token CSRF
 */
function generate_csrf_token() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Función para verificar token CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Función para registrar actividad
 */
function log_activity($usuario_id, $accion, $descripcion) {
    try {
        require_once __DIR__ . '/database.php';
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("INSERT INTO logs_actividad (usuario_id, accion, descripcion, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $usuario_id,
            $accion,
            $descripcion,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        error_log("Error al registrar actividad: " . $e->getMessage());
    }
}

/**
 * Función para formatear moneda
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}

/**
 * Función para formatear fecha
 */
function format_date($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}
?>