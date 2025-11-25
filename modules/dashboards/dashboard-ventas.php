<?php
// --- CORRECCIÓN DE RUTA ---
// Usamos __DIR__ para decirle a PHP: "Calcula la ruta desde ESTE archivo físico, no desde quien lo llama".
require_once __DIR__ . '/../../config/database.php';

$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];

// Consultas específicas para Ventas
// 1. Ventas del mes actual (Usuario actual)
$stmt = $db->prepare("SELECT COUNT(*) as total FROM ventas WHERE usuario_id = ? AND MONTH(fecha_creacion) = MONTH(CURDATE()) AND YEAR(fecha_creacion) = YEAR(CURDATE())");
$stmt->execute([$uid]);
$misVentasMes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// 2. Ventas totales (Usuario actual)
$stmt = $db->prepare("SELECT COUNT(*) as total FROM ventas WHERE usuario_id = ?");
$stmt->execute([$uid]);
$misVentasTotal = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="container-fluid px-4 py-4">
    
    <div class="welcome-banner">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?> 👋</h1>
                <p>Panel de Ventas - Gestiona tus contratos y clientes</p>
            </div>
            <div class="d-none d-md-block">
                <i class="fas fa-briefcase fa-3x" style="opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        
        <a href="nueva-venta.php" style="text-decoration: none;">
            <div class="stat-card" style="border: 2px dashed #2563eb; background: #f8fafc;">
                <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                    <div class="icon-box icon-blue mb-2">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div style="color: #003366; font-weight: 700; font-size: 1.1rem;">Nueva Venta</div>
                    <small class="text-muted">Crear nuevo contrato</small>
                </div>
            </div>
        </a>

        <div class="stat-card">
            <div class="stat-header">
                <div class="icon-box icon-green">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div>
                <div class="stat-number"><?php echo $misVentasMes; ?></div>
                <div class="stat-label">Mis Ventas del Mes</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="icon-box icon-purple">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
            <div>
                <div class="stat-number"><?php echo $misVentasTotal; ?></div>
                <div class="stat-label">Total Acumulado</div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class="fas fa-clock me-2 text-primary"></i>
                Mis Ventas Recientes
            </div>
            <a href="ver-ventas.php" class="btn btn-outline-primary btn-sm">
                Ver Historial Completo
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Paquete</th>
                        <th>Fecha</th>
                        <th>Estado Flujo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Traer las últimas 5 ventas de ESTE vendedor
                    $stmt = $db->prepare("SELECT * FROM ventas WHERE usuario_id = ? ORDER BY id DESC LIMIT 5");
                    $stmt->execute([$uid]);
                    
                    if($stmt->rowCount() > 0):
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                            // Lógica visual del estado
                            $estadoTexto = 'En Espera';
                            $claseBadge = 'status-cancelada'; // Usamos naranja/rojo por defecto

                            if($row['estatus'] == 'completada') {
                                $estadoTexto = 'Instalado';
                                $claseBadge = 'status-completada'; // Azul
                            } elseif($row['estatus'] == 'activa') {
                                // Si ya tiene técnico, es verde (en proceso)
                                if(!empty($row['asignado_tecnico'])) {
                                    $estadoTexto = 'En Ruta';
                                    $claseBadge = 'status-activa';
                                } else {
                                    $estadoTexto = 'Pendiente Despacho';
                                    $claseBadge = 'status-cancelada'; 
                                }
                            }
                    ?>
                    <tr>
                        <td style="font-weight: 700; color: #003366;">
                            <?php echo $row['folio']; ?>
                        </td>
                        <td>
                            <?php echo $row['nombre_titular']; ?>
                            <br>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $row['colonia']; ?>
                            </small>
                        </td>
                        <td><?php echo $row['paquete_contratado']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['fecha_servicio'])); ?></td>
                        <td>
                            <span class="status-badge <?php echo $claseBadge; ?>">
                                <?php echo $estadoTexto; ?>
                            </span>
                        </td>
                        <td>
                            <a href="generar-pdf.php?id=<?php echo $row['id']; ?>" target="_blank" class="action-btn pdf" title="Descargar PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aún no has registrado ventas.</p>
                                <a href="nueva-venta.php" class="btn btn-sm btn-primary">Registrar Primera Venta</a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>