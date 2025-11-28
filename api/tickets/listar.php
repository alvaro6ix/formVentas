<?php
// api/tickets/listar.php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';

if(!isset($_SESSION['rol_id'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión']);
    exit();
}

try {
    $db = (new Database())->getConnection();
    
    // Capturar variables de sesión
    $rol_id = $_SESSION['rol_id'];
    $user_id = $_SESSION['user_id'];

    // Filtros del frontend
    $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
    $prioridad = $_GET['prioridad'] ?? null;
    $estatus = $_GET['estatus'] ?? null;
    $categoria = $_GET['categoria'] ?? null;
    
    // --- QUERY BASE ---
    $sql = "SELECT 
                t.id, 
                t.numero_ticket, 
                t.prioridad, 
                t.asunto, 
                t.categoria, 
                t.estatus, 
                t.fecha_apertura,
                v.folio as folio_venta,
                v.nombre_titular as cliente,
                v.telefono,
                v.colonia,
                u.nombre_completo as tecnico_asignado,
                TIMESTAMPDIFF(HOUR, t.fecha_apertura, NOW()) as horas_abierto
            FROM tickets_soporte t
            LEFT JOIN ventas v ON t.venta_id = v.id
            LEFT JOIN usuarios u ON t.asignado_a = u.id
            WHERE 1=1";
            
    $params = [];
    
    // --- LÓGICA DE ROLES (LA PARTE IMPORTANTE) ---
    // Si es TÉCNICO (Rol 4), solo ve sus tickets asignados
    if ($rol_id == 4) {
        $sql .= " AND t.asignado_a = ?";
        $params[] = $user_id;
    }
    // (Si es Admin o Despacho, no agregamos filtro, ven todo)

    // --- FILTROS DE BÚSQUEDA ---
    if(!empty($busqueda)) {
        $sql .= " AND (t.numero_ticket LIKE ? OR v.nombre_titular LIKE ? OR v.folio LIKE ?)";
        $term = '%' . $busqueda . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }
    
    if(!empty($prioridad)) {
        $sql .= " AND t.prioridad = ?";
        $params[] = $prioridad;
    }
    
    if(!empty($estatus)) {
        $sql .= " AND t.estatus = ?";
        $params[] = $estatus;
    }
    
    if(!empty($categoria)) {
        $sql .= " AND t.categoria = ?";
        $params[] = $categoria;
    }
    
    // Ordenar
    $sql .= " ORDER BY t.fecha_apertura DESC LIMIT 100";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'tickets' => $tickets]);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>