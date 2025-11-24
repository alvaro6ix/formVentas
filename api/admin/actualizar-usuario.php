<?php
header('Content-Type: application/json');
session_start();

// 1. Verificar permisos
if(!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

require_once '../../config/database.php';
$db = (new Database())->getConnection();

try {
    if (!isset($_POST['usuario_id'])) {
        throw new Exception("ID de usuario no recibido");
    }

    $id = $_POST['usuario_id'];
    
    // 2. Preparar la consulta base (sin contraseña ni avatar aún)
    $sql = "UPDATE usuarios SET 
            nombre = ?, apellido_paterno = ?, apellido_materno = ?, 
            usuario = ?, email = ?, telefono = ?, rol_id = ?, activo = ?";
    
    $params = [
        $_POST['nombre'],
        $_POST['apellido_paterno'],
        $_POST['apellido_materno'] ?? '',
        $_POST['usuario'],
        $_POST['email'],
        $_POST['telefono'] ?? '',
        $_POST['rol_id'],
        $_POST['activo'] ?? 1
    ];
    
    // 3. Lógica para Contraseña (solo si se envió una nueva)
    if(!empty($_POST['password'])) {
        $sql .= ", password = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_BCRYPT);
    }

    // 4. Lógica para el AVATAR (¡ESTO FALTABA!)
    if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if(in_array(strtolower($ext), $allowed)) {
            // Generar nombre único
            $nombre_archivo = 'user_' . $id . '_' . time() . '.' . $ext;
            
            // Ruta donde se guardará física (subir 2 niveles desde api/admin)
            $ruta_destino = '../../assets/img/avatars/' . $nombre_archivo;
            
            // Crear carpeta si no existe
            if (!file_exists('../../assets/img/avatars/')) {
                mkdir('../../assets/img/avatars/', 0777, true);
            }

            if(move_uploaded_file($_FILES['avatar']['tmp_name'], $ruta_destino)){
                // Actualizar BD con el nombre del archivo
                $sql .= ", avatar = ?";
                $params[] = $nombre_archivo;

                // Si el usuario se edita a sí mismo, actualizamos la sesión al instante
                if($id == $_SESSION['user_id']) {
                    $_SESSION['avatar'] = $nombre_archivo;
                }
            }
        }
    }
    
    // 5. Finalizar consulta
    $sql .= " WHERE id = ?";
    $params[] = $id;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>