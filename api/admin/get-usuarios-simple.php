<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
$db = (new Database())->getConnection();

$stmt = $db->query("SELECT u.id, u.usuario, r.nombre as rol 
                    FROM usuarios u 
                    JOIN roles r ON u.rol_id = r.id 
                    ORDER BY u.usuario");
echo json_encode(['success'=>true, 'usuarios'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
?>