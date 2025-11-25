<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';

if(!isset($_SESSION['user_id'])) { echo json_encode([]); exit; }

$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];
$rol = $_SESSION['rol_id'];

// 1. CONSTRUCCIÓN DINÁMICA DE LA CONSULTA
$sql = "SELECT * FROM ventas WHERE 1=1";
$params = [];

// FILTRO DE SEGURIDAD POR ROL
// Si es Ventas (2), solo ve sus propias ventas. Admin (1), Despacho (3) ven todo.
if ($rol == 2) {
    $sql .= " AND usuario_id = ?";
    $params[] = $uid;
}

// 2. APLICAR FILTROS DEL USUARIO
// Búsqueda de Texto
if (!empty($_POST['busqueda'])) {
    $search = "%" . $_POST['busqueda'] . "%";
    $sql .= " AND (folio LIKE ? OR nombre_titular LIKE ? OR calle LIKE ? OR colonia LIKE ?)";
    array_push($params, $search, $search, $search, $search);
}

// Filtro de Estatus
if (!empty($_POST['estatus'])) {
    $sql .= " AND estatus = ?";
    $params[] = $_POST['estatus'];
}

// Filtro de Fechas
if (!empty($_POST['fecha_inicio'])) {
    $sql .= " AND fecha_servicio >= ?";
    $params[] = $_POST['fecha_inicio'];
}
if (!empty($_POST['fecha_fin'])) {
    $sql .= " AND fecha_servicio <= ?";
    $params[] = $_POST['fecha_fin'];
}

// Ordenar por más reciente
$sql .= " ORDER BY id DESC LIMIT 50"; // Limitamos a 50 para no saturar

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $resultados]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>