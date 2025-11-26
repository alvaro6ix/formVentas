<?php 
include '../includes/header.php'; 

// Seguridad: Solo Admin
if($_SESSION['rol_id'] != 1) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
?>

<div class="welcome-banner" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); margin-bottom: 25px;">
    <h1 class="text-white mb-1"><i class="fas fa-shield-alt"></i> Auditoría y Seguridad</h1>
    <p class="text-white opacity-75 mb-0">Registro histórico de todas las acciones realizadas en el sistema.</p>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form id="formFiltrosLogs" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Usuario</label>
                <select id="filtro_usuario" class="form-control">
                    <option value="">Todos los usuarios</option>
                    </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Tipo de Acción</label>
                <select id="filtro_accion" class="form-control">
                    <option value="">Todas</option>
                    <option value="LOGIN">Inicio de Sesión</option>
                    <option value="NUEVA_VENTA">Ventas</option>
                    <option value="CONFIGURACION">Configuración/Planes</option>
                    <option value="USUARIOS">Gestión Usuarios</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Fecha</label>
                <input type="date" id="filtro_fecha" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="modern-table w-100">
                <thead>
                    <tr>
                        <th style="width: 150px;">Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Detalles</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody id="tablaLogs">
                    </tbody>
            </table>
        </div>
        <div id="loading" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    cargarUsuariosFiltro();
    cargarLogs();
});

function cargarUsuariosFiltro() {
    // Reutilizamos la API de usuarios o creamos una simple
    fetch('../api/admin/get-usuarios-simple.php')
    .then(res => res.json())
    .then(data => {
        const sel = document.getElementById('filtro_usuario');
        if(data.success) {
            data.usuarios.forEach(u => {
                sel.innerHTML += `<option value="${u.id}">${u.usuario} (${u.rol})</option>`;
            });
        }
    });
}

document.getElementById('formFiltrosLogs').addEventListener('submit', (e) => {
    e.preventDefault();
    cargarLogs();
});

function cargarLogs() {
    const usuario = document.getElementById('filtro_usuario').value;
    const accion = document.getElementById('filtro_accion').value;
    const fecha = document.getElementById('filtro_fecha').value;
    
    document.getElementById('loading').style.display = 'block';
    document.getElementById('tablaLogs').innerHTML = '';

    fetch(`../api/admin/get-logs.php?usuario=${usuario}&accion=${accion}&fecha=${fecha}`)
    .then(res => res.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        const tbody = document.getElementById('tablaLogs');
        
        if(data.logs && data.logs.length > 0) {
            data.logs.forEach(log => {
                let badgeClass = 'badge-info';
                if(log.accion === 'LOGIN') badgeClass = 'badge-success';
                if(log.accion === 'ELIMINAR') badgeClass = 'badge-danger';
                if(log.accion === 'CONFIGURACION') badgeClass = 'badge-warning';

                tbody.innerHTML += `
                    <tr>
                        <td class="text-muted small">${log.fecha_fmt}</td>
                        <td class="fw-bold">
                            <i class="fas fa-user-circle text-muted me-1"></i> ${log.usuario_nombre}
                        </td>
                        <td><span class="badge ${badgeClass}" style="background-color: #e0f2fe; color: #0369a1;">${log.accion}</span></td>
                        <td>${log.detalles}</td>
                        <td class="small text-muted font-monospace">${log.ip_address}</td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No hay registros</td></tr>';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>