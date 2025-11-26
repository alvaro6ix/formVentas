<?php include '../includes/header.php'; ?>

<div class="welcome-banner" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 20px; border-radius: 12px; margin-bottom: 25px;">
    <h1 class="text-white mb-1" style="font-size: 1.8rem;">Configuración Global</h1>
    <p class="text-white opacity-75 mb-0">Gestiona la identidad de la empresa y los planes comerciales.</p>
</div>

<div class="row" style="display: flex; gap: 20px; flex-wrap: wrap;">
    
    <div style="flex: 1; min-width: 350px;">
        <div class="card">
            <div class="card-header bg-white border-bottom py-3">
                <h4 class="m-0 text-primary-dark"><i class="fas fa-building me-2"></i> Identidad Corporativa (PDF)</h4>
            </div>
            <div class="card-body">
                <form id="formEmpresa" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <p class="small text-muted mb-2">Logo Actual (Aparece en PDF)</p>
                        <div style="width: 150px; height: 150px; margin: 0 auto; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px; background: #f8fafc;">
                            <img id="previewLogo" src="../assets/img-logo/bgital_logo_moderno.png" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                        <div class="mt-3">
                            <label class="btn btn-secondary btn-sm" style="cursor: pointer;">
                                <i class="fas fa-upload me-1"></i> Cambiar Logo
                                <input type="file" name="logo" id="inputLogo" accept="image/*" style="display: none;" onchange="previewImage(this)">
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label small">Nombre de la Empresa</label>
                        <input type="text" name="nombre_empresa" id="nombre_empresa" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label small">Dirección Fiscal / Física</label>
                        <textarea name="direccion" id="direccion" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="row" style="display: flex; gap: 10px;">
                        <div class="col" style="flex: 1;">
                            <div class="form-group mb-3">
                                <label class="form-label small">Teléfono Contacto</label>
                                <input type="text" name="telefono_contacto" id="telefono_contacto" class="form-control">
                            </div>
                        </div>
                        <div class="col" style="flex: 1;">
                            <div class="form-group mb-3">
                                <label class="form-label small">Email Contacto</label>
                                <input type="text" name="email_contacto" id="email_contacto" class="form-control">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-2">
                        <i class="fas fa-save me-2"></i> Guardar Configuración
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div style="flex: 1.5; min-width: 400px;">
        <div class="card">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h4 class="m-0 text-primary-dark"><i class="fas fa-wifi me-2"></i> Planes de Internet</h4>
                <button class="btn btn-success btn-sm" onclick="abrirModalPlan()">
                    <i class="fas fa-plus"></i> Nuevo Plan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="modern-table w-100">
                        <thead>
                            <tr>
                                <th>Nombre del Plan</th>
                                <th>Velocidad</th>
                                <th>Precio</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPlanes">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalPlan" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background: white; margin: 10% auto; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; position: relative; animation: slideDown 0.3s;">
        <span onclick="cerrarModalPlan()" style="position: absolute; right: 20px; top: 15px; font-size: 24px; cursor: pointer;">&times;</span>
        
        <h3 id="modalTitulo" class="mb-4 text-primary-dark">Nuevo Plan</h3>
        
        <form id="formPlan">
            <input type="hidden" name="id" id="plan_id">
            
            <div class="form-group mb-3">
                <label class="form-label">Nombre Comercial</label>
                <input type="text" name="nombre_plan" id="nombre_plan" class="form-control" placeholder="Ej: Fibra 50MB" required>
            </div>

            <div class="row" style="display: flex; gap: 15px;">
                <div class="form-group mb-3" style="flex: 1;">
                    <label class="form-label">Velocidad (MB)</label>
                    <input type="number" name="velocidad_mb" id="velocidad_mb" class="form-control" required>
                </div>
                <div class="form-group mb-3" style="flex: 1;">
                    <label class="form-label">Precio ($)</label>
                    <input type="number" step="0.01" name="precio" id="precio" class="form-control" required>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Estado</label>
                <select name="activo" id="activo" class="form-control">
                    <option value="1">Activo (Visible en ventas)</option>
                    <option value="0">Inactivo (Oculto)</option>
                </select>
            </div>

            <div class="mt-4 text-end">
                <button type="button" class="btn btn-secondary me-2" onclick="cerrarModalPlan()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Plan</button>
            </div>
        </form>
    </div>
