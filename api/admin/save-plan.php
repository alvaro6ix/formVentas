<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/functions.php'; // 1. Agregamos el archivo de logs
session_start();

if($_SESSION['rol_id'] != 1) die(json_encode(['success'=>false]));

$db = (new Database())->getConnection();

$id = $_POST['id'] ?? '';
$nombre = $_POST['nombre_plan'];
$mb = $_POST['velocidad_mb'];
$precio = $_POST['precio'];
$activo = $_POST['activo'];

if($id) {
    // Actualizar
    $stmt = $db->prepare("UPDATE planes_internet SET nombre_plan=?, velocidad_mb=?, precio=?, activo=? WHERE id=?");
    $stmt->execute([$nombre, $mb, $precio, $activo, $id]);
    
    // 2. LOG: Registramos que se editó un plan
    registrarLog($db, 'CONFIGURACION', "Se actualizó el plan ID $id: $nombre - $$precio");

} else {
    // Crear
    $stmt = $db->prepare("INSERT INTO planes_internet (nombre_plan, velocidad_mb, precio, activo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $mb, $precio, $activo]);
    
    // 3. LOG: Registramos que se creó un plan nuevo
    registrarLog($db, 'CONFIGURACION', "Se creó nuevo plan: $nombre - $$precio");
}

echo json_encode(['success' => true]);
?>