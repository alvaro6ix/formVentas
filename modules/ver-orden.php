<?php
// modules/ver-orden.php
require_once '../includes/header.php';
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit();
}

$id_orden = $_GET['id'];
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];

// Consultar los datos de ESTA orden específica
$sql = "SELECT * FROM ventas WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$id_orden]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    echo "<div class='container py-5'><h3>Orden no encontrada</h3></div>";
    require_once '../includes/footer.php';
    exit();
}

// Calcular tiempo para el cronómetro
$inicio_js = strtotime($orden['fecha_asignacion_tecnico']) * 1000;
?>

<div class="container-fluid px-4 py-4">
    <a href="dashboard.php" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Volver al Tablero
    </a>

    <div class="card border-0 shadow-lg">
        <div class="card-header bg-info text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="m-0 text-white"><i class="fas fa-wifi"></i> Instalación: <?php echo $orden['folio']; ?></h4>
                <div class="badge bg-white text-info fs-4" id="timer_tecnico">00:00:00</div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-7">
                    <h5 class="text-primary fw-bold">Datos del Cliente</h5>
                    <ul class="list-group mb-3">
                        <li class="list-group-item"><strong>Titular:</strong> <?php echo $orden['nombre_titular']; ?></li>
                        <li class="list-group-item">
                            <strong>Dirección:</strong> 
                            <?php echo $orden['calle'] .' #'. $orden['numero_exterior'] .', '.$orden['colonia']; ?>
                            <br>
                            <small class="text-muted"><?php echo $orden['delegacion_municipio']; ?></small>
                        </li>
                        <li class="list-group-item"><strong>Plan:</strong> <?php echo $orden['paquete_contratado']; ?></li>
                        <li class="list-group-item">
                            <strong>Teléfono:</strong> 
                            <a href="tel:<?php echo $orden['telefono']; ?>"><?php echo $orden['telefono']; ?></a>
                        </li>
                        <?php if(!empty($orden['referencias'])): ?>
                        <li class="list-group-item"><strong>Referencias:</strong> <?php echo $orden['referencias']; ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="col-md-5 d-flex flex-column gap-2">
                    <h5 class="text-primary fw-bold">Acciones</h5>
                    
                    <a href="http://maps.google.com/?q=<?php echo urlencode($orden['calle'].' '.$orden['colonia'].' '.$orden['delegacion_municipio']); ?>" 
                       target="_blank" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-map-marker-alt"></i> Abrir GPS (Google Maps)
                    </a>
                    
                    <a href="generar-pdf.php?id=<?php echo $orden['id']; ?>" 
                       target="_blank" class="btn btn-outline-danger btn-lg">
                        <i class="fas fa-file-pdf"></i> Ver Contrato de Venta
                    </a>

                    <hr>

                    <button onclick="abrirFinalizar()" class="btn btn-success btn-lg py-3">
                        <i class="fas fa-check-circle"></i> FINALIZAR INSTALACIÓN
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFinalizar" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Reporte de Cierre - <?php echo $orden['folio']; ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                            <div class="col-3"><input type="number" name="cantidad[]" class="form-control" placeholder="Cant" required></div>
                            <div class="col-2"><button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.row').remove()">X</button></div>
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

<script>
// Cronómetro
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

// Funciones del Modal
function abrirFinalizar() { new bootstrap.Modal(document.getElementById('modalFinalizar')).show(); }

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

// Envío del Formulario (Asegúrate que esta ruta API sea correcta)
document.getElementById('formFinalizar').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    
    Swal.fire({
        title: 'Procesando...',
        text: 'Subiendo fotos y generando contrato final.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // AQUÍ ESTÁ LA CLAVE: Ruta a tu API existente
    fetch('../api/tecnicos/finalizar-orden.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire('¡Terminado!', 'Instalación completada y PDF generado.', 'success')
                .then(() => window.location.href = 'dashboard.php');
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Error de conexión con el servidor', 'error');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>