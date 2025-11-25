<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';

if(!isset($_SESSION['user_id'])) { die(json_encode(['success'=>false])); }

try {
    $db = (new Database())->getConnection();
    
    $venta_id = $_POST['venta_id'];
    $notas = $_POST['notas_finales'];
    
    // 1. PROCESAR MATERIALES (Array a JSON)
    $materiales = [];
    if(isset($_POST['material'])) {
        for($i=0; $i < count($_POST['material']); $i++) {
            if(!empty($_POST['cantidad'][$i])) {
                $materiales[] = [
                    'material' => $_POST['material'][$i],
                    'cantidad' => $_POST['cantidad'][$i]
                ];
            }
        }
    }
    $json_materiales = json_encode($materiales, JSON_UNESCAPED_UNICODE);

    // 2. PROCESAR FOTOS DE EVIDENCIA
    $fotos_guardadas = [];
    $uploadDir = '../../assets/evidencias/';
    if(!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    if(isset($_FILES['evidencias'])) {
        $total = count($_FILES['evidencias']['name']);
        for($i=0; $i < $total; $i++) {
            if($_FILES['evidencias']['error'][$i] === 0) {
                $ext = pathinfo($_FILES['evidencias']['name'][$i], PATHINFO_EXTENSION);
                $nombre = 'evidencia_' . $venta_id . '_' . uniqid() . '.' . $ext;
                
                if(move_uploaded_file($_FILES['evidencias']['tmp_name'][$i], $uploadDir . $nombre)) {
                    $fotos_guardadas[] = $nombre;
                }
            }
        }
    }
    $json_fotos = json_encode($fotos_guardadas);

    // 3. ACTUALIZAR BD
    $sql = "UPDATE ventas SET 
            estatus = 'completada',
            estado_instalacion = 'finalizado',
            materiales_utilizados = :mat,
            evidencia_fotos = :fotos,
            notas_tecnico = :notas,
            fecha_completada = NOW()
            WHERE id = :id";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':mat' => $json_materiales,
        ':fotos' => $json_fotos,
        ':notas' => $notas,
        ':id' => $venta_id
    ]);

    echo json_encode(['success' => true]);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>