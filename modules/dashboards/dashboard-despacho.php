<?php
require_once __DIR__ . '/../../config/database.php';
$db = (new Database())->getConnection();

// 1. Órdenes Pendientes (Sin Técnico)
$pendientes = $db->query("SELECT * FROM ventas WHERE estatus = 'activa' AND (asignado_tecnico IS NULL OR asignado_tecnico = 0)")->fetchAll(PDO::FETCH_ASSOC);

// 2. Monitoreo GLOBAL (En proceso + Finalizadas hoy)
// Esto permite ver las que están corriendo y las que ya terminaron hoy
$monitoreo = $db->query("SELECT v.*, u.nombre_completo as tecnico_nombre 
                          FROM ventas v 
                          JOIN usuarios u ON v.asignado_tecnico = u.id 
                          WHERE v.asignado_tecnico > 0 
                          AND (v.estatus = 'activa' OR (v.estatus = 'completada' AND DATE(v.fecha_completada) = CURDATE()))
                          ORDER BY v.estatus ASC, v.fecha_asignacion_tecnico DESC")->fetchAll(PDO::FETCH_ASSOC);

$tecnicos = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol_id = 4 AND activo = 1")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">
    <div class="welcome-banner mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Centro de Control y Despacho</h1>
                <p>Gestión de tiempo real y evidencias.</p>
            </div>
            <i class="fas fa-satellite-dish fa-3x text-white-50"></i>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="text-danger fw-bold m-0"><i class="fas fa-inbox me-2"></i> 1. Órdenes por Asignar (<?php echo count($pendientes); ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive-scroll">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente / Dirección</th>
                            <th>Plan</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($pendientes) > 0): foreach($pendientes as $p): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?php echo $p['folio']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo $p['nombre_titular']; ?></div>
                                <small class="text-muted"><?php echo $p['colonia']; ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo $p['paquete_contratado']; ?></span></td>
                            <td class="text-end">
                                <button onclick="abrirAsignar(<?php echo $p['id']; ?>, '<?php echo $p['folio']; ?>')" class="btn btn-sm btn-primary">
                                    Asignar Técnico <i class="fas fa-arrow-right"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No hay pendientes.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="text-primary fw-bold m-0"><i class="fas fa-stopwatch me-2"></i> 2. Monitoreo de Técnicos</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive-scroll">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Técnico</th>
                            <th>Orden / Cliente</th>
                            <th>Estado</th>
                            <th>Tiempo Total</th>
                            <th class="text-end">Evidencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($monitoreo as $m): 
                            // Cálculo de tiempos
                            $inicio = strtotime($m['fecha_asignacion_tecnico']) * 1000; // JS usa milisegundos
                            $es_finalizada = ($m['estatus'] == 'completada');
                            
                            // Si ya terminó, calculamos el tiempo fijo. Si no, dejamos que JS lo calcule.
                            $tiempo_final_texto = "Calculando...";
                            if($es_finalizada) {
                                $fin = strtotime($m['fecha_completada']);
                                $inicio_php = strtotime($m['fecha_asignacion_tecnico']);
                                $diff = $fin - $inicio_php;
                                $horas = floor($diff / 3600);
                                $mins = floor(($diff % 3600) / 60);
                                $tiempo_final_texto = sprintf("%02dh %02dm", $horas, $mins);
                            }
                        ?>
                        <tr class="<?php echo $es_finalizada ? 'bg-light' : ''; ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box icon-blue me-2" style="width: 30px; height: 30px; font-size: 0.8rem;"><i class="fas fa-user"></i></div>
                                    <div class="fw-bold"><?php echo $m['tecnico_nombre']; ?></div>
                                </div>
                            </td>
                            <td>
                                <span class="text-primary fw-bold small"><?php echo $m['folio']; ?></span><br>
                                <span class="small"><?php echo $m['nombre_titular']; ?></span>
                            </td>
                            <td>
                                <?php if($es_finalizada): ?>
                                    <span class="badge bg-success">FINALIZADO</span>
                                <?php elseif($m['estado_instalacion'] == 'pausado'): ?>
                                    <span class="badge bg-warning text-dark">PAUSADO</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">EN CURSO</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($es_finalizada): ?>
                                    <span class="fw-bold text-success"><?php echo $tiempo_final_texto; ?></span>
                                <?php else: ?>
                                    <div class="timer-badge timer-running" 
                                         id="timer-<?php echo $m['id']; ?>"
                                         data-start="<?php echo $inicio; ?>"
                                         data-state="<?php echo $m['estado_instalacion']; ?>">
                                        00:00:00
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if($es_finalizada): ?>
                                    <a href="../modules/generar-pdf.php?id=<?php echo $m['id']; ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf"></i> Ver Reporte & Evidencia
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">En proceso...</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAsignar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Asignar Técnico</h5>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsignar()"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formAsignar">
                    <input type="hidden" name="venta_id" id="inputVentaId">
                    <div class="text-center mb-4">
                        <h4 class="text-primary" id="lblFolio"></h4>
                    </div>
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">Seleccionar Técnico:</label>
                        <select name="tecnico_id" class="form-control form-select" style="color: #000 !important; background: #fff !important;" required>
                            <option value="">-- Elige un técnico --</option>
                            <?php foreach($tecnicos as $tec): ?>
                                <option value="<?php echo $tec['id']; ?>"><?php echo $tec['nombre_completo']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Iniciar Cronómetro</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// CRONÓMETRO GLOBAL
setInterval(() => {
    const now = Date.now();
    document.querySelectorAll('.timer-badge').forEach(el => {
        // El tiempo corre desde que se asignó (data-start)
        const start = parseInt(el.dataset.start);
        const diff = Math.floor((now - start) / 1000); // Segundos totales
        
        if(diff >= 0) {
            const h = Math.floor(diff / 3600).toString().padStart(2,'0');
            const m = Math.floor((diff % 3600) / 60).toString().padStart(2,'0');
            const s = Math.floor(diff % 60).toString().padStart(2,'0');
            el.innerText = `${h}:${m}:${s}`;
        }
    });
}, 1000);

function abrirAsignar(id, folio) {
    document.getElementById('inputVentaId').value = id;
    document.getElementById('lblFolio').innerText = folio;
    new bootstrap.Modal(document.getElementById('modalAsignar')).show();
}

function cerrarModalAsignar() {
    const el = document.getElementById('modalAsignar');
    const modal = bootstrap.Modal.getInstance(el);
    modal.hide();
}

document.getElementById('formAsignar').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    fetch('../api/despacho/asignar-tecnico.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
        else alert('Error al asignar');
    });
});
</script>