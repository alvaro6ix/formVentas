<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if (isset($_GET['cp'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT colonia, municipio, estado FROM codigos_postales WHERE codigo_postal = :cp");
    $stmt->execute([':cp' => $_GET['cp']]);
    
    if ($stmt->rowCount() > 0) {
        $colonias = [];
        $base = null;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!$base) { $base = ['municipio' => $row['municipio'], 'estado' => $row['estado']]; }
            $colonias[] = $row['colonia'];
        }
        echo json_encode(['success' => true, 'data' => $base, 'colonias' => $colonias]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>