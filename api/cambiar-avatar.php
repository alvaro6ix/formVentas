<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

if (isset($_FILES['nuevo_avatar']) && $_FILES['nuevo_avatar']['error'] === UPLOAD_ERR_OK) {
    
    $fileTmpPath = $_FILES['nuevo_avatar']['tmp_name'];
    $fileName = $_FILES['nuevo_avatar']['name'];
    $fileSize = $_FILES['nuevo_avatar']['size'];
    $fileType = $_FILES['nuevo_avatar']['type'];
    
    // 1. Validar extensión
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
    
    if (in_array($fileExtension, $allowedfileExtensions)) {
        
        // 2. Crear nombre único para evitar que se reemplacen si se llaman igual
        // Ejemplo: avatar_1_6546546.jpg
        $newFileName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExtension;
        
        // 3. Ruta de destino (Ajusta si tu carpeta se llama diferente)
        // Nota: Estamos subiendo un nivel (../) desde la carpeta API hacia assets
        $uploadFileDir = '../assets/img/avatar/';
        
        // Crear carpeta si no existe
        if (!file_exists($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }
        
        $dest_path = $uploadFileDir . $newFileName;

        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            
            // 4. Ruta para guardar en BD (Relativa para que funcione en HTML)
            // Guardamos: assets/img/avatar/nombre.jpg
            $dbPath = 'assets/img/avatar/' . $newFileName;

            try {
                $db = (new Database())->getConnection();
                $stmt = $db->prepare("UPDATE usuarios SET avatar = :avatar WHERE id = :id");
                $stmt->execute([
                    ':avatar' => $dbPath,
                    ':id' => $_SESSION['user_id']
                ]);

                // 5. ¡IMPORTANTE! Actualizar la sesión actual para ver el cambio sin salir
                $_SESSION['avatar'] = $dbPath;

                echo json_encode(['success' => true, 'message' => 'Avatar actualizado', 'new_path' => $dbPath]);
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al mover el archivo a la carpeta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo JPG, PNG, WEBP']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se envió ningún archivo o hubo un error']);
}
?>
