<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
session_start();

if($_SESSION['rol_id'] != 1) { die(json_encode(['success'=>false, 'message'=>'Sin permiso'])); }

try {
    $db = (new Database())->getConnection();
    
    // Subida de Logo
    $logo_sql = "";
    $params = [
        $_POST['nombre_empresa'],
        $_POST['direccion'],
        $_POST['telefono_contacto'],
        $_POST['email_contacto']
    ];

    if(isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $filename = 'logo_empresa_' . time() . '.' . $ext;
        $targetPath = '../../assets/img-logo/' . $filename;
        
        // Crear carpeta si no existe
        if(!file_exists('../../assets/img-logo/')) mkdir('../../assets/img-logo/', 0777, true);
        
        if(move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
            $logo_sql = ", logo_path = ?";
            $params[] = 'assets/img-logo/' . $filename; // Guardamos ruta relativa para BD
        }
    }

    // Actualizamos el registro (ID 1 siempre)
    // Asumimos que ya corriste el SQL que insertó el registro inicial
    $sql = "UPDATE configuracion_empresa SET 
            nombre_empresa = ?, 
            direccion = ?, 
            telefono_contacto = ?, 
            email_contacto = ? 
            $logo_sql 
            WHERE id = (SELECT id FROM configuracion_empresa LIMIT 1)"; // Truco para editar el único registro que hay

    // Si hubo logo, params tiene 5 elementos, si no, tiene 4.
    // Necesitamos ajustar el SQL dinámicamente o hacer un WHERE id simple.
    // Corrección para simplificar:
    $id_empresa = $db->query("SELECT id FROM configuracion_empresa LIMIT 1")->fetchColumn();
    
    if(!$id_empresa) {
        // Si está vacía la tabla, insertamos primero
        $db->query("INSERT INTO configuracion_empresa (nombre_empresa) VALUES ('BGITAL')");
        $id_empresa = $db->lastInsertId();
    }
    
    $sql = "UPDATE configuracion_empresa SET nombre_empresa=?, direccion=?, telefono_contacto=?, email_contacto=? $logo_sql WHERE id=?";
    $params[] = $id_empresa;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>