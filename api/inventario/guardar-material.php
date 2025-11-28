<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if(!isset($_SESSION['rol_id']) || !in_array($_SESSION['rol_id'], [1, 3])) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    $db = (new Database())->getConnection();
    
    $material_id = $_POST['material_id'] ?? null;
    
    if($material_id) {
        // ACTUALIZAR
        $sql = "UPDATE inventario SET 
                nombre_material = ?, unidad_medida = ?, precio_unitario = ?,
                cantidad_disponible = ?, stock_minimo = ?, stock_maximo = ?,
                ubicacion = ?, proveedor = ?, ultima_actualizacion = NOW()
                WHERE id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $_POST['nombre_material'],
            $_POST['unidad_medida'],
            $_POST['precio_unitario'],
            $_POST['cantidad_disponible'],
            $_POST['stock_minimo'],
            $_POST['stock_maximo'],
            $_POST['ubicacion'],
            $_POST['proveedor'],
            $material_id
        ]);
        
        $mensaje = 'Material actualizado';
    } else {
        // CREAR NUEVO
        $sql = "INSERT INTO inventario (
                nombre_material, unidad_medida, precio_unitario, cantidad_disponible,
                stock_minimo, stock_maximo, ubicacion, proveedor, cantidad_usada
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $_POST['nombre_material'],
            $_POST['unidad_medida'],
            $_POST['precio_unitario'],
            $_POST['cantidad_disponible'],
            $_POST['stock_minimo'],
            $_POST['stock_maximo'],
            $_POST['ubicacion'],
            $_POST['proveedor']
        ]);
        
        $mensaje = 'Material creado correctamente';
    }
    
    registrarLog($db, 'INVENTARIO', $mensaje . ': ' . $_POST['nombre_material']);
    
    echo json_encode(['success' => true, 'message' => $mensaje]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>