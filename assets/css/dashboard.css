/* assets/css/dashboard.css */

:root {
    /* --- COLORES DEL LOGIN (CORPORATIVOS) --- */
    --bg-sidebar: #032a74;       /* Azul Noche Oscuro (Igual al Login) */
    --bg-body: #f9f9fa;          /* Gris muy claro para el contenido */
    --primary: #2563eb;          /* Azul Brillante */
    --primary-dark: #272727;
    --accent: #00a8ff;           /* Cyan */
    --text-white: #ffffff;
    --text-muted: #c7cfda;
    
    /* SOMBRAS Y BORDES */
    --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.247), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --border-radius: 12px;
}

body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', system-ui, sans-serif;
    background-color: var(--bg-body);
}

/* =========================================
   ESTRUCTURA PRINCIPAL (LAYOUT)
   ¡ESTO ARREGLA QUE SE VEA DESORDENADO!
   ========================================= */

.main-layout {
    display: flex;
    min-height: 100vh;
    width: 100%;
    position: relative;
}

/* =========================================
   SIDEBAR (MENÚ LATERAL)
   ========================================= */

.sidebar {
    width: 260px;
    background-color: var(--bg-sidebar);
    height: 100vh; /* Altura completa */
    position: fixed; /* Fijo a la izquierda */
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
    z-index: 1000;
    box-shadow: 4px 0 10px rgba(0, 36, 197, 0.568);
    color: white;
    transition: all 0.3s ease;
}

/* Logo */
.sidebar-brand {
    padding: 20px;
    background: rgba(33, 74, 255, 0.2);
    border-bottom: 1px solid rgba(16, 61, 187, 0.05);
    text-align: center;
}

/* Enlaces del menú */
.nav-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    border-left: 4px solid transparent;
    transition: all 0.2s;
    margin-bottom: 4px;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.05);
    color: white;
    padding-left: 25px; /* Efecto movimiento */
}

.nav-link.active {
    background: linear-gradient(90deg, rgba(37, 99, 235, 0.2) 0%, transparent 100%);
    border-left-color: var(--primary);
    color: white;
}

.nav-link i {
    width: 24px;
    margin-right: 10px;
    text-align: center;
    font-size: 1.1rem;
}

/* Separadores de menú */
.menu-section-label {
    padding: 20px 20px 10px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    color: var(--accent);
    opacity: 0.8;
}

/* Sección de Usuario (Abajo) */
.user-info-container {
    margin-top: auto;
    padding: 15px;
    background: rgba(0, 0, 0, 0.2);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
}

/* =========================================
   CONTENIDO PRINCIPAL
   ========================================= */

.content-area {
    margin-left: 260px; /* Espacio para el sidebar */
    flex: 1;
    padding: 30px;
    width: calc(100% - 260px);
}

/* Banner de Bienvenida */
.welcome-banner {
    background: linear-gradient(135deg, #0979fa 0%, #3b76f6 100%);
    color: white;
    padding: 30px;
    border-radius: var(--border-radius);
    margin-bottom: 30px;
    box-shadow: 0 10px 20px rgba(87, 86, 86, 0.719);
    position: relative;
    overflow: hidden;
}

/* Tarjetas de Estadísticas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-card);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border: 1px solid #b9b8b7;
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
}

.icon-box {
    width: 50px; height: 50px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 15px;
}

.icon-blue { background: #eff6ff; color: var(--primary); }
.icon-green { background: #f0fdf4; color: #16a34a; }
.icon-purple { background: #f5f3ff; color: #7c3aed; }
.icon-orange { background: #fff7ed; color: #ea580c; }

.stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
}

.stat-label {
    font-size: 0.85rem;
    text-transform: uppercase;
    font-weight: 600;
    color: #272727;
    letter-spacing: 0.5px;
}

/* Tablas */
.table-card {
    background: rgb(255, 255, 255);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-card);
    overflow: hidden;
}

.table-header {
    padding: 20px;
    border-bottom: 1.5px solid #117ae2;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table th {
    background: #f8fafc;
    padding: 15px 20px;
    text-align: left;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
}

.modern-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f5f9;
    color: #212221;
}

/* Botones */
.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    border: none;
    transition: 0.2s;
}

