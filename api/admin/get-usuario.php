<?php
header('Content-Type: application/json');
session_start();

if(!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    echo json_encode(['success' => false]);
    exit();
}

require_once '../../config/database.php';
$db = (new Database())->getConnection();

if(isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'usuario' => $usuario]);
} else {
    echo json_encode(['success' => false]);
}
?>