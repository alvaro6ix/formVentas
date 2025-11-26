<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php'; // <--- 1. IMPORTAR FUNCIONES

if(!isset($_SESSION['user_id'])) { die(json_encode(['success'=>false])); }

try {
    $db = (new Database())->getConnection();
    
    $venta_id = $_POST['venta_id'];
    $tecnico_id = $_POST['tecnico_id'];
    $despacho_id = $_SESSION['user_id'];
    
    $sql = "UPDATE ventas SET 
            asignado_tecnico = ?, 
            asignado_despacho = ?,
            fecha_asignacion_tecnico = NOW(), /* ESTO INICIA EL CRONÓMETRO */
            estado_instalacion = 'en_camino', /* CAMBIA ESTADO AUTOMÁTICO */
            ultimo_cambio_estado = NOW()
            WHERE id = ?";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$tecnico_id, $despacho_id, $venta_id]);

    // --- 2. REGISTRAR LOG DE AUDITORÍA ---
    // Guardamos quién asignó a quién
    registrarLog($db, 'DESPACHO', "Se asignó al técnico ID $tecnico_id para atender la venta ID $venta_id");
    
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>