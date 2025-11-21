<?php
// modules/login.php
session_start();
require_once '../config/database.php';

// Si ya está logueado, redirigir al dashboard
if(isset($_SESSION['user_id'])){ header("Location: dashboard.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    
    // --- CORRECCIÓN REALIZADA AQUÍ ---
    // Usamos :usuario como marcador de posición en lugar de 'BGITAL'
    $stmt = $db->prepare("SELECT id, usuario, password, nombre_completo FROM usuarios WHERE usuario = :usuario AND activo = 1");
    
    // Vinculamos el dato real del formulario a la consulta
    $stmt->bindParam(":usuario", $usuario);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Verificamos el hash de la contraseña
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['nombre'] = $row['nombre_completo'];
            header("Location: dashboard.php");
            exit();
        }
    }
    // Si el usuario no existe o la contraseña no coincide
    $error = "Credenciales incorrectas";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login | BDIGITAL</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-bg">
        <div class="login-card">
            <img src="../assets/img/logo.png" alt="Logo" class="login-logo" onerror="this.style.display='none'">
            <h2 class="login-title">Acceso Seguro</h2>
            
            <?php if(isset($error)): ?>
                <div style="background: #ffdce0; color: #c0392b; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    Ingresar <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</body>
</html>