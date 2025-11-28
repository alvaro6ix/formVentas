<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';

if(!isset($_SESSION['rol_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    $db = (new Database())->getConnection();
    
    $stats = [];
    
    // Total materiales
    $stmt = $db->query("SELECT COUNT(*) as total FROM inventario");
    $stats['total_materiales'] = $stmt->fetch()['total'];
    
    // Stock crítico
    $stmt = $db->query("SELECT COUNT(*) as total FROM vista_inventario_alertas WHERE estado_stock = 'critico'");
    $stats['stock_critico'] = $stmt->fetch()['total'];
    
    // Valor total
    $stmt = $db->query("SELECT SUM(valor_inventario) as total FROM vista_inventario_alertas");
    $stats['valor_total'] = $stmt->fetch()['total'] ?? 0;
    
    // Movimientos hoy
    $stmt = $db->query("SELECT COUNT(*) as total FROM movimientos_inventario WHERE DATE(fecha_movimiento) = CURDATE()");
    $stats['movimientos_hoy'] = $stmt->fetch()['total'];
    
    echo json_encode(['success' => true, 'stats' => $stats]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>