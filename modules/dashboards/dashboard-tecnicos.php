<?php
require_once __DIR__ . '/../../config/database.php';
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];

// Orden Activa (Asignada a mí y NO completada)
$orden = $db->query("SELECT * FROM ventas WHERE asignado_tecnico = $uid AND estatus = 'activa' ORDER BY fecha_asignacion_tecnico DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Pendientes (Ya no hay 'pendientes' de iniciar, en cuanto te asignan, ya la tienes activa)
// Si quieres ver historial, haríamos otra query.
?>

<div class="container-fluid px-4 py-4">
    <div class="welcome-banner mb-4">
        <h1>Zona Técnica: <?php echo $_SESSION['nombre']; ?></h1>
    </div>

    <?php if($orden): 
        // El cronómetro inicia desde que Despacho dio click (fecha_asignacion_tecnico)
        $inicio_js = strtotime($orden['fecha_asignacion_tecnico']) * 1000;
    ?>
    
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="m-0"><i class="fas fa-tools"></i> Orden en Curso: <?php echo $orden['folio']; ?></h4>
                <div class="badge bg-white text-primary fs-4" id="timer_tecnico">00:00:00</div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-7">
                    <h5 class="text-primary fw-bold">Datos del Cliente</h5>
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><strong>Nombre:</strong> <?php echo $orden['nombre_titular']; ?></li>
                        <li class="list-group-item"><strong>Dirección:</strong> <?php echo $orden['calle'] .' #'. $orden['numero_exterior'] .', '.$orden['colonia']; ?></li>
                        <li class="list-group-item"><strong>Plan:</strong> <?php echo $orden['paquete_contratado']; ?></li>
                        <li class="list-group-item">
                            <strong>Contacto:</strong> 
                            <a href="tel:<?php echo $orden['telefono']; ?>"><?php echo $orden['telefono']; ?></a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-5 d-flex flex-column gap-2">
                    <h5 class="text-primary fw-bold">Acciones</h5>
                    
                    <a href="http://maps.google.com/?q=<?php echo urlencode($orden['calle'].' '.$orden['colonia'].' '.$orden['delegacion_municipio']); ?>" 
                       target="_blank" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-map-marker-alt"></i> Abrir GPS
                    </a>
                    
                    <a href="../modules/generar-pdf.php?id=<?php echo $orden['id']; ?>" 
                       target="_blank" class="btn btn-outline-danger btn-lg">
                        <i class="fas fa-file-pdf"></i> Ver Contrato PDF
                    </a>

                    <hr>

                    <button onclick="abrirFinalizar()" class="btn btn-success btn-lg py-3">
                        <i class="fas fa-check-circle"></i> FINALIZAR INSTALACIÓN
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <div class="text-center py-5 mt-5">
        <div class="icon-box icon-green mx-auto mb-3" style="width: 100px; height: 100px; font-size: 3rem;">
            <i class="fas fa-coffee"></i>
        </div>
        <h2 class="text-muted">Sin órdenes asignadas</h2>
        <p>Espera a que Despacho te asigne una nueva ruta.</p>
        <button onclick="location.reload()" class="btn btn-primary mt-3"><i class="fas fa-sync"></i> Actualizar</button>
    </div>
    <?php endif; ?>
</div>

<?php if($orden): ?>
<div class="modal fade" id="modalFinalizar" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Reporte de Cierre - <?php echo $orden['folio']; ?></h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModal()"></button>
            </div>
            <div class="modal-body">
                <form id="formFinalizar" enctype="multipart/form-data">
                    <input type="hidden" name="venta_id" value="<?php echo $orden['id']; ?>">
                    
                    <h6 class="fw-bold text-success mb-3 border-bottom pb-2">1. Material Utilizado</h6>
                    <div id="listaMateriales">
                        <div class="row mb-2 material-row">
                            <div class="col-7">
                                <select name="material[]" class="form-control form-select">
                                    <option value="Cable Fibra (mts)">Cable Fibra (mts)</option>
                                    <option value="Tensores">Tensores</option>
                                    <option value="Conectores">Conectores</option>
                                    <option value="Grapas">Grapas</option>
                                    <option value="Roseta">Roseta</option>
                                    <option value="Patch Cord">Patch Cord</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <input type="number" name="cantidad[]" class="form-control" placeholder="Cant" required>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.row').remove()">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addMaterial()">+ Agregar otro material</button>

                    <h6 class="fw-bold text-success mt-4 mb-3 border-bottom pb-2">2. Evidencia Fotográfica</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Selecciona fotos de la instalación, módem y potencia:</label>
                        <input type="file" name="evidencias[]" class="form-control" multiple accept="image/*" required>
                    </div>

                    <h6 class="fw-bold text-success mt-4 mb-3 border-bottom pb-2">3. Comentarios Finales</h6>
                    <textarea name="notas_finales" class="form-control" rows="3" placeholder="Observaciones importantes..."></textarea>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success btn-lg py-3 fw-bold">
                            CONFIRMAR Y CERRAR ORDEN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// 1. CRONÓMETRO TÉCNICO
<?php if($orden): ?>
    setInterval(() => {
        const start = <?php echo $inicio_js; ?>;
        const now = Date.now();
        const diff = Math.floor((now - start) / 1000);
        
        const h = Math.floor(diff / 3600).toString().padStart(2,'0');
        const m = Math.floor((diff % 3600) / 60).toString().padStart(2,'0');
        const s = Math.floor(diff % 60).toString().padStart(2,'0');
        
        const el = document.getElementById('timer_tecnico');
        if(el) el.innerText = `${h}:${m}:${s}`;
    }, 1000);
<?php endif; ?>

// 2. FUNCIONES DEL MODAL
function abrirFinalizar() {
    new bootstrap.Modal(document.getElementById('modalFinalizar')).show();
}

function cerrarModal() {
    const el = document.getElementById('modalFinalizar');
    const modal = bootstrap.Modal.getInstance(el);
    if(modal) modal.hide();
}

function addMaterial() {
    const html = `
        <div class="row mb-2 material-row">
            <div class="col-7">
                <select name="material[]" class="form-control form-select">
                    <option value="Cable Fibra (mts)">Cable Fibra (mts)</option>
                    <option value="Tensores">Tensores</option>
                    <option value="Conectores">Conectores</option>
                    <option value="Grapas">Grapas</option>
                    <option value="Roseta">Roseta</option>
                    <option value="Patch Cord">Patch Cord</option>
                </select>
            </div>
            <div class="col-3"><input type="number" name="cantidad[]" class="form-control" placeholder="Cant" required></div>
            <div class="col-2"><button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.row').remove()">X</button></div>
        </div>`;
    document.getElementById('listaMateriales').insertAdjacentHTML('beforeend', html);
}

// 3. ENVÍO DEL FORMULARIO
document.getElementById('formFinalizar')?.addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    
    Swal.fire({
        title: 'Enviando Reporte...',
        text: 'Subiendo fotos y cerrando orden. Espere un momento.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('../api/tecnicos/finalizar-orden.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire('¡Excelente!', 'Trabajo finalizado correctamente', 'success')
                .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error de red', 'No se pudo conectar con el servidor', 'error');
    });
});
</script>