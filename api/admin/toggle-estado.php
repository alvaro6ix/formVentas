<?php
header('Content-Type: application/json');
session_start();

if(!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    echo json_encode(['success' => false]);
    exit();
}

require_once '../../config/database.php';
$db = (new Database())->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['id']) && isset($data['activo'])) {
    $stmt = $db->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
    $stmt->execute([$data['activo'], $data['id']]);
    
    $texto = $data['activo'] == 1 ? 'activado' : 'desactivado';
    echo json_encode(['success' => true, 'message' => "Usuario $texto correctamente"]);
} else {
    echo json_encode(['success' => false]);
}
?>

