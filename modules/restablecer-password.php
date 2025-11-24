<?php
session_start();
require_once '../config/database.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$token_valido = false;
$mensaje = '';
$tipo_mensaje = '';
$usuario_id = null;

if($token) {
    $db = (new Database())->getConnection();
    
    // Verificar token
    $stmt = $db->prepare("SELECT id, usuario, nombre FROM usuarios WHERE token_recuperacion = ? AND token_expiracion > NOW() AND activo = 1");
    $stmt->execute([$token]);
    
    if($stmt->rowCount() > 0) {
        $token_valido = true;
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $usuario_id = $user['id'];
    } else {
        $mensaje = "El enlace de recuperación es inválido o ha expirado.";
        $tipo_mensaje = 'error';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valido) {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if(strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres";
        $tipo_mensaje = 'error';
    } elseif($password !== $password_confirm) {
        $mensaje = "Las contraseñas no coinciden";
        $tipo_mensaje = 'error';
    } else {
        // Actualizar contraseña
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE usuarios SET password = ?, token_recuperacion = NULL, token_expiracion = NULL, intentos_fallidos = 0, bloqueado = 0 WHERE id = ?");
        $stmt->execute([$password_hash, $usuario_id]);
        
        // Log
        require_once '../config/config.php';
        log_activity($usuario_id, 'cambio_password', 'Cambió contraseña mediante recuperación');
        
        $mensaje = "Contraseña actualizada correctamente. Redirigiendo al login...";
        $tipo_mensaje = 'success';
        
        echo "<script>setTimeout(() => window.location.href='login.php', 2000);</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña | Bdigital</title>
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

        .reset-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 14px 50px 14px 16px;
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

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
        }

        .toggle-password:hover {
            color: #667eea;
        }

        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
        }

        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }

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
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="icon-wrapper">
            <i class="fas fa-lock"></i>
        </div>

        <h1>Restablecer Contraseña</h1>
        <p class="subtitle">Ingresa tu nueva contraseña</p>

        <?php if($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>">
            <i class="fas fa-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo $mensaje; ?></span>
        </div>
        <?php endif; ?>

        <?php if($token_valido && !$mensaje): ?>
        <form method="POST" id="resetForm">
            <div class="form-group">
                <label class="form-label">Nueva Contraseña</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" class="form-input" placeholder="Mínimo 6 caracteres" required minlength="6">
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Confirmar Contraseña</label>
                <div class="input-wrapper">
                    <input type="password" name="password_confirm" id="password_confirm" class="form-input" placeholder="Repite la contraseña" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password_confirm')"></i>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <i class="fas fa-check"></i> Actualizar Contraseña
            </button>
        </form>
        <?php elseif(!$token_valido): ?>
        <div style="text-align: center; padding: 20px;">
            <i class="fas fa-times-circle" style="font-size: 60px; color: #ef4444; margin-bottom: 15px;"></i>
            <p style="color: #6b7280;">El enlace es inválido o ha expirado.</p>
        </div>
        <?php endif; ?>

        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al Login
        </a>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            
            if(field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Validar fortaleza de contraseña
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');

        if(passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;

                // Criterios de fortaleza
                if(password.length >= 6) strength++;
                if(password.length >= 10) strength++;
                if(/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if(/\d/.test(password)) strength++;
                if(/[^a-zA-Z\d]/.test(password)) strength++;

                // Actualizar barra
                strengthBar.className = 'password-strength-bar';
                if(strength <= 2) {
                    strengthBar.classList.add('strength-weak');
                } else if(strength <= 3) {
                    strengthBar.classList.add('strength-medium');
                } else {
                    strengthBar.classList.add('strength-strong');
                }
            });
        }

        // Validar que las contraseñas coincidan
        const form = document.getElementById('resetForm');
        if(form) {
            form.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirm = document.getElementById('password_confirm').value;

                if(password !== confirm) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden');
                }
            });
        }
    </script>
</body>
</html>