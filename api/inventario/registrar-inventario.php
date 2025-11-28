<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if(!isset($_SESSION['rol_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    $db = (new Database())->getConnection();
    
    // Usar stored procedure
    $stmt = $db->prepare("CALL sp_registrar_movimiento_inventario(?, ?, ?, ?, NULL, NULL, ?)");
    $stmt->execute([
        $_POST['material_id'],
        $_POST['tipo_movimiento'],
        $_POST['cantidad'],
        $_SESSION['user_id'],
        $_POST['motivo']
    ]);
    
    registrarLog($db, 'INVENTARIO', "Movimiento de inventario: " . $_POST['tipo_movimiento']);
    
    echo json_encode(['success' => true, 'message' => 'Movimiento registrado correctamente']);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>