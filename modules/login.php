<?php
session_start();
require_once '../config/database.php';

// Si ya está logueado, redirigir
if(isset($_SESSION['user_id'])){ 
    header("Location: dashboard.php"); 
    exit(); 
}

$error = '';
$intentos_restantes = 5;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    
    // Consulta corregida con los datos que necesitamos
    $stmt = $db->prepare("SELECT id, usuario, password, nombre, apellido_paterno, rol_id, activo, bloqueado, intentos_fallidos, avatar FROM usuarios WHERE usuario = :usu OR email = :mail");
    $stmt->execute([':usu' => $usuario, ':mail' => $usuario]);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['bloqueado'] == 1) {
            $error = "Usuario bloqueado. Contacta al administrador.";
        } elseif($row['activo'] == 0) {
            $error = "Usuario inactivo. Contacta al administrador.";
        } elseif (password_verify($password, $row['password'])) {
            
            // LOGIN EXITOSO
            $stmt = $db->prepare("UPDATE usuarios SET intentos_fallidos = 0, ultimo_acceso = NOW() WHERE id = ?");
            $stmt->execute([$row['id']]);
            
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['nombre'] = ($row['nombre']) ? $row['nombre'] . ' ' . $row['apellido_paterno'] : $row['usuario'];
            $_SESSION['rol_id'] = $row['rol_id'];
            $_SESSION['avatar'] = $row['avatar']; 
            
            header("Location: dashboard.php");
            exit();

        } else {
            // LOGIN FALLIDO
            $intentos = $row['intentos_fallidos'] + 1;
            $bloqueado = ($intentos >= 5) ? 1 : 0;
            $stmt = $db->prepare("UPDATE usuarios SET intentos_fallidos = ?, bloqueado = ? WHERE id = ?");
            $stmt->execute([$intentos, $bloqueado, $row['id']]);
            
            $intentos_restantes = 5 - $intentos;
            if($bloqueado) $error = "Usuario bloqueado por intentos fallidos.";
            else $error = "Credenciales incorrectas. Intentos restantes: $intentos_restantes";
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bdigital | Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow: hidden;
            background: #020617; /* Fondo muy oscuro (casi negro azulado) */
        }

        /* CONTENEDOR PRINCIPAL */
        .login-container { display: flex; height: 100vh; position: relative; }

        /* LADO IZQUIERDO - FORMULARIO */
        .login-left {
            width: 45%;
            background: #ffffff; /* Blanco limpio */
            display: flex; flex-direction: column; justify-content: center;
            padding: 60px; position: relative; z-index: 10;
            box-shadow: 5px 0 30px rgba(0,0,0,0.15); /* Sombra gris elegante */
        }

        .logo-section {
            position: absolute; top: 40px; left: 60px;
            display: flex; align-items: center; gap: 12px;
        }

        /* Logo como imagen */
        .logo-img {
            height: 50px; width: auto; object-fit: contain;
        }

        .login-form-wrapper { max-width: 420px; width: 100%; margin: 0 auto; }

        .login-title { 
            font-size: 36px; font-weight: 800; 
            color: #0f172a; /* Gris muy oscuro */
            margin-bottom: 8px; 
        }

        .login-subtitle { 
            color: #64748b; /* Gris medio */
            font-size: 15px; margin-bottom: 40px; 
        }

        .form-group { margin-bottom: 24px; }

        .form-label { 
            display: block; font-size: 14px; font-weight: 600; 
            color: #334155; /* Gris azulado oscuro */
            margin-bottom: 8px; 
        }

        .input-wrapper { position: relative; }

        .form-input {
            width: 100%; padding: 14px 16px;
            border: 2px solid #e2e8f0; /* Borde gris claro */
            border-radius: 10px;
            font-size: 15px; transition: all 0.3s ease; background: #f8fafc;
        }

        .form-input:focus { 
            outline: none; 
            border-color: #2563eb; /* Azul Fuerte (Primary) */
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); 
            background: white;
        }

        .input-icon {
            position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; cursor: pointer; transition: color 0.3s;
        }
        .input-icon:hover { color: #2563eb; }

        .forgot-password { text-align: right; margin-top: 8px; }
        .forgot-password a { 
            color: #2563eb; text-decoration: none; font-size: 14px; font-weight: 600; 
        }
        .forgot-password a:hover { text-decoration: underline; }

        .btn-login {
            width: 100%; padding: 16px;
            /* Gradiente Azul: De Azul Fuerte a Azul Claro */
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white; border: none; border-radius: 10px;
            font-size: 16px; font-weight: 600; cursor: pointer;
            transition: all 0.3s ease; 
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
            position: relative; overflow: hidden;
        }

        .btn-login:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4); 
        }

        .alert {
            padding: 14px 18px; border-radius: 10px; margin-bottom: 24px;
            display: flex; align-items: center; gap: 12px; animation: slideDown 0.3s ease;
        }
        .alert-danger { 
            background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; 
        }

        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* LADO DERECHO - MAPA FIBRA ÓPTICA (AZUL) */
        .login-right {
            width: 55%; 
            background: #0f172a; /* Azul noche muy oscuro */
            position: relative; overflow: hidden;
        }

        #fiberCanvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

        .fiber-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            /* Gradiente suave sobre el canvas */
            background: radial-gradient(circle at 50% 50%, transparent 0%, rgba(15, 23, 42, 0.8) 100%);
            pointer-events: none; z-index: 2;
        }

        .animated-text {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            z-index: 3; text-align: center;
        }

        .brand-name {
            font-size: 80px; font-weight: 900;
            /* Gradiente de Texto: Azul Fuerte a Cyan */
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 50%, #22d3ee 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; 
            animation: glow 3s ease-in-out infinite;
            text-shadow: 0 0 30px rgba(59, 130, 246, 0.3); 
            letter-spacing: -2px;
        }

        @keyframes glow {
            0%, 100% { filter: drop-shadow(0 0 15px rgba(59, 130, 246, 0.4)); }
            50% { filter: drop-shadow(0 0 30px rgba(34, 211, 238, 0.6)); }
        }

        .brand-subtitle {
            color: #93c5fd; /* Azul muy claro */
            font-size: 18px; margin-top: 10px; letter-spacing: 4px;
            text-transform: uppercase; animation: fadeInOut 2s ease-in-out infinite;
        }
        @keyframes fadeInOut { 0%, 100% { opacity: 0.6; } 50% { opacity: 1; } }

        /* Stats decorativos */
        .stats-overlay {
            position: absolute; bottom: 40px; left: 40px; right: 40px;
            z-index: 3; display: flex; gap: 30px;
        }

        .stat-item {
            background: rgba(30, 41, 59, 0.4); /* Fondo oscuro translúcido */
            backdrop-filter: blur(10px);
            padding: 20px; border-radius: 12px; 
            border: 1px solid rgba(59, 130, 246, 0.2); /* Borde azul sutil */
            flex: 1;
            transition: transform 0.3s;
        }
        .stat-item:hover { transform: translateY(-5px); border-color: rgba(59, 130, 246, 0.5); }

        .stat-value { 
            font-size: 28px; font-weight: 700; 
            color: #60a5fa; /* Azul brillante */
            margin-bottom: 5px; 
        }

        .stat-label { 
            color: #cbd5e1; /* Gris claro */
            font-size: 13px; text-transform: uppercase; letter-spacing: 1px; 
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .login-left { width: 50%; padding: 40px; }
            .login-right { width: 50%; }
            .brand-name { font-size: 60px; }
        }
        @media (max-width: 768px) {
            .login-container { flex-direction: column; }
            .login-left { width: 100%; height: 100vh; }
            .login-right { display: none; }
            .logo-section { position: static; margin-bottom: 40px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="logo-section">
                <img src="../assets/img-logo/bgital_logo_moderno.png" alt="Bdigital" class="logo-img">
            </div>

            <div class="login-form-wrapper">
                <h1 class="login-title">Login</h1>
                <p class="login-subtitle">Ingresa tus credenciales para acceder al sistema</p>

                <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label class="form-label">Usuario o Email</label>
                        <div class="input-wrapper">
                            <input type="text" name="usuario" class="form-input" placeholder="usuario@bdigital.com" required autofocus>
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" class="form-input" placeholder="••••••••••" required>
                            <i class="fas fa-eye input-icon" id="togglePassword"></i>
                        </div>
                        <div class="forgot-password">
                            <a href="#" onclick="mostrarRecuperacion(); return false;">¿Olvidaste tu contraseña?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </form>

                <div style="margin-top: 30px; text-align: center; color: #070707ff; font-size: 13px;">
                    <p>&copy; <?php echo date('Y'); ?> BGITAL Telecomunicaciones. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>

        <div class="login-right">
            <canvas id="fiberCanvas"></canvas>
            <div class="fiber-overlay"></div>
            
            <div class="animated-text">
                <div class="brand-name">BGITAL</div>
                <div class="brand-subtitle">Red de Fibra Óptica</div>
            </div>

            <div class="stats-overlay">
                <div class="stat-item">
                    <div class="stat-value" id="stat1">0</div>
                    <div class="stat-label">Nodos Activos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="stat2">0</div>
                    <div class="stat-label">Km de Fibra</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="stat3">0</div>
                    <div class="stat-label">Clientes</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ==========================================
        // ANIMACIÓN DE FIBRA ÓPTICA (VERSIÓN AZUL)
        // ==========================================
        const canvas = document.getElementById('fiberCanvas');
        const ctx = canvas.getContext('2d');

        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;

        window.addEventListener('resize', () => {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
        });

        const nodes = [];
        const particles = [];

        // Crear nodos
        for(let i = 0; i < 25; i++) {
            nodes.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 0.6,
                vy: (Math.random() - 0.5) * 0.6,
                radius: Math.random() * 2 + 1.5
            });
        }

        class Particle {
            constructor(start, end) {
                this.start = start;
                this.end = end;
                this.x = start.x;
                this.y = start.y;
                this.progress = 0;
                this.speed = 0.01 + Math.random() * 0.015;
            }

            update() {
                this.progress += this.speed;
                if(this.progress >= 1) {
                    this.progress = 0;
                    this.start = this.end;
                    this.end = nodes[Math.floor(Math.random() * nodes.length)];
                }
                this.x = this.start.x + (this.end.x - this.start.x) * this.progress;
                this.y = this.start.y + (this.end.y - this.start.y) * this.progress;
            }

            draw() {
                // PARTÍCULA AZUL BRILLANTE
                ctx.beginPath();
                ctx.arc(this.x, this.y, 2, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(59, 130, 246, ${1 - this.progress})`; // Azul Tailwind-500
                ctx.fill();
                
                // ESTELA CYAN
                ctx.beginPath();
                ctx.arc(this.x, this.y, 4, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(34, 211, 238, ${0.2 * (1 - this.progress)})`; // Cyan Tailwind-400
                ctx.fill();
            }
        }

        // Crear partículas
        for(let i = 0; i < 60; i++) {
            const start = nodes[Math.floor(Math.random() * nodes.length)];
            const end = nodes[Math.floor(Math.random() * nodes.length)];
            particles.push(new Particle(start, end));
        }

        function animate() {
            // FONDO TRANSPARENTE PARA ESTELA
            ctx.fillStyle = 'rgba(15, 23, 42, 0.2)'; // Azul noche oscuro
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            nodes.forEach(node => {
                node.x += node.vx;
                node.y += node.vy;

                if(node.x < 0 || node.x > canvas.width) node.vx *= -1;
                if(node.y < 0 || node.y > canvas.height) node.vy *= -1;

                // NODO (PUNTO)
                ctx.beginPath();
                ctx.arc(node.x, node.y, node.radius, 0, Math.PI * 2);
                ctx.fillStyle = '#3b82f6'; // Azul
                ctx.fill();
            });

            // DIBUJAR CONEXIONES (LÍNEAS AZULES)
            for(let i = 0; i < nodes.length; i++) {
                for(let j = i + 1; j < nodes.length; j++) {
                    const dx = nodes[i].x - nodes[j].x;
                    const dy = nodes[i].y - nodes[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if(distance < 180) {
                        ctx.beginPath();
                        ctx.moveTo(nodes[i].x, nodes[i].y);
                        ctx.lineTo(nodes[j].x, nodes[j].y);
                        // Color de línea azulado tenue
                        ctx.strokeStyle = `rgba(59, 130, 246, ${1 - distance / 180})`; 
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }

            particles.forEach(particle => {
                particle.update();
                particle.draw();
            });

            requestAnimationFrame(animate);
        }

        animate();

        // ==========================================
        // ANIMACIÓN DE STATS (NÚMEROS)
        // ==========================================
        function animateValue(id, start, end, duration) {
            const obj = document.getElementById(id);
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                obj.textContent = Math.floor(progress * (end - start) + start);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        setTimeout(() => {
            animateValue("stat1", 0, 847, 2000);
            animateValue("stat2", 0, 1250, 2000);
            animateValue("stat3", 0, 3429, 2000);
        }, 500);

        // MOSTRAR/OCULTAR PASSWORD
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        function mostrarRecuperacion() {
            alert('Función de recuperación de contraseña - Se implementará en siguiente fase');
        }
    </script>
</body>
</html>