</div>

<script>
// 1. CARGA INICIAL
document.addEventListener('DOMContentLoaded', () => {
    cargarConfiguracion();
    cargarPlanes();
});

// 2. FUNCIONES DE EMPRESA
function cargarConfiguracion() {
    fetch('../api/admin/get-config-data.php')
    .then(res => res.json())
    .then(data => {
        if(data.success && data.empresa) {
            const e = data.empresa;
            document.getElementById('nombre_empresa').value = e.nombre_empresa;
            document.getElementById('direccion').value = e.direccion;
            document.getElementById('telefono_contacto').value = e.telefono_contacto;
            document.getElementById('email_contacto').value = e.email_contacto;
            
            if(e.logo_path) {
                // Ajustamos la ruta para que se vea desde modules/
                document.getElementById('previewLogo').src = '../' + e.logo_path + '?t=' + new Date().getTime(); 
            }
        }
    });
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewLogo').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('formEmpresa').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    Swal.fire({title: 'Guardando...', didOpen: () => Swal.showLoading()});

    fetch('../api/admin/save-empresa.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Guardado', 'Datos de empresa actualizados', 'success');
            // Actualizar el logo del header si cambió (opcional, requiere recargar)
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});

// 3. FUNCIONES DE PLANES
function cargarPlanes() {
    fetch('../api/admin/get-config-data.php') // Reusamos el endpoint o creamos uno solo
    .then(res => res.json())
    .then(data => {
        const tbody = document.getElementById('tablaPlanes');
        tbody.innerHTML = '';
        
        if(data.planes) {
            data.planes.forEach(plan => {
                const estado = plan.activo == 1 
                    ? '<span class="badge badge-success">Activo</span>' 
                    : '<span class="badge badge-danger">Inactivo</span>';
                
                tbody.innerHTML += `
                    <tr>
                        <td class="fw-bold">${plan.nombre_plan}</td>
                        <td>${plan.velocidad_mb} MB</td>
                        <td>$${plan.precio}</td>
                        <td class="text-center">${estado}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-secondary" onclick='editarPlan(${JSON.stringify(plan)})'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarPlan(${plan.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
    });
}

// LOGICA MODAL PLANES
const modal = document.getElementById('modalPlan');

function abrirModalPlan() {
    document.getElementById('formPlan').reset();
    document.getElementById('plan_id').value = '';
    document.getElementById('modalTitulo').innerText = 'Nuevo Plan';
    modal.style.display = 'block';
}

function cerrarModalPlan() {
    modal.style.display = 'none';
}

function editarPlan(plan) {
    document.getElementById('plan_id').value = plan.id;
    document.getElementById('nombre_plan').value = plan.nombre_plan;
    document.getElementById('velocidad_mb').value = plan.velocidad_mb;
    document.getElementById('precio').value = plan.precio;
    document.getElementById('activo').value = plan.activo;
    document.getElementById('modalTitulo').innerText = 'Editar Plan';
    modal.style.display = 'block';
}

document.getElementById('formPlan').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('../api/admin/save-plan.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Éxito', 'Plan guardado correctamente', 'success');
            cerrarModalPlan();
            cargarPlanes();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});

function eliminarPlan(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Al eliminar un plan, desaparecerá de las opciones de venta nueva.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/admin/delete-plan.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: id})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Eliminado', 'El plan ha sido eliminado', 'success');
                    cargarPlanes();
                } else {
                    Swal.fire('Error', 'No se pudo eliminar', 'error');
                }
            });
        }
    })
}

// Cerrar modal al hacer click fuera
window.onclick = function(event) {
    if (event.target == modal) cerrarModalPlan();
}
</script>

<?php include '../includes/footer.php'; ?>