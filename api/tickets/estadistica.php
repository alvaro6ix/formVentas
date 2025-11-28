<?php
// api/tickets/estadisticas.php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';
ini_set('display_errors', 0);

try {
    $db = (new Database())->getConnection();
    $rol_id = $_SESSION['rol_id'] ?? 0;
    $user_id = $_SESSION['user_id'] ?? 0;

    // Consulta base
    $sql = "SELECT 
            SUM(CASE WHEN estatus = 'abierto' THEN 1 ELSE 0 END) as abiertos,
            SUM(CASE WHEN estatus = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
            SUM(CASE WHEN estatus = 'resuelto' AND DATE(fecha_resolucion) = CURDATE() THEN 1 ELSE 0 END) as resueltos_hoy,
            0 as sla_cumplido -- (Lógica SLA pendiente)
            FROM tickets_soporte
            WHERE 1=1";
            
    $params = [];

    // SI ES TÉCNICO, FILTRAMOS SUS NÚMEROS
    if ($rol_id == 4) {
        $sql .= " AND asignado_a = ?";
        $params[] = $user_id;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'stats' => $stats]);

} catch(Exception $e) {
    echo json_encode(['success' => false]);
}
?>