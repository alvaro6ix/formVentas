<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="welcome-banner">
    <div style="position: relative; z-index: 2;">
        <h1 class="text-white mb-2">Panel de Control General</h1>
        <p class="text-white opacity-75 mb-0">Bienvenido, <?php echo $_SESSION['nombre_completo'] ?? 'Administrador'; ?>. Aquí tienes el resumen operativo de BGITAL.</p>
    </div>
    <i class="fas fa-chart-line" style="position: absolute; right: 20px; bottom: -20px; font-size: 10rem; opacity: 0.1; color: white;"></i>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-label text-muted">Ventas Hoy</div>
                <div class="stat-value text-primary-dark" id="kpi_ventas_hoy">0</div>
            </div>
            <div class="icon-box icon-blue"><i class="fas fa-shopping-cart"></i></div>
        </div>
        <div class="mt-2 text-success small"><i class="fas fa-arrow-up"></i> <span id="txt_hoy_vs_ayer">Analizando...</span></div>
    </div>

    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='ver-ventas.php'">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-label text-muted">Ventas del Mes</div>
                <div class="stat-value text-primary-dark" id="kpi_ventas_mes">0</div>
            </div>
            <div class="icon-box icon-green"><i class="fas fa-calendar-check"></i></div>
        </div>
        <div class="mt-2 text-muted small">Acumulado mensual</div>
    </div>

    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-label text-muted">Pendientes Instalación</div>
                <div class="stat-value text-primary-dark" id="kpi_pendientes">0</div>
            </div>
            <div class="icon-box icon-orange"><i class="fas fa-tools"></i></div>
        </div>
        <div class="mt-2 text-warning small">Requieren atención</div>
    </div>

    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='admin/gestionar-usuarios.php'">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-label text-muted">Gestión Usuarios</div>
                <div class="stat-value text-primary-dark">Admin</div>
            </div>
            <div class="icon-box icon-purple"><i class="fas fa-users-cog"></i></div>
        </div>
        <div class="mt-2 text-primary small">Click para gestionar</div>
    </div>
</div>

<div class="row" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
    <div class="col-lg-8" style="flex: 2; min-width: 300px;">
        <div class="card h-100">
            <div class="card-header bg-white border-0 py-3">
                <h4 class="m-0"><i class="fas fa-chart-area text-primary me-2"></i>Tendencia de Ventas (Últimos 7 días)</h4>
            </div>
            <div class="card-body">
                <canvas id="chartTendencia" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4" style="flex: 1; min-width: 300px;">
        <div class="card h-100">
            <div class="card-header bg-white border-0 py-3">
                <h4 class="m-0"><i class="fas fa-chart-pie text-accent me-2"></i>Paquetes Más Vendidos</h4>
            </div>
            <div class="card-body">
                <canvas id="chartPaquetes" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h4 class="m-0"><i class="fas fa-filter text-success me-2"></i>Embudo de Conversión (Flujo Operativo)</h4>
    </div>
    <div class="card-body">
        <div style="position: relative; height: 100px; width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 0 50px;">
            <div style="position: absolute; top: 50%; left: 50px; right: 50px; height: 4px; background: #e2e8f0; z-index: 1;"></div>
            
            <div class="text-center position-relative" style="z-index: 2;">
                <div class="icon-box icon-blue mx-auto mb-2 rounded-circle" style="width: 60px; height: 60px; font-size: 1.2rem;">
                    <i class="fas fa-file-signature"></i>
                </div>
                <h5 class="mb-0" id="funnel_registradas">0</h5>
                <small class="text-muted">Ventas</small>
            </div>

            <div class="text-center position-relative" style="z-index: 2;">
                <div class="icon-box icon-purple mx-auto mb-2 rounded-circle" style="width: 60px; height: 60px; font-size: 1.2rem;">
                    <i class="fas fa-boxes"></i>
                </div>
                <h5 class="mb-0" id="funnel_despachadas">0</h5>
                <small class="text-muted">Despachadas</small>
            </div>

            <div class="text-center position-relative" style="z-index: 2;">
                <div class="icon-box icon-orange mx-auto mb-2 rounded-circle" style="width: 60px; height: 60px; font-size: 1.2rem;">
                    <i class="fas fa-hard-hat"></i>
                </div>
                <h5 class="mb-0" id="funnel_tecnico">0</h5>
                <small class="text-muted">En Técnico</small>
            </div>

            <div class="text-center position-relative" style="z-index: 2;">
                <div class="icon-box icon-green mx-auto mb-2 rounded-circle" style="width: 60px; height: 60px; font-size: 1.2rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="mb-0" id="funnel_completadas">0</h5>
                <small class="text-muted">Completadas</small>
            </div>
        </div>
        
        <div class="mt-4 px-4">
             <div class="progress" style="height: 20px; border-radius: 10px; background-color: #f1f5f9; overflow: hidden;">
                <div id="progressBarFunnel" class="progress-bar bg-success" role="progressbar" style="width: 0%; transition: width 1s ease;"></div>
             </div>
             <p class="text-center mt-2 small text-muted">Tasa de Finalización Global</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosDashboard();
});

