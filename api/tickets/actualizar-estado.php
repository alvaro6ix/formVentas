<?php
// api/tickets/actualizar-estado.php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';

if(!isset($_SESSION['rol_id'])) { exit(json_encode(['success'=>false, 'message'=>'Sin sesión'])); }

try {
    $db = (new Database())->getConnection();
    
    $ticket_id = $_POST['ticket_id'];
    $estado = $_POST['estado'];
    $user_id = $_SESSION['user_id'];

    // Actualizar estado
    $stmt = $db->prepare("UPDATE tickets_soporte SET estatus = ? WHERE id = ?");
    $stmt->execute([$estado, $ticket_id]);

    // Guardar en historial
    $stmt2 = $db->prepare("INSERT INTO ticket_seguimiento (ticket_id, usuario_id, tipo_accion, comentario) VALUES (?, ?, 'cambio_estado', ?)");
    $stmt2->execute([$ticket_id, $user_id, "Cambió el estado a: $estado"]);

    echo json_encode(['success' => true]);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>