<?php
// api/admin/dashboard-stats.php
header('Content-Type: application/json');
require_once '../../config/database.php';
session_start();

// Seguridad: Solo admin
if(!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    // 1. KPIs Principales (Tarjetas Superiores)
    $kpis = [
        'ventas_hoy' => 0,
        'ventas_mes' => 0,
        'instalaciones_pendientes' => 0,
        'ingresos_estimados' => 0
    ];

    // Ventas Hoy
    $stmt = $db->query("SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha_creacion) = CURDATE()");
    $kpis['ventas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Ventas del Mes
    $stmt = $db->query("SELECT COUNT(*) as total FROM ventas WHERE MONTH(fecha_creacion) = MONTH(CURRENT_DATE()) AND YEAR(fecha_creacion) = YEAR(CURRENT_DATE())");
    $kpis['ventas_mes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Instalaciones Pendientes (No completadas ni canceladas)
    $stmt = $db->query("SELECT COUNT(*) as total FROM ventas WHERE estatus NOT IN ('completada', 'cancelada')");
    $kpis['instalaciones_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Datos para Gráfica: Tendencia de Ventas (Últimos 7 días)
    $stmt = $db->query("
        SELECT DATE(fecha_creacion) as fecha, COUNT(*) as cantidad 
        FROM ventas 
        WHERE fecha_creacion >= DATE(NOW()) - INTERVAL 7 DAY
        GROUP BY DATE(fecha_creacion)
        ORDER BY fecha ASC
    ");
    $tendencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Datos para Gráfica: Distribución de Paquetes
    $stmt = $db->query("
        SELECT paquete_contratado as nombre, COUNT(*) as cantidad 
        FROM ventas 
        GROUP BY paquete_contratado
    ");
    $paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Datos para el Embudo de Conversión
    // Paso 1: Venta Registrada (Total)
    // Paso 2: Asignada a Despacho (asignado_despacho IS NOT NULL)
    // Paso 3: Asignada a Técnico (asignado_tecnico IS NOT NULL)
    // Paso 4: Instalada (estatus = 'completada')
    
    $funnel = [];
    $funnel['registradas'] = $db->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
    $funnel['despachadas'] = $db->query("SELECT COUNT(*) FROM ventas WHERE asignado_despacho IS NOT NULL")->fetchColumn();
    $funnel['en_tecnico']  = $db->query("SELECT COUNT(*) FROM ventas WHERE asignado_tecnico IS NOT NULL")->fetchColumn();
    $funnel['completadas'] = $db->query("SELECT COUNT(*) FROM ventas WHERE estatus = 'completada'")->fetchColumn();

    echo json_encode([
        'success' => true,
        'kpis' => $kpis,
        'tendencia' => $tendencia,
        'paquetes' => $paquetes,
        'funnel' => $funnel
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>