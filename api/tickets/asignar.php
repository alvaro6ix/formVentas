<?php
// api/tickets/asignar.php
header('Content-Type: application/json');

// Desactivar errores de HTML para no romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../../config/database.php';

try {
    if(!isset($_SESSION['rol_id'])) {
        throw new Exception("No hay sesión activa.");
    }

    $db = (new Database())->getConnection();
    
    // 1. RECIBIR DATOS
    $ticket_id = $_POST['ticket_id'] ?? '';
    $tecnico_id = $_POST['tecnico_id'] ?? '';
    $instrucciones = $_POST['instrucciones'] ?? '';
    
    // 2. VALIDAR QUE EL ID NO ESTÉ VACÍO
    if (empty($ticket_id)) {
        throw new Exception("Error: El sistema no recibió el ID del ticket. Intenta recargar la página.");
    }

    // 3. VALIDAR QUE EL TICKET REALMENTE EXISTA EN LA BD
    // Esto evita el error SQLSTATE[23000]
    $checkStmt = $db->prepare("SELECT id FROM tickets_soporte WHERE id = ?");
    $checkStmt->execute([$ticket_id]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception("El ticket con ID #$ticket_id no existe en la base de datos.");
    }

    // 4. ACTUALIZAR EL TICKET
    // Usamos 'asignado_a' que es el nombre correcto que vimos antes
    $sql = "UPDATE tickets_soporte SET 
            asignado_a = ?, 
            estatus = 'asignado',
            fecha_asignacion = NOW()
            WHERE id = ?";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$tecnico_id, $ticket_id]);

    // 5. GUARDAR EN HISTORIAL
    $sqlHistorial = "INSERT INTO ticket_seguimiento 
                    (ticket_id, usuario_id, tipo_accion, comentario) 
                    VALUES (?, ?, 'asignacion', ?)";
                    
    $stmtHistorial = $db->prepare($sqlHistorial);
    $comentario = "Asignado a técnico (ID: $tecnico_id). Notas: $instrucciones";
    
    $stmtHistorial->execute([
        $ticket_id, 
        $_SESSION['user_id'], 
        $comentario
    ]);

    echo json_encode(['success' => true, 'message' => 'Técnico asignado correctamente']);

} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>