<?php 
require_once '../includes/header.php';
require_once '../config/database.php';

// 1. PERMISOS: Agregamos el rol 4 (Técnico) para que pueda entrar
if(!isset($_SESSION['rol_id']) || !in_array($_SESSION['rol_id'], [1, 3, 4])) {
    header("Location: dashboard.php");
    exit();
}

$rol_actual = $_SESSION['rol_id'];
$db = (new Database())->getConnection();

// 2. OBTENER TÉCNICOS: Solo necesario si eres Admin(1) o Despacho(3) para el select del modal
$tecnicos = [];
if ($rol_actual != 4) {
    $stmt = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 4 AND activo = 1");
    $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark fw-bold m-0">
                <i class="fas fa-headset me-2"></i> Mesa de Soporte Técnico
            </h2>
            <p class="text-muted m-0">
                <?php echo ($rol_actual == 4) ? 'Mis asignaciones y servicios' : 'Gestión de tickets y asignación'; ?>
            </p>
        </div>
        
        <?php if($rol_actual != 4): ?>
        <button onclick="abrirModalTicket()" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nuevo Ticket de Reporte
        </button>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1 fw-bold">PENDIENTES / ABIERTOS</p>
                    <h3 class="fw-bold mb-0 text-danger" id="stat_abiertos">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1 fw-bold">EN PROCESO</p>
                    <h3 class="fw-bold mb-0 text-warning" id="stat_proceso">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1 fw-bold">RESUELTOS HOY</p>
                    <h3 class="fw-bold mb-0 text-success" id="stat_resueltos">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <p class="text-muted small mb-1 fw-bold">EFICIENCIA</p>
                    <h3 class="fw-bold mb-0 text-primary" id="stat_sla">0%</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body bg-light">
            <form id="formFiltros" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Buscar</label>
                    <input type="text" id="busqueda" class="form-control" placeholder="Ticket, folio, cliente...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Prioridad</label>
                    <select id="prioridad" class="form-select">
                        <option value="">Todas</option>
                        <option value="urgente">Urgente</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Estado</label>
                    <select id="estatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="abierto">Abierto</option>
                        <option value="asignado">Asignado</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="resuelto">Resuelto</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Categoría</label>
                    <select id="categoria" class="form-select">
                        <option value="">Todas</option>
                        <option value="tecnico">Técnico</option>
                        <option value="suspension">Suspensión</option>
                        <option value="reactivacion">Reactivación</option>
                        <option value="cancelacion">Cancelación</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <button type="button" onclick="limpiarFiltros()" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-lg">
        <div class="card-header bg-white py-3">
            <h5 class="m-0 fw-bold"><i class="fas fa-list"></i> Lista de Tickets</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table w-100">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Ticket</th>
                            <th style="width: 100px;">Folio Venta</th>
                            <th style="width: 80px;" class="text-center">Prioridad</th>
                            <th>Cliente / Asunto</th>
                            <th>Dirección</th>
                            <?php if($rol_actual != 4): ?>
                            <th style="width: 150px;">Técnico</th>
                            <?php endif; ?>
                            <th style="width: 100px;">Tiempo</th>
                            <th style="width: 100px;" class="text-center">Estado</th>
                            <th style="width: 120px;" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaTickets">
                    </tbody>
                </table>
            </div>
            <div id="loading" class="text-center py-5" style="display:none;">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTicket" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-ticket-alt"></i> Nuevo Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTicket">
                    <div class="mb-4 p-3 bg-light rounded">
                        <label class="form-label fw-bold"><i class="fas fa-search"></i> Buscar Cliente *</label>
                        <div class="input-group mb-2">
                            <input type="text" id="inputBuscarCliente" class="form-control" placeholder="Nombre o folio...">
                            <button type="button" class="btn btn-primary" onclick="buscarCliente()"><i class="fas fa-search"></i></button>
                        </div>
                        <div id="resultadosBusqueda" style="max-height: 200px; overflow-y: auto;"></div>
                        
                        <input type="hidden" name="venta_id" id="venta_id_seleccionada" required>
                        <input type="hidden" name="cliente_id" id="cliente_id_seleccionada" required>
                        
                        <div id="clienteSeleccionado" style="display:none;" class="mt-3 p-3 border border-success rounded bg-white">
                            <h6 class="text-success"><i class="fas fa-check-circle"></i> Seleccionado:</h6>
                            <div id="infoCliente"></div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Categoría *</label>
                            <select name="categoria" class="form-select" required>
                                <option value="tecnico">Problema Técnico</option>
                                <option value="suspension">Suspensión</option>
                                <option value="reactivacion">Reactivación</option>
                                <option value="cambio_plan">Cambio de Plan</option>
                                <option value="cancelacion">Cancelación</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prioridad *</label>
                            <select name="prioridad" class="form-select" required>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                                <option value="baja">Baja</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asunto *</label>
                        <input type="text" name="asunto" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción *</label>
                        <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" name="requiere_materiales" class="form-check-input" id="checkMateriales">
                        <label class="form-check-label" for="checkMateriales">Requiere materiales</label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAsignar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Asignar Técnico</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAsignar">
                    <input type="hidden" name="ticket_id" id="asignar_ticket_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Técnico *</label>
                        <select name="tecnico_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($tecnicos as $tec): ?>
                            <option value="<?php echo $tec['id']; ?>"><?php echo $tec['nombre_completo']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instrucciones</label>
                        <textarea name="instrucciones" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// PASAMOS EL ROL DE PHP A JS
const ROL_ACTUAL = <?php echo $rol_actual; ?>;

document.addEventListener('DOMContentLoaded', () => {
    cargarEstadisticas();
    cargarTickets();
});

function cargarEstadisticas() {
    fetch('../api/tickets/estadisticas.php')
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            document.getElementById('stat_abiertos').textContent = data.stats.abiertos || 0;
            document.getElementById('stat_proceso').textContent = data.stats.en_proceso || 0;
            document.getElementById('stat_resueltos').textContent = data.stats.resueltos_hoy || 0;
            document.getElementById('stat_sla').textContent = (data.stats.sla_cumplido || 0) + '%';
        }
    })
    .catch(e => console.log('Stats no disponibles'));
}

