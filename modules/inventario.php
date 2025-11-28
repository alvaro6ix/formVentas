<?php 
require_once '../includes/header.php';
require_once '../config/database.php';

// Solo Admin y Despacho pueden gestionar inventario
if(!isset($_SESSION['rol_id']) || !in_array($_SESSION['rol_id'], [1, 3])) {
    header("Location: dashboard.php");
    exit();
}

$db = (new Database())->getConnection();
?>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark fw-bold m-0">
                <i class="fas fa-boxes me-2"></i> Gestión de Inventario
            </h2>
            <p class="text-muted m-0">Control de materiales, movimientos y alertas de stock</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="abrirModalMaterial()" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Nuevo Material
            </button>
            <button onclick="abrirModalMovimiento()" class="btn btn-success">
                <i class="fas fa-exchange-alt me-2"></i> Registrar Movimiento
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">TOTAL MATERIALES</p>
                            <h3 class="fw-bold mb-0" id="stat_total">0</h3>
                        </div>
                        <div class="icon-box icon-blue" style="width: 50px; height: 50px;">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">STOCK CRÍTICO</p>
                            <h3 class="fw-bold mb-0 text-danger" id="stat_critico">0</h3>
                        </div>
                        <div class="icon-box icon-orange" style="width: 50px; height: 50px;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">VALOR INVENTARIO</p>
                            <h3 class="fw-bold mb-0 text-success" id="stat_valor">$0</h3>
                        </div>
                        <div class="icon-box icon-green" style="width: 50px; height: 50px;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted small mb-1 fw-bold">MOVIMIENTOS HOY</p>
                            <h3 class="fw-bold mb-0" id="stat_movimientos">0</h3>
                        </div>
                        <div class="icon-box icon-purple" style="width: 50px; height: 50px;">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Inventario -->
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold"><i class="fas fa-list"></i> Inventario Actual</h5>
                <div class="d-flex gap-2">
                    <select id="filtroEstado" class="form-select form-select-sm" onchange="cargarInventario()">
                        <option value="">Todos</option>
                        <option value="critico">Stock Crítico</option>
                        <option value="bajo">Stock Bajo</option>
                        <option value="normal">Normal</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="modern-table w-100">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th style="width: 100px;" class="text-center">Stock</th>
                            <th style="width: 120px;">Mínimo / Máximo</th>
                            <th style="width: 100px;">Precio Unit.</th>
                            <th style="width: 120px;">Valor Total</th>
                            <th style="width: 150px;">Ubicación</th>
                            <th style="width: 120px;" class="text-center">Estado</th>
                            <th style="width: 150px;" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaInventario">
                    </tbody>
                </table>
            </div>
            <div id="loading" class="text-center py-5" style="display:none;">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Material -->
<div class="modal fade" id="modalMaterial" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-box"></i> Nuevo Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formMaterial">
                    <input type="hidden" name="material_id" id="material_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del Material *</label>
                            <input type="text" name="nombre_material" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Unidad de Medida</label>
                            <select name="unidad_medida" class="form-select">
                                <option value="pza">Pieza (pza)</option>
                                <option value="metros">Metros (m)</option>
                                <option value="kg">Kilogramos (kg)</option>
                                <option value="litros">Litros (L)</option>
                                <option value="caja">Caja</option>
                                <option value="rollo">Rollo</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Precio Unitario</label>
                            <input type="number" step="0.01" name="precio_unitario" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Stock Actual *</label>
                            <input type="number" name="cantidad_disponible" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Stock Mínimo</label>
                            <input type="number" name="stock_minimo" class="form-control" value="10">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Stock Máximo</label>
                            <input type="number" name="stock_maximo" class="form-control" value="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ubicación</label>
                            <input type="text" name="ubicacion" class="form-control" value="Almacén General">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Proveedor</label>
                            <input type="text" name="proveedor" class="form-control">
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Guardar Material
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Movimiento -->
<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Registrar Movimiento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formMovimiento">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Material *</label>
                        <select name="material_id" id="selectMaterial" class="form-select" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Movimiento *</label>
                        <select name="tipo_movimiento" class="form-select" required>
                            <option value="entrada">Entrada (Compra/Devolución)</option>
                            <option value="salida">Salida (Uso/Asignación)</option>
                            <option value="ajuste">Ajuste de Inventario</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cantidad *</label>
                        <input type="number" name="cantidad" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo/Referencia</label>
                        <textarea name="motivo" class="form-control" rows="2" placeholder="Ej: Compra factura #123, Uso en instalación..."></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    cargarEstadisticas();
    cargarInventario();
    cargarMaterialesSelect();
});

