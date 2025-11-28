<?php
// api/tickets/finalizar.php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';

// Desactivar errores visibles para no romper JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if(!isset($_SESSION['rol_id'])) { 
    echo json_encode(['success'=>false, 'message'=>'Sin sesión']); 
    exit(); 
}

try {
    $db = (new Database())->getConnection();
    
    $ticket_id = $_POST['ticket_id'];
    $solucion = $_POST['solucion'];
    $user_id = $_SESSION['user_id'];

    if(empty($solucion)) { throw new Exception("Debes escribir la solución."); }

    // --- MANEJO DE FOTOS ---
    $rutas_fotos = [];
    $directorio = "../../uploads/evidencia_tickets/";
    
    // Crear carpeta si no existe
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    if(isset($_FILES['fotos'])) {
        $total = count($_FILES['fotos']['name']);
        for($i=0; $i < $total; $i++) {
            if($_FILES['fotos']['tmp_name'][$i] != "") {
                $nombre_original = $_FILES['fotos']['name'][$i];
                $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                // Nombre único: ticketID_fecha_random.jpg
                $nombre_nuevo = $ticket_id . '_' . date('YmdHis') . '_' . rand(100,999) . '.' . $extension;
                
                if(move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $directorio . $nombre_nuevo)) {
                    // Guardamos la ruta relativa para la BD
                    $rutas_fotos[] = "uploads/evidencia_tickets/" . $nombre_nuevo;
                }
            }
        }
    }

    // Convertir array de fotos a texto (JSON) para guardar en un solo campo
    $fotos_json = json_encode($rutas_fotos);

    // --- ACTUALIZAR TICKET ---
    $sql = "UPDATE tickets_soporte SET 
            estatus = 'resuelto',
            solucion = ?,
            evidencia_fotos = ?,
            fecha_resolucion = NOW(),
            fecha_cierre = NOW()
            WHERE id = ?";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$solucion, $fotos_json, $ticket_id]);

    // --- GUARDAR EN HISTORIAL ---
    $stmt2 = $db->prepare("INSERT INTO ticket_seguimiento (ticket_id, usuario_id, tipo_accion, comentario) VALUES (?, ?, 'cierre', ?)");
    $stmt2->execute([$ticket_id, $user_id, "Ticket Resuelto. Solución: " . substr($solucion, 0, 50) . "..."]);

    echo json_encode(['success' => true]);

} catch(Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>