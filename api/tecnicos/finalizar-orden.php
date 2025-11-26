<?php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php'; // <--- 1. IMPORTAR FUNCIONES

if(!isset($_SESSION['user_id'])) { 
    die(json_encode(['success'=>false, 'message'=>'No autorizado'])); 
}

try {
    $db = (new Database())->getConnection();
    
    $venta_id = $_POST['venta_id'];
    $notas = $_POST['notas_finales'];
    $tecnico_id = $_SESSION['user_id'];

    // =======================================================
    // 1. OBTENER MATERIALES PLANIFICADOS (Original de la venta)
    // =======================================================
    $stmt = $db->prepare("SELECT materiales_utilizados, folio FROM ventas WHERE id = ?");
    $stmt->execute([$venta_id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $materiales_planificados = json_decode($venta['materiales_utilizados'], true) ?: [];

    // =======================================================
    // 2. PROCESAR MATERIALES REALMENTE USADOS (Reporte del técnico)
    // =======================================================
    $materiales_usados = [];
    if(isset($_POST['material']) && is_array($_POST['material'])) {
        for($i=0; $i < count($_POST['material']); $i++) {
            if(!empty($_POST['cantidad'][$i])) {
                $materiales_usados[] = [
                    'material' => $_POST['material'][$i],
                    'cantidad' => (int)$_POST['cantidad'][$i]
                ];
            }
        }
    }
    $json_materiales_usados = json_encode($materiales_usados, JSON_UNESCAPED_UNICODE);

    // =======================================================
    // 3. CALCULAR DIFERENCIAS (Auditoría de Inventario)
    // =======================================================
    $discrepancias = [];
    $tiene_diferencias = false;

    // Crear índice de materiales planificados por nombre
    $plan_index = [];
    foreach($materiales_planificados as $mat) {
        $nombre = is_array($mat) ? $mat['material'] : '';
        $cant = is_array($mat) ? (int)$mat['cantidad'] : 0;
        $plan_index[$nombre] = $cant;
    }

    // Comparar con lo usado
    foreach($materiales_usados as $usado) {
        $nombre = $usado['material'];
        $cant_usada = (int)$usado['cantidad'];
        $cant_planificada = $plan_index[$nombre] ?? 0;
        
        $diferencia = $cant_usada - $cant_planificada;
        
        if($diferencia != 0) {
            $tiene_diferencias = true;
            $discrepancias[] = [
                'material' => $nombre,
                'planificado' => $cant_planificada,
                'usado' => $cant_usada,
                'diferencia' => $diferencia,
                'tipo' => $diferencia > 0 ? 'exceso' : 'ahorro'
            ];
        }
    }

    // Detectar materiales planificados que NO se usaron
    foreach($materiales_planificados as $plan) {
        $nombre = is_array($plan) ? $plan['material'] : '';
        $encontrado = false;
        
        foreach($materiales_usados as $usado) {
            if($usado['material'] == $nombre) {
                $encontrado = true;
                break;
            }
        }
        
        if(!$encontrado && $nombre != '') {
            $tiene_diferencias = true;
            $discrepancias[] = [
                'material' => $nombre,
                'planificado' => (int)$plan['cantidad'],
                'usado' => 0,
                'diferencia' => -(int)$plan['cantidad'],
                'tipo' => 'no_usado'
            ];
        }
    }

    $json_discrepancias = json_encode($discrepancias, JSON_UNESCAPED_UNICODE);

    // =======================================================
    // 4. PROCESAR EVIDENCIA FOTOGRÁFICA
    // =======================================================
    $fotos_guardadas = [];
    $uploadDir = '../../assets/evidencias/';
    if(!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    if(isset($_FILES['evidencias'])) {
        $total = count($_FILES['evidencias']['name']);
        for($i=0; $i < $total; $i++) {
            if($_FILES['evidencias']['error'][$i] === 0) {
                $ext = strtolower(pathinfo($_FILES['evidencias']['name'][$i], PATHINFO_EXTENSION));
                $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                if(!in_array($ext, $extensiones_permitidas)) continue;
                if($_FILES['evidencias']['size'][$i] > 5242880) continue;
                
                $nombre = 'evidencia_' . $venta['folio'] . '_' . uniqid() . '.' . $ext;
                
                if(move_uploaded_file($_FILES['evidencias']['tmp_name'][$i], $uploadDir . $nombre)) {
                    $fotos_guardadas[] = $nombre;
                }
            }
        }
    }
    $json_fotos = json_encode($fotos_guardadas);

    // =======================================================
    // 5. ACTUALIZAR BASE DE DATOS
    // =======================================================
    // Verificar si las columnas nuevas existen
    $check_columns = $db->query("SHOW COLUMNS FROM ventas LIKE 'materiales_tecnicos'")->rowCount();
    
    if($check_columns > 0) {
        $sql = "UPDATE ventas SET 
                estatus = 'completada',
                estado_instalacion = 'finalizado',
                materiales_tecnicos = :mat_tecnicos,
                discrepancias_materiales = :discrepancias,
                evidencia_fotos = :fotos,
                notas_tecnico = :notas,
                fecha_completada = NOW()
                WHERE id = :id";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':mat_tecnicos' => $json_materiales_usados,
            ':discrepancias' => $json_discrepancias,
            ':fotos' => $json_fotos,
            ':notas' => $notas,
            ':id' => $venta_id
        ]);
    } else {
        $sql = "UPDATE ventas SET 
                estatus = 'completada',
                estado_instalacion = 'finalizado',
                materiales_utilizados = :mat,
                evidencia_fotos = :fotos,
                notas_tecnico = :notas,
                fecha_completada = NOW()
                WHERE id = :id";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':mat' => $json_materiales_usados,
            ':fotos' => $json_fotos,
            ':notas' => $notas,
            ':id' => $venta_id
        ]);
    }

    // =======================================================
    // 6. REGISTRAR EN LOG DE INVENTARIO (Específico)
    // =======================================================
    $check_log_table = $db->query("SHOW TABLES LIKE 'log_inventario'")->rowCount();
    if($check_log_table > 0) {
        try {
            $log_sql = "INSERT INTO log_inventario 
                        (venta_id, folio, tecnico_id, materiales_planificados, materiales_usados, 
                         discrepancias, tiene_diferencias, fecha_registro) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $log_stmt = $db->prepare($log_sql);
            $log_stmt->execute([
                $venta_id, $venta['folio'], $tecnico_id,
                json_encode($materiales_planificados, JSON_UNESCAPED_UNICODE),
                $json_materiales_usados, $json_discrepancias,
                $tiene_diferencias ? 1 : 0
            ]);
        } catch(Exception $log_error) { error_log($log_error->getMessage()); }
    }

    // =======================================================
    // 7. ACTUALIZAR INVENTARIO GENERAL
    // =======================================================
    $check_inv_table = $db->query("SHOW TABLES LIKE 'inventario'")->rowCount();
    if($check_inv_table > 0) {
        foreach($materiales_usados as $mat) {
            try {
                $update_inv = "UPDATE inventario 
                               SET cantidad_disponible = cantidad_disponible - :cantidad,
                                   cantidad_usada = cantidad_usada + :cantidad,
                                   ultima_actualizacion = NOW()
                               WHERE LOWER(TRIM(nombre_material)) = LOWER(TRIM(:material))";
                
                $inv_stmt = $db->prepare($update_inv);
                $inv_stmt->execute([':cantidad' => $mat['cantidad'], ':material' => $mat['material']]);
            } catch(Exception $inv_error) { error_log($inv_error->getMessage()); }
        }
    }

    // =======================================================
    // --- 8. NUEVO: REGISTRAR EN LOG DE AUDITORÍA GENERAL ---
    // =======================================================
    registrarLog($db, 'INSTALACION', "Se finalizó la instalación del folio " . $venta['folio'] . ". Notas: " . substr($notas, 0, 50));
    // -------------------------------------------------------

    // =======================================================
    // 9. RESPUESTA AL FRONTEND
    // =======================================================
    $respuesta = [
        'success' => true,
        'folio' => $venta['folio'],
        'tiene_discrepancias' => $tiene_diferencias,
        'discrepancias' => $discrepancias,
        'total_fotos' => count($fotos_guardadas)
    ];

    if($tiene_diferencias) {
        $respuesta['warning'] = 'Se detectaron diferencias entre lo planificado y lo usado';
        $respuesta['mensaje_detalle'] = count($discrepancias) . ' materiales con diferencias';
    }

    echo json_encode($respuesta);

} catch(Exception $e) {
    error_log("Error en finalizar-orden: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
}
?>