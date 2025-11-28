<?php
// modules/ticket-detalle.php
require_once '../includes/header.php';
require_once '../config/database.php';

// 0. Validar Sesión y ID
if (!isset($_SESSION['rol_id'])) { header("Location: dashboard.php"); exit(); }
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { echo "<script>window.location.href='dashboard.php';</script>"; exit(); }

$ticket_id = $_GET['id'];
$db = (new Database())->getConnection();
$rol_actual = $_SESSION['rol_id'];
$user_id_actual = $_SESSION['user_id'];

// 1. Obtener información del Ticket y del Cliente
$sql = "SELECT 
            t.*, 
            v.nombre_titular, v.telefono, v.calle, v.colonia, v.numero_exterior, v.delegacion_municipio, v.paquete_contratado,
            u.nombre_completo as tecnico_nombre
        FROM tickets_soporte t
        INNER JOIN ventas v ON t.venta_id = v.id
        LEFT JOIN usuarios u ON t.asignado_a = u.id
        WHERE t.id = ?";

$stmt = $db->prepare($sql);
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    echo "<div class='container py-5 text-center'><h3>Ticket no encontrado</h3><a href='dashboard.php' class='btn btn-primary'>Volver</a></div>";
    require_once '../includes/footer.php';
    exit();
}

// --- LÓGICA DE PERMISOS ---
// El técnico solo puede gestionar si es SU ticket. Admin y Despacho solo miran.
$puede_gestionar = ($rol_actual == 4 && $ticket['asignado_a'] == $user_id_actual);

// Colores visuales
$estadoColor = 'secondary';
$estadoTexto = strtoupper(str_replace('_', ' ', $ticket['estatus']));
switch($ticket['estatus']) {
    case 'abierto': $estadoColor = 'danger'; break;
    case 'asignado': $estadoColor = 'warning text-dark'; break;
    case 'en_proceso': $estadoColor = 'primary'; $estadoTexto = 'EN PROCESO'; break;
    case 'resuelto': $estadoColor = 'success'; break;
}
?>