function cargarDatosDashboard() {
    fetch('../api/admin/dashboard-stats.php')
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            actualizarKPIs(data.kpis);
            renderizarGraficaTendencia(data.tendencia);
            renderizarGraficaPaquetes(data.paquetes);
            actualizarEmbudo(data.funnel);
        } else {
            console.error('Error al cargar datos:', data.message);
        }
    })
    .catch(error => console.error('Error de red:', error));
}

function actualizarKPIs(kpis) {
    // Animación simple de conteo
    animarNumero('kpi_ventas_hoy', kpis.ventas_hoy);
    animarNumero('kpi_ventas_mes', kpis.ventas_mes);
    animarNumero('kpi_pendientes', kpis.instalaciones_pendientes);
    
    document.getElementById('txt_hoy_vs_ayer').innerText = 'Datos en tiempo real';
}

function actualizarEmbudo(funnel) {
    animarNumero('funnel_registradas', funnel.registradas);
    animarNumero('funnel_despachadas', funnel.despachadas);
    animarNumero('funnel_tecnico', funnel.en_tecnico);
    animarNumero('funnel_completadas', funnel.completadas);

    // Calcular porcentaje de eficiencia (Completadas vs Registradas)
    let porcentaje = 0;
    if(funnel.registradas > 0) {
        porcentaje = Math.round((funnel.completadas / funnel.registradas) * 100);
    }
    document.getElementById('progressBarFunnel').style.width = porcentaje + '%';
    document.getElementById('progressBarFunnel').innerText = porcentaje + '%';
}

// Configuración Global de Estilos para ChartJS
Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
Chart.defaults.color = '#64748b';

function renderizarGraficaTendencia(datos) {
    const ctx = document.getElementById('chartTendencia').getContext('2d');
    
    const etiquetas = datos.map(d => d.fecha);
    const valores = datos.map(d => d.cantidad);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Ventas Diarias',
                data: valores,
                borderColor: '#2563eb', // Var primary
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 2,
                tension: 0.4, // Curva suave
                fill: true,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563eb',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [2, 4] }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

function renderizarGraficaPaquetes(datos) {
    const ctx = document.getElementById('chartPaquetes').getContext('2d');
    
    const etiquetas = datos.map(d => d.nombre);
    const valores = datos.map(d => d.cantidad);
    
    // Paleta de colores basada en tu CSS
    const colores = ['#2563eb', '#00a8ff', '#0A0E27', '#10b981', '#f59e0b'];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: etiquetas,
            datasets: [{
                data: valores,
                backgroundColor: colores,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 20 }
                }
            },
            cutout: '70%' // Hace el anillo más delgado
        }
    });
}

function animarNumero(id, valorFinal) {
    const elemento = document.getElementById(id);
    let valorActual = 0;
    const incremento = Math.ceil(valorFinal / 20); // Velocidad
    
    if(valorFinal === 0) {
        elemento.innerText = 0;
        return;
    }

    const intervalo = setInterval(() => {
        valorActual += incremento;
        if (valorActual >= valorFinal) {
            valorActual = valorFinal;
            clearInterval(intervalo);
        }
        elemento.innerText = valorActual;
    }, 40);
}
</script>