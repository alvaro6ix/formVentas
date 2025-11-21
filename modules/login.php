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
    
    $stmt = $db->prepare("SELECT id, usuario, password, nombre_completo FROM usuarios WHERE usuario = :usuario AND activo = 1");
    $stmt->bindParam(":usuario", $usuario);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['nombre'] = $row['nombre_completo'];
            header("Location: dashboard.php");
            exit();
        }
    }
    $error = "Credenciales incorrectas";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BDIGITAL</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-bg">
        <div class="login-card">
            <div class="login-logo-container">
                <?php if(file_exists('../assets/img/logo.png')): ?>
                    <img src="../assets/img/logo.png" alt="Logo BDIGITAL" class="login-logo">
                <?php else: ?>
                    <div class="login-logo-placeholder">
                        <i class="fas fa-network-wired"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <h2 class="login-title">BDIGITAL</h2>
            <p class="login-subtitle">Sistema de Ventas</p>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <div style="position: relative;">
                        <input type="text" name="usuario" class="form-control" required autofocus>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div style="position: relative;">
                        <input type="password" name="password" class="form-control" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Ingresar al Sistema
                </button>
            </form>
            
            <div class="login-footer">
                <p>&copy; 2024 BDIGITAL Telecomunicaciones. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>