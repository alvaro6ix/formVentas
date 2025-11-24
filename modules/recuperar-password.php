<?php
session_start();
require_once '../config/database.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $db = (new Database())->getConnection();
        
        // Verificar si el email existe
        $stmt = $db->prepare("SELECT id, usuario, nombre, apellido_paterno FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generar token único
            $token = bin2hex(random_bytes(32));
            $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Guardar token en BD
            $stmt = $db->prepare("UPDATE usuarios SET token_recuperacion = ?, token_expiracion = ? WHERE id = ?");
            $stmt->execute([$token, $expiracion, $user['id']]);
            
            // Link de recuperación
            $link = "http://" . $_SERVER['HTTP_HOST'] . "/FormVentas/modules/restablecer-password.php?token=" . $token;
            
            // Aquí deberías enviar el email
            // Por ahora solo mostramos el mensaje
            $mensaje = "Se ha enviado un enlace de recuperación a tu correo. (Link: $link)";
            $tipo_mensaje = 'success';
            
            // Log
            require_once '../config/config.php';
            log_activity($user['id'], 'recuperacion_password', 'Solicitó recuperación de contraseña');
            
        } else {
            $mensaje = "Si el email existe, recibirás un enlace de recuperación.";
            $tipo_mensaje = 'info';
        }
    } else {
        $mensaje = "Email inválido";
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | Bdigital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .recovery-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 30px;
            transition: gap 0.3s;
        }

        .back-link:hover {
            gap: 12px;
        }

        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .icon-wrapper i {
            font-size: 32px;
            color: white;
        }

        h1 {
            text-align: center;
            color: #1a1a2e;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 15px;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        @media (max-width: 600px) {
            .recovery-card {
                padding: 30px 25px;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="recovery-card">
        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al Login
        </a>

        <div class="icon-wrapper">
            <i class="fas fa-key"></i>
        </div>

        <h1>Recuperar Contraseña</h1>
        <p class="subtitle">
            Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        <?php if($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>">
            <i class="fas fa-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : ($tipo_mensaje == 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
            <span><?php echo $mensaje; ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" class="form-input" placeholder="tu-email@bdigital.com" required autofocus>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Enviar Enlace de Recuperación
            </button>
        </form>
    </div>
</body>
</html>