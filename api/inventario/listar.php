<?php
// api/inventario/listar.php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';

if(!isset($_SESSION['rol_id']) || !in_array($_SESSION['rol_id'], [1, 3])) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    $db = (new Database())->getConnection();
    
    $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
    
    $sql = "SELECT * FROM vista_inventario_alertas WHERE 1=1";
    
    if($filtro) {
        $sql .= " AND estado_stock = '$filtro'";
    }
    
    $sql .= " ORDER BY estado_stock DESC, nombre_material ASC";
    
    $stmt = $db->query($sql);
    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'materiales' => $materiales
    ]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>