.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: var(--primary-dark); }
.btn-secondary { background: white; border: 1px solid #e2e8f0; color: #475569; }
.btn-secondary:hover { background: #f1f5f9; }

/* Badges */
.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
}
.status-activa { background: #dcfce7; color: #166534; }
.status-completada { background: #dbeafe; color: #1e40af; }

/* RESPONSIVE */
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .content-area { margin-left: 0; width: 100%; }
}


/* Flexbox y Alineación */
.d-flex { display: flex; }
.d-none { display: none; }
.flex-column { flex-direction: column; }
.justify-content-between { justify-content: space-between; }
.justify-content-center { justify-content: center; }
.align-items-center { align-items: center; }
.align-items-start { align-items: flex-start; }
.text-center { text-align: center; }
.text-end { text-align: right; }
.w-100 { width: 100%; }

/* Espaciados (Márgenes y Rellenos) */
.m-0 { margin: 0 !important; }
.mb-1 { margin-bottom: 0.25rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 1rem; }
.mb-4 { margin-bottom: 1.5rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 1rem; }
.mt-4 { margin-top: 1.5rem; }

.p-0 { padding: 0 !important; }
.py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
.py-5 { padding-top: 3rem; padding-bottom: 3rem; }
.px-4 { padding-left: 1.5rem; padding-right: 1.5rem; }

/* Tipografía y Colores de Texto */
.text-white { color: white !important; }
.text-primary { color: var(--primary); }
.text-primary-dark { color: var(--primary-dark); }
.text-muted { color: #94a3b8; }
.text-danger { color: #ef4444; }
.text-warning { color: #f59e0b; }
.text-success { color: #10b981; }
.fw-bold { font-weight: 700; }
.small { font-size: 0.875rem; }

/* Arreglo específico para el Banner */
.welcome-banner h1, 
.welcome-banner p {
    color: white !important;
}

/* Arreglo para iconos grandes (FontAwesome) */
.fa-2x { font-size: 2em; }
.fa-3x { font-size: 3em; }

/* Responsivo extra para utilidades */
@media (min-width: 768px) {
    .d-md-block { display: block !important; }
}
/* ============================================
   FIXES DE DISEÑO Y FORMULARIOS (TEMA AZUL)
   ============================================ */

/* Arreglar inputs y selects invisibles */
.form-control, .form-select {
    background-color: #ffffff !important;
    color: #1e293b !important; /* Texto oscuro casi negro */
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 10px 15px;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Títulos y textos con más contraste */
h1, h2, h3, h4, h5, h6 {
    color: #0f172a !important; /* Azul noche muy oscuro */
}

/* Tarjetas más definidas */
.card, .stat-card {
    border: 1px solid #e2e8f0;
    background: #ffffff !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

/* Botones de acción en tabla */
.btn-icon {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-blue { background: #eff6ff; color: #2563eb; }
.btn-blue:hover { background: #2563eb; color: white; }

.btn-green { background: #f0fdf4; color: #16a34a; }
.btn-green:hover { background: #16a34a; color: white; }

.btn-orange { background: #fff7ed; color: #ea580c; }
.btn-orange:hover { background: #ea580c; color: white; }

/* Modal Headers en Azul Corporativo */
.modal-header {
    background: linear-gradient(135deg, #e9e3e3 0%, #dee6ee 100%);
    color: white;
}
.modal-header .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}
.modal-title {
    color: rgb(8, 8, 8) !important;
}
/* --- FIX PARA EL SELECT DEL MODAL (TEXTO INVISIBLE) --- */
.form-select, select.form-control {
    background-color: #fff !important;
    color: #1f2937 !important; /* Gris oscuro */
    font-weight: 500;
}
.form-select option {
    color: #1f2937 !important;
    padding: 10px;
}

/* --- TABLA PROFESIONAL CON SCROLL (Para muchas órdenes) --- */
.table-responsive-scroll {
    max-height: 500px; /* Altura máxima antes de hacer scroll */
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

/* Cabecera fija al hacer scroll */
.table-responsive-scroll thead th {
    position: sticky;
    top: 0;
    background-color: #f8fafc;
    z-index: 1;
    box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
}

/* Cronómetro visual */
.timer-badge {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 1rem;
    padding: 5px 10px;
    border-radius: 6px;
    display: inline-block;
    min-width: 100px;
    text-align: center;
}
.timer-running { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
.timer-paused { background-color: #ffedd5; color: #9a3412; border: 1px solid #fdba74; animation: blink 2s infinite; }

@keyframes blink { 50% { opacity: 0.6; } }

/* ============================================
   FIX URGENTE VISUAL (Pega esto al final)
   ============================================ */

/* 1. Arreglar el Select de Técnicos (Texto Invisible) */
select.form-select, select.form-control, select {
    color: #1a1a1a !important; /* Negro fuerte */
    background-color: #ffffff !important;
    border: 1px solid #ccc !important;
    opacity: 1 !important;
    font-weight: 600 !important;
}
select option {
    color: #000 !important;
    background: #fff !important;
}

/* 2. Arreglar Tabla Desordenada */
.table-responsive-scroll {
    overflow-x: auto;
}
.modern-table th, .modern-table td {
    white-space: nowrap; /* Evita que el texto se aplaste */
    padding: 15px 20px;
    vertical-align: middle;
}
.modern-table th {
    background-color: #f1f5f9;
    color: #475569;
    font-weight: 700;
    letter-spacing: 0.5px;
}

/* 3. Estilo del Cronómetro */
.timer-badge {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    background: #e0e7ff;
    color: #3730a3;
    padding: 5px 10px;
    border-radius: 5px;
    border: 1px solid #c7d2fe;
}
/* --- FIX PARA SELECTS E INPUTS --- */
select.form-select, select.form-control, input.form-control {
    background-color: #ffffff !important;
    color: #1f2937 !important; /* Texto oscuro */
    border: 1px solid #ced4da !important;
    font-weight: 500;
}
select option {
    color: #000000 !important;
    background: #ffffff !important;
    padding: 10px;
}

/* --- CRONÓMETRO VISUAL --- */
.timer-badge {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 1.1rem;
    padding: 8px 12px;
    border-radius: 6px;
    display: inline-block;
    min-width: 110px;
    text-align: center;
    background: #e0e7ff;
    color: #3730a3;
    border: 1px solid #c7d2fe;
}
.timer-running { background-color: #dcfce7; color: #166534; border-color: #86efac; }
.timer-paused { background-color: #ffedd5; color: #9a3412; border-color: #fdba74; animation: blink 2s infinite; }

@keyframes blink { 50% { opacity: 0.6; } }

/* --- TABLA CON SCROLL --- */
.table-responsive-scroll {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
}