function cargarTickets() {
    const busqueda = document.getElementById('busqueda').value;
    const prioridad = document.getElementById('prioridad').value;
    const estatus = document.getElementById('estatus').value;
    const categoria = document.getElementById('categoria').value;

    document.getElementById('loading').style.display = 'block';
    
    fetch(`../api/tickets/listar.php?busqueda=${busqueda}&prioridad=${prioridad}&estatus=${estatus}&categoria=${categoria}`)
    .then(r => r.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        const tbody = document.getElementById('tablaTickets');
        tbody.innerHTML = '';
        
        if(data.success && data.tickets.length > 0) {
            data.tickets.forEach(t => {
                // Configuración de colores
                let badgePrioridad = t.prioridad === 'urgente' ? 'bg-danger' : (t.prioridad === 'alta' ? 'bg-warning text-dark' : 'bg-info');
                let badgeEstatus = 'bg-secondary';
                if(t.estatus === 'abierto') badgeEstatus = 'bg-danger';
                if(t.estatus === 'asignado') badgeEstatus = 'bg-warning text-dark';
                if(t.estatus === 'en_proceso') badgeEstatus = 'bg-primary';
                if(t.estatus === 'resuelto') badgeEstatus = 'bg-success';
                
                // 4. LÓGICA DE BOTONES: Solo mostrar Asignar si NO es técnico
                let botones = '';
                if(ROL_ACTUAL != 4 && (t.estatus === 'abierto' || t.estatus === 'asignado')) {
                    botones += `<button onclick="asignarTicket(${t.id})" class="btn btn-sm btn-success me-1" title="Asignar"><i class="fas fa-user-plus"></i></button>`;
                }
                botones += `<button onclick="verDetalleTicket(${t.id})" class="btn btn-sm btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></button>`;

                // Columna técnico (solo si no eres técnico)
                let columnaTecnico = '';
                if(ROL_ACTUAL != 4) {
                    columnaTecnico = `<td>${t.tecnico_asignado || '<span class="text-muted small">--</span>'}</td>`;
                }

                tbody.innerHTML += `
                    <tr>
                        <td class="fw-bold text-primary">${t.numero_ticket}</td>
                        <td class="fw-bold text-secondary">${t.folio_venta || ''}</td>
                        <td class="text-center"><span class="badge ${badgePrioridad}">${t.prioridad}</span></td>
                        <td>
                            <div class="fw-bold text-truncate" style="max-width: 200px;">${t.cliente || 'Desconocido'}</div>
                            <small class="text-muted">${t.asunto}</small>
                        </td>
                        <td><small>${t.colonia || ''}</small></td>
                        ${columnaTecnico}
                        <td><small>${t.horas_abierto || 0}h</small></td>
                        <td class="text-center"><span class="badge ${badgeEstatus}">${t.estatus.toUpperCase()}</span></td>
                        <td class="text-end">${botones}</td>
                    </tr>
                `;
            });
        } else {
            let colspan = (ROL_ACTUAL != 4) ? 9 : 8;
            tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center py-4 text-muted">No hay tickets encontrados</td></tr>`;
        }
    })
    .catch(e => {
        document.getElementById('loading').style.display = 'none';
        console.error(e);
    });
}

