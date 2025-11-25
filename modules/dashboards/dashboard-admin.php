<?php
// 1. Conexión y consultas
require_once '../config/database.php';
$db = (new Database())->getConnection();

// Consultas de estadísticas generales (ADMIN VE TODO)
$ventasHoy = $db->query("SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha_creacion) = CURDATE()")->fetch(PDO::FETCH_ASSOC)['total'];
$ventasMes = $db->query("SELECT COUNT(*) as total FROM ventas WHERE MONTH(fecha_creacion) = MONTH(CURDATE()) AND YEAR(fecha_creacion) = YEAR(CURDATE())")->fetch(PDO::FETCH_ASSOC)['total'];
$totalVentas = $db->query("SELECT COUNT(*) as total FROM ventas")->fetch(PDO::FETCH_ASSOC)['total'];
$usuariosActivos = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1")->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="container-fluid px-4 py-4">
    
    <div class="welcome-banner">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
                <p>Panel de Administración General - BGITAL</p>
            </div>
            <div class="d-none d-md-block">
                <i class="fas fa-chart-line fa-3x" style="opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="icon-box icon-blue">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <div>
                <div class="stat-number"><?php echo $ventasHoy; ?></div>
                <div class="stat-label">Ventas Hoy</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="icon-box icon-green">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div>
                <div class="stat-number"><?php echo $ventasMes; ?></div>
                <div class="stat-label">Ventas del Mes</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="icon-box icon-purple">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div>
                <div class="stat-number"><?php echo $usuariosActivos; ?></div>
                <div class="stat-label">Usuarios Activos</div>
            </div>
        </div>

        <a href="../modules/admin/gestionar-usuarios.php" style="text-decoration: none;">
            <div class="stat-card" style="border: 2px dashed #cbd5e1; background: transparent;">
                <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                    <div class="icon-box icon-orange mb-2">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div style="color: #003366; font-weight: 700;">Gestionar Usuarios</div>
                    <small class="text-muted">Administrar accesos</small>
                </div>
            </div>
        </a>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class="fas fa-history me-2 text-primary"></i>
                Últimas Ventas Registradas (Global)
            </div>
            <a href="../modules/ver-ventas.php" class="btn btn-outline-primary btn-sm">
                Ver Todas
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Plan</th>
                        <th>Fecha</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consulta para admin: Muestra ventas de TODOS
                    $query = $db->query("SELECT * FROM ventas ORDER BY id DESC LIMIT 10");
                    
                    if($query->rowCount() > 0):
                        while($row = $query->fetch(PDO::FETCH_ASSOC)):
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
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?php echo $row['paquete_contratado']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['fecha_servicio'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $row['estatus']; ?>">
                                <?php echo strtoupper($row['estatus']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="../modules/generar-pdf.php?id=<?php echo $row['id']; ?>" 
                               target="_blank" 
                               class="action-btn pdf" 
                               title="Descargar PDF">
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
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay ventas registradas en el sistema.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>