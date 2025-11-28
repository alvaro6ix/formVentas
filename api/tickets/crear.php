<?php
// api/tickets/crear.php

// 1. INICIAR BUFFER (Atrapa cualquier salida indeseada)
ob_start();

header('Content-Type: application/json');
// Ocultamos errores HTML para que no rompan el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

$response = [];
$code = 200;

try {
    session_start();
    
    // 2. VERIFICAR RUTAS (Causa común de error)
    if (!file_exists('../../config/database.php')) {
        throw new Exception("Error crítico: No se encuentra el archivo config/database.php");
    }
    require_once '../../config/database.php';

    if(!isset($_SESSION['rol_id'])) {
        $code = 401;
        throw new Exception("Tu sesión ha expirado. Recarga la página.");
    }

    $db = (new Database())->getConnection();
    
    // 3. RECIBIR DATOS CON SEGURIDAD
    $venta_id = $_POST['venta_id'] ?? $_POST['servicio_id'] ?? '';
    $cliente_id = $_POST['cliente_id'] ?? '';
    
    // Si faltan datos, lanzamos error claro
    if (empty($venta_id) || empty($cliente_id)) {
        throw new Exception("Datos incompletos: Faltan IDs. (Venta: $venta_id, Cliente: $cliente_id)");
    }

    // 4. GENERAR NUMERO DE TICKET (Sin Stored Procedures)
    $numero_ticket = 'TKT-' . date('ymd') . '-' . rand(1000,9999);
    
    // 5. INSERTAR
    $sql = "INSERT INTO tickets_soporte (
        numero_ticket, 
        cliente_id, 
        venta_id, 
        asunto, 
        descripcion,
        categoria, 
        prioridad, 
        requiere_materiales, 
        usuario_despacho
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $stmt->execute([
        $numero_ticket,
        $cliente_id,
        $venta_id,
        $_POST['asunto'] ?? 'Sin asunto',
        $_POST['descripcion'] ?? 'Sin descripción',
        $_POST['categoria'] ?? 'general',
        $_POST['prioridad'] ?? 'media',
        isset($_POST['requiere_materiales']) ? 1 : 0,
        $user_id
    ]);
    
    $ticket_id = $db->lastInsertId();
    
    // 6. REGISTRO EN SEGUIMIENTO (Opcional, en try-catch por si la tabla no existe)
    try {
        $stmtSeg = $db->prepare("INSERT INTO ticket_seguimiento (ticket_id, usuario_id, tipo_accion, comentario) VALUES (?, ?, 'comentario', 'Ticket creado')");
        $stmtSeg->execute([$ticket_id, $user_id]);
    } catch (Exception $ex) {
        // Si falla el seguimiento, no detenemos el proceso principal, solo lo ignoramos
    }
    
    // PREPARAR RESPUESTA EXITOSA
    $response = [
        'success' => true,
        'message' => 'Ticket creado exitosamente',
        'numero_ticket' => $numero_ticket,
        'ticket_id' => $ticket_id
    ];

} catch (Exception $e) {
    // CAPTURAR CUALQUIER ERROR
    if ($code === 200) $code = 400; // Si no era error de sesión, es error de solicitud
    $response = [
        'success' => false,
        'message' => $e->getMessage() // Aquí verás el error real
    ];
}

// 7. LIMPIAR Y ENVIAR JSON FINAL
// Borramos cualquier "Warning" o texto basura que PHP haya escupido antes
ob_end_clean(); 

// Enviamos el código HTTP correcto y el JSON limpio
http_response_code($code);
echo json_encode($response);
?>