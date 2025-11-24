<?php
header('Content-Type: application/json');
session_start();

// Verificar permisos
if(!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db = (new Database())->getConnection();
        
        // Validar campos básicos
        $required = ['nombre', 'apellido_paterno', 'usuario', 'email', 'password', 'rol_id'];
        foreach($required as $field) {
            if(empty($_POST[$field])) {
                throw new Exception("El campo " . $field . " es obligatorio");
            }
        }
        
        // Verificar duplicados
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ?");
        $stmt->execute([$_POST['usuario'], $_POST['email']]);
        if($stmt->rowCount() > 0) {
            throw new Exception("El usuario o email ya existe");
        }

        // --- PROCESAR IMAGEN DE PERFIL ---
        $nombre_avatar = 'default.png'; // Imagen por defecto
        
        if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            
            if(in_array(strtolower($ext), $permitidas)) {
                // Crear nombre único: user_TIMESTAMP_RANDOM.ext
                $nombre_final = 'user_' . time() . '_' . uniqid() . '.' . $ext;
                
                // Ruta destino (ajustada a tu estructura)
                $uploadDir = '../../assets/img/avatar/';
                
                if(!file_exists($uploadDir)){
                    mkdir($uploadDir, 0777, true);
                }
                
                if(move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $nombre_final)) {
                    $nombre_avatar = $nombre_final; // Guardamos solo el nombre
                }
            }
        }
        // ---------------------------------
        
        $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO usuarios (
            nombre, apellido_paterno, apellido_materno, usuario, email, 
            telefono, password, rol_id, avatar, activo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $_POST['nombre'],
            $_POST['apellido_paterno'],
            $_POST['apellido_materno'] ?? '',
            $_POST['usuario'],
            $_POST['email'],
            $_POST['telefono'] ?? '',
            $password_hash,
            $_POST['rol_id'],
            $nombre_avatar, // Aquí va la foto
            $_POST['activo'] ?? 1
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Usuario creado correctamente con foto',
            'id' => $db->lastInsertId()
        ]);
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>