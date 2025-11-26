<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
session_start();

if($_SESSION['rol_id'] != 1) die(json_encode(['logs'=>[]]));

$db = (new Database())->getConnection();

$where = ["1=1"];
$params = [];

if(!empty($_GET['usuario'])) {
    $where[] = "l.usuario_id = ?";
    $params[] = $_GET['usuario'];
}

if(!empty($_GET['accion'])) {
    $where[] = "l.accion LIKE ?";
    $params[] = "%".$_GET['accion']."%";
}

if(!empty($_GET['fecha'])) {
    $where[] = "DATE(l.fecha) = ?";
    $params[] = $_GET['fecha'];
}

$sql = "SELECT l.*, DATE_FORMAT(l.fecha, '%d/%m/%Y %H:%i') as fecha_fmt, 
               COALESCE(u.usuario, 'Sistema') as usuario_nombre
        FROM logs_acciones l
        LEFT JOIN usuarios u ON l.usuario_id = u.id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY l.fecha DESC LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['logs' => $logs]);
?>