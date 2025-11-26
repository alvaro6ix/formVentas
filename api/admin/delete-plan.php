<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
session_start();

if($_SESSION['rol_id'] != 1) die(json_encode(['success'=>false]));

$data = json_decode(file_get_contents('php://input'), true);
$db = (new Database())->getConnection();

$stmt = $db->prepare("DELETE FROM planes_internet WHERE id = ?");
$stmt->execute([$data['id']]);

echo json_encode(['success' => true]);
?>