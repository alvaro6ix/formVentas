<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';

if(!isset($_SESSION['user_id'])) { die(json_encode(['success'=>false, 'message'=>'No login'])); }

$db = (new Database())->getConnection();
$id = $_POST['venta_id'];
$nuevo_estado = $_POST['nuevo_estado']; // 'pausado', 'instalando'

try {
    // 1. Obtener estado actual y tiempo
    $stmt = $db->prepare("SELECT estado_instalacion, ultimo_cambio_estado, tiempo_acumulado FROM ventas WHERE id = ?");
    $stmt->execute([$id]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);

    $tiempo_acumulado = $actual['tiempo_acumulado'];
    
    // SI ESTABA CORRIENDO Y LO VAMOS A PAUSAR:
    // Calculamos cuánto tiempo pasó desde el último cambio hasta AHORA y lo sumamos
    if (($actual['estado_instalacion'] == 'en_camino' || $actual['estado_instalacion'] == 'instalando') && $nuevo_estado == 'pausado') {
        $inicio = strtotime($actual['ultimo_cambio_estado']);
        $ahora = time();
        $segundos_transcurridos = $ahora - $inicio;
        $tiempo_acumulado += $segundos_transcurridos;
    }

    // Actualizamos BD
    $sql = "UPDATE ventas SET 
            estado_instalacion = ?, 
            tiempo_acumulado = ?, 
            ultimo_cambio_estado = NOW() 
            WHERE id = ?";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$nuevo_estado, $tiempo_acumulado, $id]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>