<?php
// modules/dashboards/dashboard-tecnicos.php
require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];
$nombre = $_SESSION['nombre_usuario'] ?? 'Técnico';

// 1. INSTALACIONES (Ventas)
$sqlInstalaciones = "SELECT id, folio, nombre_titular, calle, colonia, numero_exterior, telefono, paquete_contratado, 
                        'instalacion' as tipo, fecha_asignacion_tecnico as fecha, 'media' as prioridad, estatus
                     FROM ventas 
                     WHERE asignado_tecnico = ? AND estatus IN ('activa', 'completada')"; // Traemos completadas también para ver el PDF hoy

$stmt = $db->prepare($sqlInstalaciones);
$stmt->execute([$uid]);
$instalaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. TICKETS
$sqlTickets = "SELECT t.id, t.numero_ticket as folio, v.nombre_titular, v.calle, v.colonia, v.numero_exterior, v.telefono, 
                    t.asunto as paquete_contratado, 'ticket' as tipo, t.fecha_asignacion as fecha, t.prioridad, t.estatus
               FROM tickets_soporte t
               INNER JOIN ventas v ON t.venta_id = v.id
               WHERE t.asignado_a = ? AND (t.estatus IN ('asignado', 'en_proceso') OR t.estatus = 'resuelto')";

$stmt2 = $db->prepare($sqlTickets);
$stmt2->execute([$uid]);
$tickets = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// 3. Unir y Ordenar
$asignaciones = array_merge($instalaciones, $tickets);
usort($asignaciones, function($a, $b) {
    // Pendientes primero, Resueltos al final
    $aResuelto = ($a['estatus'] == 'resuelto' || $a['estatus'] == 'completada');
    $bResuelto = ($b['estatus'] == 'resuelto' || $b['estatus'] == 'completada');
    if ($aResuelto && !$bResuelto) return 1;
    if (!$aResuelto && $bResuelto) return -1;
    return strtotime($b['fecha']) - strtotime($a['fecha']);
});
?>

<div class="container-fluid px-4 py-4">
    <div class="card bg-primary text-white shadow-sm mb-4 border-0">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold m-0"><i class="fas fa-tools me-2"></i> Zona Técnica</h2>
                <p class="m-0 opacity-75">Bienvenido, <?php echo htmlspecialchars($nombre); ?></p>
            </div>
            <div class="text-end"><h3 class="m-0 fw-bold"><?php echo count($asignaciones); ?></h3><small>Total Lista</small></div>
        </div>
    </div>

    <?php if(empty($asignaciones)): ?>
        <div class="text-center py-5 mt-5">
            <div class="icon-box icon-green mx-auto mb-3" style="width:100px;height:100px;font-size:3rem;display:flex;align-items:center;justify-content:center;background:#e8f5e9;color:#2e7d32;border-radius:50%;"><i class="fas fa-check"></i></div>
            <h2 class="text-muted fw-bold">Sin asignaciones</h2>
            <button onclick="location.reload()" class="btn btn-primary mt-3 rounded-pill">Actualizar</button>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($asignaciones as $item): ?>
                <?php 
                    $esTicket = ($item['tipo'] == 'ticket');
                    $esResuelto = ($item['estatus'] == 'resuelto' || $item['estatus'] == 'completada');
                    
                    // Estilos
                    $borde = $esResuelto ? 'border-success' : ($esTicket ? 'border-warning' : 'border-info');
                    $badgeBg = $esResuelto ? 'bg-success text-white' : ($esTicket ? 'bg-warning text-dark' : 'bg-info text-white');
                    $icono = $esTicket ? 'fa-headset' : 'fa-wifi';
                    $textoTipo = $esTicket ? 'SOPORTE' : 'INSTALACIÓN';
                    if($esResuelto) $textoTipo = 'FINALIZADO';

                    // Links (AQUÍ ESTABA EL ERROR ANTES, AHORA CORREGIDO)
                    // Instalación -> ver-orden.php | Ticket -> ticket-detalle.php
                    $linkDetalle = $esTicket ? "ticket-detalle.php?id=" . $item['id'] : "ver-orden.php?id=" . $item['id'];
                    $linkPDF = $esTicket ? "reporte-ticket.php?id=" . $item['id'] : "../modules/generar-pdf.php?id=" . $item['id'];
                ?>

                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm <?php echo $borde; ?> border-start border-4 hover-shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge <?php echo $badgeBg; ?> rounded-pill px-3 py-2">
                                    <i class="fas <?php echo $icono; ?>"></i> <?php echo $textoTipo; ?>
                                </span>
                                <?php if(!$esResuelto && isset($item['prioridad']) && $item['prioridad'] == 'urgente'): ?>
                                    <span class="badge bg-danger animate__animated animate__pulse animate__infinite">URGENTE</span>
                                <?php endif; ?>
                            </div>

                            <h5 class="card-title fw-bold text-dark mb-1"><?php echo htmlspecialchars($item['nombre_titular']); ?></h5>
                            <small class="text-muted fw-bold mb-2 d-block">#<?php echo $item['folio']; ?></small>

                            <p class="mb-2 text-dark small">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> 
                                <?php echo htmlspecialchars($item['colonia'] . ', ' . $item['calle'] . ' #' . $item['numero_exterior']); ?>
                            </p>

                            <div class="bg-light p-2 rounded mb-3 border">
                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size:0.7rem;">Detalle:</small>
                                <span class="text-primary fw-bold text-truncate d-block"><?php echo htmlspecialchars($item['paquete_contratado']); ?></span>
                            </div>

                            <div class="d-grid gap-2">
                                <?php if($esResuelto): ?>
                                    <a href="<?php echo $linkPDF; ?>" target="_blank" class="btn btn-outline-danger fw-bold"><i class="fas fa-file-pdf me-2"></i> Ver Reporte PDF</a>
                                <?php else: ?>
                                    <a href="<?php echo $linkDetalle; ?>" class="btn btn-primary fw-bold">Gestionar <i class="fas fa-arrow-right ms-2"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<style>.hover-shadow:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; transition:0.2s; }</style>