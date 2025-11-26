<?php
/**
 * API de Códigos Postales - VERSIÓN CORREGIDA
 * Usa PDO y mejor manejo de errores
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// 1. Conexión usando tu clase Database
require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 2. Obtener el CP enviado
    $cp = isset($_GET['cp']) ? trim($_GET['cp']) : '';
    
    // 3. Validar que el CP tenga 5 dígitos
    if (strlen($cp) !== 5 || !ctype_digit($cp)) {
        echo json_encode([
            'encontrado' => false,
            'error' => 'Código Postal inválido (debe ser 5 dígitos)'
        ]);
        exit;
    }
    
    // 4. Buscar en la tabla de códigos postales
    // NOTA: Tu tabla tiene: id, codigo_postal, colonia, municipio, estado, ciudad
    $sql = "SELECT colonia, municipio, estado, ciudad
            FROM codigos_postales 
            WHERE codigo_postal = :cp 
            ORDER BY colonia ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':cp', $cp, PDO::PARAM_STR);
    $stmt->execute();
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // DEBUG: Escribir en archivo para ver qué se encontró
    error_log("CP Buscado: $cp - Resultados: " . count($resultados));
    
    if (count($resultados) > 0) {
        // Extraer datos
        $colonias = [];
        $municipio = '';
        $estado = '';
        
        foreach ($resultados as $row) {
            $colonias[] = $row['colonia'];
            // Municipio y estado son los mismos para todas las colonias del CP
            if (empty($municipio)) {
                $municipio = $row['municipio'];
                $estado = $row['estado'];
            }
        }
        
        // 5. Devolver JSON exitoso
        echo json_encode([
            'encontrado' => true,
            'estado' => $estado,
            'municipio' => $municipio,
            'colonias' => $colonias,
            'total_colonias' => count($colonias)
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        // No se encontró el CP
        echo json_encode([
            'encontrado' => false,
            'mensaje' => 'Código Postal no encontrado en la base de datos'
        ]);
    }
    
} catch (PDOException $e) {
    // Error de base de datos
    error_log("Error en api_cp.php: " . $e->getMessage());
    
    echo json_encode([
        'encontrado' => false,
        'error' => 'Error de base de datos',
        'detalle' => $e->getMessage() // Eliminar esto en producción
    ]);
    
} catch (Exception $e) {
    // Otro tipo de error
    error_log("Error general en api_cp.php: " . $e->getMessage());
    
    echo json_encode([
        'encontrado' => false,
        'error' => 'Error del servidor',
        'detalle' => $e->getMessage() // Eliminar esto en producción
    ]);
}
?>