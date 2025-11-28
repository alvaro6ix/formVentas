<?php
session_start();
require_once '../config/database.php';

// ... (tu código PHP permanece igual) ...
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
    
    $stmt = $db->prepare("SELECT id, usuario, password, nombre, apellido_paterno, rol_id, activo, bloqueado, intentos_fallidos, avatar FROM usuarios WHERE usuario = :usu OR email = :mail");
    $stmt->execute([':usu' => $usuario, ':mail' => $usuario]);
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['bloqueado'] == 1) {
            $error = "Usuario bloqueado. Contacta al administrador.";
        } elseif($row['activo'] == 0) {
            $error = "Usuario inactivo. Contacta al administrador.";
        } elseif (password_verify($password, $row['password'])) {
            
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow: hidden;
            background: #020617;
        }

        .login-container { display: flex; height: 100vh; position: relative; }

        .login-left {
            width: 45%;
            background: #d6d8ddff;
            display: flex; flex-direction: column; justify-content: center;
            padding: 60px; position: relative; z-index: 10;
            box-shadow: 5px 0 30px rgba(0,0,0,0.15);
        }

        .logo-section {
            position: absolute; top: 40px; left: 60px;
            display: flex; align-items: center; gap: 12px;
        }

        .logo-img {
            height: 50px; width: auto; object-fit: contain;
        }

        .login-form-wrapper { max-width: 420px; width: 100%; margin: 0 auto; }

        .login-title { 
            font-size: 36px; font-weight: 800; 
            color: #0f172a;
            margin-bottom: 8px; 
        }

        .login-subtitle { 
            color: #64748b;
            font-size: 15px; margin-bottom: 40px; 
        }

        .form-group { margin-bottom: 24px; }

        .form-label { 
            display: block; font-size: 14px; font-weight: 600; 
            color: #334155;
            margin-bottom: 8px; 
        }

        .input-wrapper { position: relative; }

        .form-input {
            width: 100%; padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px; transition: all 0.3s ease; background: #f8fafc;
        }

        .form-input:focus { 
            outline: none; 
            border-color: #2563eb;
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

        /* LADO DERECHO - MAPA SUPER DETALLADO */
        .login-right {
            width: 55%; 
            background: #0a0f1c;
            position: relative; overflow: hidden;
        }

        #tolucaMap { 
            position: absolute; 
            top: 0; left: 0; 
            width: 100%; height: 100%;
        }

        .map-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(10, 15, 28, 0.3) 0%, rgba(10, 15, 28, 0.8) 100%);
            pointer-events: none; z-index: 2;
        }

        /* MARCA DE AGUA BGITAL */
        .watermark {
            position: absolute;
            z-index: 1;
            pointer-events: none;
            opacity: 0.15;
            font-size: 180px;
            font-weight: 900;
            color: #3b82f6;
            font-family: 'Inter', sans-serif;
            text-transform: uppercase;
            letter-spacing: 8px;
            transform: rotate(-25deg);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            white-space: nowrap;
        }

        .watermark-1 {
            top: 20%;
            left: 10%;
        }

        .watermark-2 {
            top: 60%;
            left: 50%;
            transform: rotate(15deg);
        }

        .watermark-3 {
            top: 80%;
            left: 20%;
            transform: rotate(-10deg);
        }

        .central-marker {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            z-index: 4; text-align: center;
        }

        .marker-pin {
            width: 20px; height: 20px; background: #22d3ee;
            border: 3px solid white; border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.4), 0 0 20px rgba(34, 211, 238, 0.8);
            animation: techPulse 2s infinite;
        }

        @keyframes techPulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 211, 238, 0.7), 0 0 0 0 rgba(34, 211, 238, 0.4); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 10px rgba(34, 211, 238, 0), 0 0 0 20px rgba(34, 211, 238, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 211, 238, 0), 0 0 0 0 rgba(34, 211, 238, 0); }
        }

        .marker-label {
            background: linear-gradient(135deg, #22d3ee, #3b82f6); color: white; padding: 6px 12px;
            border-radius: 15px; font-size: 11px; font-weight: 700;
            margin-top: 10px; white-space: nowrap; text-transform: uppercase;
            letter-spacing: 1px;
        }

        .metro-station {
            position: absolute; z-index: 3; text-align: center;
        }

        .metro-icon {
            width: 16px; height: 16px; background: #dc2626;
            border: 2px solid white; border-radius: 50%;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
        }

        .metro-label {
            background: #dc2626; color: white; padding: 3px 8px;
            border-radius: 10px; font-size: 9px; font-weight: 600;
            margin-top: 5px; white-space: nowrap;
        }

        /* Stats decorativos */
        .stats-overlay {
            position: absolute; bottom: 30px; left: 30px; right: 30px;
            z-index: 3; display: flex; gap: 15px;
        }

        .stat-item {
            background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px);
            padding: 12px; border-radius: 10px; 
            border: 1px solid rgba(59, 130, 246, 0.3); box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            flex: 1; text-align: center;
            transition: all 0.3s ease;
        }
        .stat-item:hover { 
            transform: translateY(-3px); 
            border-color: rgba(34, 211, 238, 0.6);
            box-shadow: 0 6px 25px rgba(34, 211, 238, 0.2);
        }

        .stat-value { 
            font-size: 20px; font-weight: 800; 
            background: linear-gradient(135deg, #60a5fa, #22d3ee);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 4px; 
        }

        .stat-label { 
            color: #cbd5e1;
            font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .login-left { width: 50%; padding: 40px; }
            .login-right { width: 50%; }
            .watermark { font-size: 120px; }
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
            <canvas id="tolucaMap"></canvas>
            <div class="map-overlay"></div>
            
            <!-- MARCAS DE AGUA BGITAL REPETIDAS -->
            <div class="watermark watermark-1">BGITAL</div>
            <div class="watermark watermark-2">BGITAL</div>
            <div class="watermark watermark-3">BGITAL</div>

            <div class="central-marker">
                <div class="marker-pin"></div>
                <div class="marker-label">Central BGITAL</div>
            </div>

            <!-- Estaciones del Metro -->
            <div class="metro-station" style="top: 35%; left: 45%;">
                <div class="metro-icon"></div>
                <div class="metro-label">Toluca</div>
            </div>
            <div class="metro-station" style="top: 40%; left: 48%;">
                <div class="metro-icon"></div>
                <div class="metro-label">Metepec</div>
            </div>
            <div class="metro-station" style="top: 32%; left: 42%;">
                <div class="metro-icon"></div>
                <div class="metro-label">Lerma</div>
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
                    <div class="stat-label">Clientes Conectados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="stat4">0</div>
                    <div class="stat-label">Zonas Cubiertas</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ==========================================
        // MAPA ULTRA DETALLADO - TOLUCA + METEPEC
        // ==========================================
        const canvas = document.getElementById('tolucaMap');
        const ctx = canvas.getContext('2d');

        function resizeCanvas() {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        // Calles principales de Toluca y Metepec (más de 30 calles)
        const callesToluca = [
            // AVENIDAS PRINCIPALES (Toluca)
            { name: "PASEO TOLLOCAN", points: [{x: 0.1, y: 0.5}, {x: 0.9, y: 0.5}], type: "avenida_principal", width: 10, color: "#3b82f6" },
            { name: "BLVD. SOLIDARIDAD", points: [{x: 0.15, y: 0.35}, {x: 0.85, y: 0.35}], type: "avenida_principal", width: 8, color: "#3b82f6" },
            { name: "AV. HIDALGO", points: [{x: 0.4, y: 0.1}, {x: 0.4, y: 0.9}], type: "avenida_principal", width: 8, color: "#3b82f6" },
            { name: "AV. MORELOS", points: [{x: 0.6, y: 0.1}, {x: 0.6, y: 0.9}], type: "avenida_principal", width: 8, color: "#3b82f6" },
            { name: "PERIFÉRICO", points: [{x: 0.1, y: 0.2}, {x: 0.3, y: 0.1}, {x: 0.7, y: 0.1}, {x: 0.9, y: 0.2}, {x: 0.9, y: 0.8}, {x: 0.7, y: 0.9}, {x: 0.3, y: 0.9}, {x: 0.1, y: 0.8}, {x: 0.1, y: 0.2}], type: "periferico", width: 9, color: "#1d4ed8" },
            
            // AVENIDAS SECUNDARIAS (Toluca)
            { name: "LÓPEZ MATEOS", points: [{x: 0.3, y: 0.4}, {x: 0.7, y: 0.4}], type: "avenida", width: 6, color: "#60a5fa" },
            { name: "ALLENDE", points: [{x: 0.3, y: 0.6}, {x: 0.7, y: 0.6}], type: "avenida", width: 6, color: "#60a5fa" },
            { name: "JUÁREZ", points: [{x: 0.2, y: 0.4}, {x: 0.2, y: 0.6}], type: "avenida", width: 5, color: "#60a5fa" },
            { name: "ZARAGOZA", points: [{x: 0.8, y: 0.4}, {x: 0.8, y: 0.6}], type: "avenida", width: 5, color: "#60a5fa" },
            { name: "REFORMA", points: [{x: 0.5, y: 0.35}, {x: 0.5, y: 0.65}], type: "avenida", width: 5, color: "#60a5fa" },
            
            // CALLES DE METEPEC
            { name: "AV. ESTADO MÉXICO", points: [{x: 0.25, y: 0.7}, {x: 0.45, y: 0.85}], type: "avenida", width: 6, color: "#60a5fa" },
            { name: "BLVD. METEPEC", points: [{x: 0.3, y: 0.75}, {x: 0.5, y: 0.8}], type: "avenida", width: 6, color: "#60a5fa" },
            { name: "AV. LAS TORRES", points: [{x: 0.35, y: 0.7}, {x: 0.35, y: 0.9}], type: "avenida", width: 5, color: "#60a5fa" },
            { name: "SAN MATEO", points: [{x: 0.4, y: 0.72}, {x: 0.5, y: 0.78}], type: "calle", width: 4, color: "#93c5fd" },
            { name: "SAN JERÓNIMO", points: [{x: 0.42, y: 0.75}, {x: 0.52, y: 0.82}], type: "calle", width: 4, color: "#93c5fd" },
            
            // CALLES RESIDENCIALES (Toluca Norte)
            { name: "GALEANA", points: [{x: 0.45, y: 0.25}, {x: 0.55, y: 0.25}], type: "calle", width: 3, color: "#93c5fd" },
            { name: "GUERRERO", points: [{x: 0.45, y: 0.28}, {x: 0.55, y: 0.28}], type: "calle", width: 3, color: "#93c5fd" },
            { name: "MATAMOROS", points: [{x: 0.45, y: 0.31}, {x: 0.55, y: 0.31}], type: "calle", width: 3, color: "#93c5fd" },
            { name: "INDUSTRIA", points: [{x: 0.48, y: 0.2}, {x: 0.48, y: 0.35}], type: "calle", width: 3, color: "#93c5fd" },
            { name: "COMERCIO", points: [{x: 0.52, y: 0.2}, {x: 0.52, y: 0.35}], type: "calle", width: 3, color: "#93c5fd" },
            
            // CALLES RESIDENCIALES (Toluca Sur)
            { name: "OCAMPO", points: [{x: 0.45, y: 0.65}, {x: 0.55, y: 0.65}], type: "calle", width: 3, color: "#93c5fd" },
            { name: "ALDA", points: [{x: 0.45, y: 0.68}, {x: 0.55, y: 0.68}], type: "calle", width: 3, color: "#93c5fd" },
            { name: "5 DE MAYO", points: [{x: 0.45, y: 0.71}, {x: 0.55, y: 0.71}], type: "calle", width: 3, color: "#93c5fd" },
            { name: "16 DE SEPTIEMBRE", points: [{x: 0.48, y: 0.6}, {x: 0.48, y: 0.75}], type: "calle", width: 3, color: "#93c5fd" },
            { name: "20 DE NOVIEMBRE", points: [{x: 0.52, y: 0.6}, {x: 0.52, y: 0.75}], type: "calle", width: 3, color: "#93c5fd" },
            
            // ZONA OESTE (San Buenaventura)
            { name: "UNIVERSIDAD", points: [{x: 0.65, y: 0.4}, {x: 0.8, y: 0.45}], type: "avenida", width: 5, color: "#60a5fa" },
            { name: "TECNOLÓGICO", points: [{x: 0.7, y: 0.35}, {x: 0.7, y: 0.5}], type: "avenida", width: 5, color: "#60a5fa" },
            { name: "CIENTÍFICA", points: [{x: 0.75, y: 0.38}, {x: 0.75, y: 0.52}], type: "calle", width: 3, color: "#93c5fd" },
            
            // ZONA ESTE (San Mateo)
            { name: "AGRÍCOLA", points: [{x: 0.2, y: 0.25}, {x: 0.35, y: 0.3}], type: "avenida", width: 5, color: "#60a5fa" },
            { name: "INDUSTRIAL", points: [{x: 0.25, y: 0.2}, {x: 0.25, y: 0.35}], type: "avenida", width: 5, color: "#60a5fa" },
            { name: "ARTESANAL", points: [{x: 0.3, y: 0.22}, {x: 0.3, y: 0.33}], type: "calle", width: 3, color: "#93c5fd" }
        ];

        // Línea del Metro
        const lineaMetro = [
            { name: "LÍNEA METRO", points: [{x: 0.4, y: 0.15}, {x: 0.45, y: 0.35}, {x: 0.48, y: 0.4}, {x: 0.5, y: 0.45}, {x: 0.52, y: 0.5}, {x: 0.48, y: 0.55}, {x: 0.45, y: 0.6}, {x: 0.42, y: 0.7}], type: "metro", width: 6, color: "#dc2626" }
        ];

        // Nodos de red
        const nodosRed = [
            { x: 0.5, y: 0.5, name: "SANTA ANA", type: "central", desc: "Central BGITAL" },
            { x: 0.7, y: 0.5, name: "SAN BUENAVENTURA", type: "nodo_principal" },
            { x: 0.4, y: 0.3, name: "SAN MATEO", type: "nodo_principal" },
            { x: 0.6, y: 0.3, name: "LA TERESONA", type: "nodo_principal" },
            { x: 0.4, y: 0.7, name: "SANTIAGUITO", type: "nodo_principal" },
            { x: 0.6, y: 0.7, name: "LA MERCED", type: "nodo_principal" },
            { x: 0.2, y: 0.5, name: "TOLLOCAN", type: "nodo_principal" },
            { x: 0.8, y: 0.5, name: "MORELOS", type: "nodo_principal" },
            { x: 0.3, y: 0.8, name: "METEPEC CENTRO", type: "nodo_secundario" },
            { x: 0.75, y: 0.4, name: "UNIVERSIDAD", type: "nodo_secundario" },
            { x: 0.25, y: 0.25, name: "INDUSTRIAL", type: "nodo_secundario" },
            { x: 0.45, y: 0.25, name: "NORTE", type: "nodo_secundario" },
            { x: 0.55, y: 0.25, name: "NORTE 2", type: "nodo_secundario" },
            { x: 0.45, y: 0.75, name: "SUR", type: "nodo_secundario" },
            { x: 0.55, y: 0.75, name: "SUR 2", type: "nodo_secundario" }
        ];

        // Estaciones del Metro
        const estacionesMetro = [
            { x: 0.4, y: 0.15, name: "TERMINAL", type: "metro" },
            { x: 0.45, y: 0.35, name: "SAN MATEO", type: "metro" },
            { x: 0.48, y: 0.4, name: "CENTRO", type: "metro" },
            { x: 0.5, y: 0.45, name: "SANTA ANA", type: "metro" },
            { x: 0.52, y: 0.5, name: "SAN BUENAVENTURA", type: "metro" },
            { x: 0.48, y: 0.55, name: "LA MERCED", type: "metro" },
            { x: 0.45, y: 0.6, name: "SANTIAGUITO", type: "metro" },
            { x: 0.42, y: 0.7, name: "METEPEC", type: "metro" }
        ];

        // Partículas de fibra óptica
        const particulasFibra = [];

        class ParticulaFibra {
            constructor() {
                this.calle = callesToluca[Math.floor(Math.random() * callesToluca.length)];
                this.progress = Math.random();
                this.speed = 0.004 + Math.random() * 0.006;
                this.size = Math.random() * 2 + 1;
                this.segmentIndex = Math.floor(Math.random() * (this.calle.points.length - 1));
                this.hue = Math.random() * 60 + 200; // Azules
                this.type = Math.random() > 0.7 ? "principal" : "residencial";
            }

            update() {
                this.progress += this.speed;
                if (this.progress >= 1) {
                    this.progress = 0;
                    this.calle = callesToluca[Math.floor(Math.random() * callesToluca.length)];
                    this.segmentIndex = Math.floor(Math.random() * (this.calle.points.length - 1));
                    this.type = Math.random() > 0.7 ? "principal" : "residencial";
                }
            }

            getPosition() {
                const start = this.calle.points[this.segmentIndex];
                const end = this.calle.points[this.segmentIndex + 1];
                return {
                    x: start.x + (end.x - start.x) * this.progress,
                    y: start.y + (end.y - start.y) * this.progress
                };
            }

            draw() {
                const pos = this.getPosition();
                const x = pos.x * canvas.width;
                const y = pos.y * canvas.height;

                // Color según tipo de fibra
                let color;
                if (this.type === "principal") {
                    color = `hsla(${this.hue}, 100%, 65%, 0.9)`;
                } else {
                    color = `hsla(${this.hue + 30}, 100%, 75%, 0.7)`;
                }

                // Partícula de fibra
                ctx.beginPath();
                ctx.arc(x, y, this.size, 0, Math.PI * 2);
                ctx.fillStyle = color;
                ctx.fill();

                // Efecto de luz tecnológico
                const gradient = ctx.createRadialGradient(x, y, 0, x, y, this.size * 4);
                gradient.addColorStop(0, color);
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
                
                ctx.beginPath();
                ctx.arc(x, y, this.size * 4, 0, Math.PI * 2);
                ctx.fillStyle = gradient;
                ctx.fill();
            }
        }

        // Crear partículas (muchas más para efecto denso)
        for (let i = 0; i < 150; i++) {
            particulasFibra.push(new ParticulaFibra());
        }

        // Función para dibujar texto con efecto tecnológico
        function dibujarTextoConSombra(texto, x, y, color = '#ffffff', fontSize = 10, align = 'center') {
            ctx.save();
            ctx.font = `600 ${fontSize}px Inter, Arial, sans-serif`;
            ctx.textAlign = align;
            ctx.textBaseline = 'middle';
            
            // Sombra tecnológica
            ctx.fillStyle = 'rgba(0, 0, 0, 0.6)';
            ctx.fillText(texto, x + 1, y + 1);
            
            // Texto principal
            ctx.fillStyle = color;
            ctx.fillText(texto, x, y);
            
            ctx.restore();
        }

        function dibujarMapa() {
            // Fondo tecnológico oscuro
            const gradient = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
            gradient.addColorStop(0, '#0f172a');
            gradient.addColorStop(0.5, '#1e293b');
            gradient.addColorStop(1, '#0f172a');
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Cuadrícula tecnológica sutil
            ctx.strokeStyle = 'rgba(59, 130, 246, 0.1)';
            ctx.lineWidth = 0.5;
            for (let x = 0; x < canvas.width; x += 40) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, canvas.height);
                ctx.stroke();
            }
            for (let y = 0; y < canvas.height; y += 40) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(canvas.width, y);
                ctx.stroke();
            }

            // Dibujar línea del Metro primero (para que quede detrás)
            lineaMetro.forEach(linea => {
                ctx.beginPath();
                ctx.moveTo(linea.points[0].x * canvas.width, linea.points[0].y * canvas.height);
                
                for (let i = 1; i < linea.points.length; i++) {
                    ctx.lineTo(linea.points[i].x * canvas.width, linea.points[i].y * canvas.height);
                }

                ctx.strokeStyle = linea.color;
                ctx.lineWidth = linea.width;
                ctx.lineCap = 'round';
                ctx.stroke();

                // Efecto de neón para el metro
                ctx.strokeStyle = 'rgba(220, 38, 38, 0.3)';
                ctx.lineWidth = linea.width + 4;
                ctx.stroke();
            });

            // Dibujar calles
            callesToluca.forEach(calle => {
                ctx.beginPath();
                ctx.moveTo(calle.points[0].x * canvas.width, calle.points[0].y * canvas.height);
                
                for (let i = 1; i < calle.points.length; i++) {
                    ctx.lineTo(calle.points[i].x * canvas.width, calle.points[i].y * canvas.height);
                }

                // Estilo según tipo de calle
                ctx.strokeStyle = calle.color;
                ctx.lineWidth = calle.width;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.stroke();

                // Efecto de brillo para avenidas principales
                if (calle.type === "avenida_principal") {
                    ctx.strokeStyle = 'rgba(59, 130, 246, 0.3)';
                    ctx.lineWidth = calle.width + 4;
                    ctx.stroke();
                }

                // Dibujar nombre de la calle
                if (calle.points.length === 2) {
                    const start = calle.points[0];
                    const end = calle.points[1];
                    const midX = (start.x + end.x) / 2 * canvas.width;
                    const midY = (start.y + end.y) / 2 * canvas.height;
                    
                    ctx.save();
                    ctx.translate(midX, midY);
                    
                    // Calcular ángulo de la calle
                    const angle = Math.atan2(
                        end.y * canvas.height - start.y * canvas.height,
                        end.x * canvas.width - start.x * canvas.width
                    );
                    ctx.rotate(angle);
                    
                    // Fondo tecnológico para el texto
                    ctx.fillStyle = 'rgba(15, 23, 42, 0.9)';
                    ctx.fillRect(-ctx.measureText(calle.name).width/2 - 8, -12, 
                                ctx.measureText(calle.name).width + 16, 20);
                    
                    // Borde tecnológico
                    ctx.strokeStyle = 'rgba(59, 130, 246, 0.5)';
                    ctx.lineWidth = 1;
                    ctx.strokeRect(-ctx.measureText(calle.name).width/2 - 8, -12, 
                                  ctx.measureText(calle.name).width + 16, 20);
                    
                    // Texto de la calle
                    dibujarTextoConSombra(calle.name, 0, 0, '#93c5fd', 9);
                    ctx.restore();
                }
            });

            // Dibujar partículas de fibra
            particulasFibra.forEach(particula => {
                particula.update();
                particula.draw();
            });

            // Dibujar nodos de red
            nodosRed.forEach(nodo => {
                const x = nodo.x * canvas.width;
                const y = nodo.y * canvas.height;

                if (nodo.type === "central") {
                    // Nodo central ya tiene marcador especial
                } else if (nodo.type === "nodo_principal") {
                    // Nodos principales
                    ctx.beginPath();
                    ctx.arc(x, y, 8, 0, Math.PI * 2);
                    ctx.fillStyle = '#10b981';
                    ctx.fill();

                    // Efecto de pulso
                    ctx.beginPath();
                    ctx.arc(x, y, 12, 0, Math.PI * 2);
                    ctx.strokeStyle = 'rgba(16, 185, 129, 0.5)';
                    ctx.lineWidth = 2;
                    ctx.stroke();

                    // Nombre del nodo
                    dibujarTextoConSombra(nodo.name, x + 15, y - 12, '#10b981', 8);
                } else {
                    // Nodos secundarios
                    ctx.beginPath();
                    ctx.arc(x, y, 5, 0, Math.PI * 2);
                    ctx.fillStyle = '#8b5cf6';
                    ctx.fill();

                    ctx.beginPath();
                    ctx.arc(x, y, 8, 0, Math.PI * 2);
                    ctx.strokeStyle = 'rgba(139, 92, 246, 0.3)';
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            });

            // Dibujar estaciones del Metro
            estacionesMetro.forEach(estacion => {
                const x = estacion.x * canvas.width;
                const y = estacion.y * canvas.height;

                // Icono de estación
                ctx.beginPath();
                ctx.arc(x, y, 6, 0, Math.PI * 2);
                ctx.fillStyle = '#dc2626';
                ctx.fill();

                // Borde blanco
                ctx.beginPath();
                ctx.arc(x, y, 8, 0, Math.PI * 2);
                ctx.strokeStyle = 'white';
                ctx.lineWidth = 2;
                ctx.stroke();

                // Nombre de estación
                dibujarTextoConSombra(estacion.name, x, y + 15, '#fca5a5', 8);
            });

            // Efectos de conexión entre nodos (fibra óptica)
            ctx.strokeStyle = 'rgba(59, 130, 246, 0.2)';
            ctx.lineWidth = 1;
            ctx.setLineDash([2, 2]);
            
            nodosRed.forEach((nodo, index) => {
                if (nodo.type === "central") {
                    // Conectar nodo central con principales
                    nodosRed.forEach(otherNodo => {
                        if (otherNodo.type === "nodo_principal") {
                            ctx.beginPath();
                            ctx.moveTo(nodo.x * canvas.width, nodo.y * canvas.height);
                            ctx.lineTo(otherNodo.x * canvas.width, otherNodo.y * canvas.height);
                            ctx.stroke();
                        }
                    });
                }
            });
            ctx.setLineDash([]);
        }

        function animar() {
            dibujarMapa();
            requestAnimationFrame(animar);
        }

        animar();

        // ==========================================
        // ANIMACIÓN DE STATS
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
            animateValue("stat1", 0, 127, 2000);
            animateValue("stat2", 0, 428, 2000);
            animateValue("stat3", 0, 2157, 2000);
            animateValue("stat4", 0, 28, 2000);
        }, 1000);

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