function cargarEstadisticas() {
    fetch('../api/inventario/estadisticas.php')
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            document.getElementById('stat_total').textContent = data.stats.total_materiales || 0;
            document.getElementById('stat_critico').textContent = data.stats.stock_critico || 0;
            document.getElementById('stat_valor').textContent = '$' + parseFloat(data.stats.valor_total || 0).toLocaleString('es-MX');
            document.getElementById('stat_movimientos').textContent = data.stats.movimientos_hoy || 0;
        }
    });
}

function cargarInventario() {
    const filtro = document.getElementById('filtroEstado').value;
    document.getElementById('loading').style.display = 'block';
    
    fetch(`../api/inventario/listar.php?filtro=${filtro}`)
    .then(r => r.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        const tbody = document.getElementById('tablaInventario');
        tbody.innerHTML = '';
        
        if(data.success && data.materiales.length > 0) {
            data.materiales.forEach(m => {
                let badgeEstado = 'bg-success';
                let textoEstado = 'Normal';
                
                if(m.estado_stock === 'critico') { badgeEstado = 'bg-danger'; textoEstado = 'Crítico'; }
                if(m.estado_stock === 'bajo') { badgeEstado = 'bg-warning text-dark'; textoEstado = 'Bajo'; }
                
                const valor = parseFloat(m.valor_inventario || 0);
                const precio = parseFloat(m.precio_unitario || 0);
                
                tbody.innerHTML += `
                    <tr>
                        <td class="fw-bold">${m.nombre_material}</td>
                        <td class="text-center fw-bold">${m.cantidad_disponible} <small class="text-muted">${m.unidad_medida}</small></td>
                        <td class="text-muted small">${m.stock_minimo} / ${m.stock_maximo}</td>
                        <td>$${precio.toLocaleString('es-MX')}</td>
                        <td class="fw-bold text-success">$${valor.toLocaleString('es-MX')}</td>
                        <td>${m.ubicacion}</td>
                        <td class="text-center"><span class="badge ${badgeEstado}">${textoEstado}</span></td>
                        <td class="text-end">
                            <button onclick="verHistorial(${m.id})" class="btn btn-sm btn-outline-info" title="Historial">
                                <i class="fas fa-history"></i>
                            </button>
                            <button onclick="editarMaterial(${m.id})" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No hay materiales registrados</td></tr>';
        }
    });
}

function abrirModalMaterial() {
    document.getElementById('formMaterial').reset();
    document.getElementById('material_id').value = '';
    new bootstrap.Modal(document.getElementById('modalMaterial')).show();
}

function abrirModalMovimiento() {
    new bootstrap.Modal(document.getElementById('modalMovimiento')).show();
}

function cargarMaterialesSelect() {
    fetch('../api/inventario/listar.php')
    .then(r => r.json())
    .then(data => {
        const select = document.getElementById('selectMaterial');
        data.materiales.forEach(m => {
            select.innerHTML += `<option value="${m.id}">${m.nombre_material} (Stock: ${m.cantidad_disponible})</option>`;
        });
    });
}

document.getElementById('formMaterial').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    fetch('../api/inventario/guardar-material.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Éxito', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalMaterial')).hide();
            cargarInventario();
            cargarEstadisticas();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});

document.getElementById('formMovimiento').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    fetch('../api/inventario/registrar-movimiento.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Éxito', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('modalMovimiento')).hide();
            cargarInventario();
            cargarEstadisticas();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});

function verHistorial(id) {
    window.location.href = `inventario-historial.php?id=${id}`;
}

function editarMaterial(id) {
    // Implementar edición (similar a clientes)
    alert('Función en desarrollo');
}
</script>

<?php include '../includes/footer.php'; ?>