<div class="container-fluid px-4 py-4">
    <?php $rutaVolver = ($rol_actual == 4) ? 'dashboard.php' : 'tickets.php'; ?>
    <a href="<?php echo $rutaVolver; ?>" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Volver
    </a>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-primary">
                        <i class="fas fa-ticket-alt me-2"></i> Ticket #<?php echo $ticket['numero_ticket']; ?>
                    </h5>
                    <span class="badge bg-<?php echo $estadoColor; ?> fs-6 rounded-pill">
                        <?php echo $estadoTexto; ?>
                    </span>
                </div>
                <div class="card-body">
                    <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($ticket['asunto']); ?></h4>
                    <div class="alert alert-light border border-start border-4 border-primary">
                        <small class="text-muted text-uppercase fw-bold">Descripción:</small>
                        <p class="mb-0 fs-5"><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></p>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="text-secondary fw-bold">Cliente</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-user text-primary me-2"></i> <?php echo $ticket['nombre_titular']; ?></li>
                                <li class="mb-2"><i class="fas fa-phone text-success me-2"></i> <a href="tel:<?php echo $ticket['telefono']; ?>"><?php echo $ticket['telefono']; ?></a></li>
                                <li class="mb-2"><i class="fas fa-wifi text-info me-2"></i> <?php echo $ticket['paquete_contratado']; ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-secondary fw-bold">Ubicación</h6>
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                <?php echo $ticket['calle'] . " #" . $ticket['numero_exterior'] . ", " . $ticket['colonia']; ?>
                                <br>
                                <small class="text-muted ms-4"><?php echo $ticket['delegacion_municipio']; ?></small>
                            </p>
                            <a href="https://maps.google.com/?q=<?php echo urlencode($ticket['calle']." ".$ticket['colonia']." ".$ticket['delegacion_municipio']); ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fas fa-location-arrow"></i> Ver en GPS
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted"><strong>Técnico Asignado:</strong> <?php echo $ticket['tecnico_nombre'] ?? 'Sin asignar'; ?></small>
                    </div>
                </div>
            </div>

            <?php if($ticket['estatus'] == 'resuelto'): ?>
            <div class="card border-0 shadow-sm border-start border-success border-4 mb-4 bg-success bg-opacity-10">
                <div class="card-body">
                    <h5 class="fw-bold text-success"><i class="fas fa-check-circle me-2"></i> Solución Aplicada</h5>
                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($ticket['solucion'])); ?></p>
                    
                    <?php 
                        $fotos = json_decode($ticket['evidencia_fotos'], true);
                        if(!empty($fotos)): 
                    ?>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                             <?php foreach($fotos as $foto): ?>
                                <a href="../<?php echo $foto; ?>" target="_blank">
                                    <img src="../<?php echo $foto; ?>" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <small class="text-muted d-block mt-2">Cerrado el: <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_cierre'])); ?></small>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-lg <?php echo ($ticket['estatus']=='resuelto'?'bg-success':'bg-primary'); ?> text-white mb-3">
                <div class="card-body">
                    <h5 class="fw-bold"><i class="fas fa-cog me-2"></i> Acciones</h5>
                    <hr class="border-white opacity-50">

                    <?php if ($puede_gestionar): ?>
                        
                        <?php if($ticket['estatus'] == 'asignado'): ?>
                            <button onclick="cambiarEstado('en_proceso')" class="btn btn-light text-primary fw-bold w-100 py-3 shadow-sm">
                                <i class="fas fa-play me-2"></i> INICIAR TRABAJO
                            </button>
                        <?php endif; ?>

                        <?php if($ticket['estatus'] == 'en_proceso'): ?>
                            <button onclick="abrirModalCierre()" class="btn btn-light text-success fw-bold w-100 mb-2 py-3 shadow-sm border border-success">
                                <i class="fas fa-check-circle me-2"></i> FINALIZAR
                            </button>
                            <button onclick="cambiarEstado('asignado')" class="btn btn-outline-light w-100 btn-sm">
                                <i class="fas fa-pause me-2"></i> Pausar
                            </button>
                        <?php endif; ?>

                    <?php endif; ?>

                    <?php if($ticket['estatus'] == 'resuelto'): ?>
                        <div class="mb-3"><i class="fas fa-check-double fa-lg me-2"></i> Servicio Finalizado</div>
                        <a href="reporte-ticket.php?id=<?php echo $ticket_id; ?>" target="_blank" class="btn btn-white text-danger fw-bold w-100 py-3 shadow">
                            <i class="fas fa-file-pdf me-2"></i> VER REPORTE PDF
                        </a>
                    <?php endif; ?>

                    <?php if($ticket['estatus'] != 'resuelto' && !$puede_gestionar): ?>
                        <div class="alert alert-white bg-white bg-opacity-25 border-0 text-white mb-0">
                            <small><i class="fas fa-info-circle"></i> Ticket en curso. Solo el técnico responsable puede actualizar el estado.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($puede_gestionar): ?>
<div class="modal fade" id="modalCierre" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Finalizar Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCierre" enctype="multipart/form-data">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                    <div class="mb-3">
                        <label class="fw-bold">Solución Aplicada *</label>
                        <textarea name="solucion" class="form-control" rows="4" required placeholder="Describe el trabajo..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Fotos de Evidencia</label>
                        <input type="file" name="fotos[]" class="form-control" multiple accept="image/*">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">CONFIRMAR Y CERRAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarEstado(nuevoEstado) {
    Swal.fire({
        title: '¿Confirmar?',
        text: "Cambiar estado del ticket",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/tickets/actualizar-estado.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `ticket_id=<?php echo $ticket_id; ?>&estado=${nuevoEstado}`
            }).then(r => r.json()).then(data => {
                if(data.success) location.reload();
                else Swal.fire('Error', data.message, 'error');
            });
        }
    });
}

function abrirModalCierre() {
    new bootstrap.Modal(document.getElementById('modalCierre')).show();
}

document.getElementById('formCierre').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    Swal.fire({ title: 'Procesando...', didOpen: () => Swal.showLoading() });

    fetch('../api/tickets/finalizar.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire('¡Listo!', 'Ticket cerrado y reporte generado', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});
</script>
<?php endif; ?>

<style>.btn-white { background:white; color:#333; } .btn-white:hover { background:#f0f0f0; }</style>
<?php require_once '../includes/footer.php'; ?>