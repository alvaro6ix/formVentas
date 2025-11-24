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

if(isset($data['id'])) {
    // No permitir eliminar al propio usuario
    if($data['id'] == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'No puedes eliminarte a ti mismo']);
        exit();
    }
    
    $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    echo json_encode(['success' => true, 'message' => 'Usuario eliminado']);
} else {
    echo json_encode(['success' => false]);
}
?>