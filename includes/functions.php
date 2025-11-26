<?php
// includes/functions.php

/**
 * Registra una acción en la base de datos de auditoría.
 * * @param PDO $db Conexión a la base de datos
 * @param string $accion Nombre corto de la acción (Ej: 'LOGIN', 'NUEVA_VENTA')
 * @param string $detalles Descripción detallada (Ej: 'Se vendió folio 105 a Juan')
 */
function registrarLog($db, $accion, $detalles) {
    // Si no hay sesión iniciada, el usuario es 0 (Sistema/Invitado)
    $usuario_id = $_SESSION['user_id'] ?? 0;
    
    // Obtener IP del cliente
    $ip = $_SERVER['REMOTE_ADDR'];
    
    try {
        $stmt = $db->prepare("INSERT INTO logs_acciones (usuario_id, accion, detalles, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $accion, $detalles, $ip]);
    } catch (Exception $e) {
        // Silenciosamente fallamos para no detener el sistema si el log falla
        error_log("Error guardando log: " . $e->getMessage());
    }
}
?>