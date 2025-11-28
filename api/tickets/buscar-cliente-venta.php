<?php
// api/tickets/buscar-cliente-venta.php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';

if(!isset($_SESSION['rol_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    $db = (new Database())->getConnection();
    
    $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
    
    if(strlen($busqueda) < 3) {
        echo json_encode(['success' => false, 'message' => 'Escribe al menos 3 caracteres']);
        exit();
    }
    
    // Buscar en ventas (que son los clientes)
    $sql = "SELECT 
                id, folio, nombre_titular, telefono, celular, 
                paquete_contratado, colonia, delegacion_municipio,
                calle, numero_exterior
            FROM ventas 
            WHERE (
                nombre_titular LIKE ? 
                OR folio LIKE ?
                OR telefono LIKE ?
            )
            ORDER BY fecha_creacion DESC
            LIMIT 20";
    
    $busqueda_param = '%' . $busqueda . '%';
    $stmt = $db->prepare($sql);
    $stmt->execute([$busqueda_param, $busqueda_param, $busqueda_param]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'resultados' => $resultados,
        'total' => count($resultados)
    ]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>