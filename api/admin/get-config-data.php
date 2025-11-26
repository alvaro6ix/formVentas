<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
session_start();

$db = (new Database())->getConnection();

// 1. Obtener Empresa
$stmt = $db->query("SELECT * FROM configuracion_empresa LIMIT 1");
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Obtener Planes
$stmt = $db->query("SELECT * FROM planes_internet ORDER BY precio ASC");
$planes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'empresa' => $empresa,
    'planes' => $planes
]);
?>