// Filtros
document.getElementById('formFiltros').addEventListener('submit', (e) => { e.preventDefault(); cargarTickets(); });
function limpiarFiltros() {
    document.getElementById('busqueda').value = '';
    document.getElementById('prioridad').value = '';
    document.getElementById('estatus').value = 'abierto'; // Reset a abierto o vacío
    document.getElementById('categoria').value = '';
    cargarTickets();
}

// Modal Crear
function abrirModalTicket() {
    document.getElementById('formTicket').reset();
    document.getElementById('clienteSeleccionado').style.display = 'none';
    document.getElementById('resultadosBusqueda').innerHTML = '';
    new bootstrap.Modal(document.getElementById('modalTicket')).show();
}

function buscarCliente() {
    const busqueda = document.getElementById('inputBuscarCliente').value;
    if(busqueda.length < 3) { Swal.fire('Oops', 'Escribe al menos 3 letras', 'warning'); return; }
    
    fetch(`../api/tickets/buscar-cliente-venta.php?busqueda=${encodeURIComponent(busqueda)}`)
    .then(r => r.json())
    .then(data => {
        const div = document.getElementById('resultadosBusqueda');
        div.innerHTML = '';
        if(data.success && data.resultados.length > 0) {
            div.innerHTML = '<div class="list-group mt-2">';
            data.resultados.forEach(v => {
                div.innerHTML += `
                    <button type="button" class="list-group-item list-group-item-action" onclick='seleccionarCliente(${JSON.stringify(v)})'>
                        <strong>${v.nombre_titular}</strong> (${v.folio}) <br> <small>${v.colonia}</small>
                    </button>`;
            });
            div.innerHTML += '</div>';
        } else {
            div.innerHTML = '<div class="alert alert-warning mt-2">No encontrado</div>';
        }
    });
}

function seleccionarCliente(v) {
    document.getElementById('venta_id_seleccionada').value = v.id;
    document.getElementById('cliente_id_seleccionada').value = v.id;
    document.getElementById('clienteSeleccionado').style.display = 'block';
    document.getElementById('infoCliente').innerHTML = `<strong>${v.nombre_titular}</strong> - ${v.paquete_contratado}`;
    document.getElementById('resultadosBusqueda').innerHTML = '';
}

document.getElementById('formTicket').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    if(!formData.get('venta_id')) { Swal.fire('Error', 'Selecciona un cliente', 'error'); return; }

    fetch('../api/tickets/crear.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Éxito', 'Ticket creado', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalTicket')).hide();
            cargarTickets();
            cargarEstadisticas();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});

// Modal Asignar
function asignarTicket(id) {
    document.getElementById('asignar_ticket_id').value = id;
    new bootstrap.Modal(document.getElementById('modalAsignar')).show();
}

document.getElementById('formAsignar').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    fetch('../api/tickets/asignar.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Asignado', 'Técnico notificado', 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalAsignar')).hide();
            cargarTickets();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});

function verDetalleTicket(id) {
    window.location.href = `ticket-detalle.php?id=${id}`;
}
</script>

<?php include '../includes/footer.php'; ?>