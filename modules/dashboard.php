<?php include '../includes/header.php'; ?>
<?php
// Obtener resumen rápido
require_once '../config/database.php';
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];

// Ventas de hoy
$stmt = $db->prepare("SELECT COUNT(*) as total FROM ventas WHERE usuario_id = ? AND DATE(fecha_creacion) = CURDATE()");
$stmt->execute([$uid]);
$ventasHoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total de ventas
$stmt = $db->prepare("SELECT COUNT(*) as total FROM ventas WHERE usuario_id = ?");
$stmt->execute([$uid]);
$totalVentas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Ventas del mes
$stmt = $db->prepare("SELECT COUNT(*) as total FROM ventas WHERE usuario_id = ? AND MONTH(fecha_creacion) = MONTH(CURDATE()) AND YEAR(fecha_creacion) = YEAR(CURDATE())");
$stmt->execute([$uid]);
$ventasMes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="card" style="background: var(--gradient-primary); color: white;">
    <h1>Bienvenido, <?php echo $_SESSION['nombre']; ?> 👋</h1>
    <p>Panel de control del sistema de ventas BDIGITAL</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-value"><?php echo $ventasHoy; ?></div>
        <div class="stat-label">Ventas Hoy</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-value"><?php echo $ventasMes; ?></div>
        <div class="stat-label">Ventas del Mes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="stat-value"><?php echo $totalVentas; ?></div>
        <div class="stat-label">Total Ventas</div>
    </div>
    
    <a href="nueva-venta.php" class="stat-card text-center" style="text-decoration: none; border: 2px dashed var(--accent); display: flex; flex-direction: column; justify-content: center; align-items: center;">
        <i class="fas fa-plus-circle" style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;"></i>
        <h3 style="color: var(--primary); margin-bottom: 0.5rem;">Nueva Venta</h3>
        <p style="color: var(--text-muted);">Crear contrato</p>
    </a>
</div>

<div class="table-container">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-history"></i>
            Últimas Ventas
        </div>
        <a href="ver-ventas.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-list"></i>
            Ver Todas
        </a>
    </div>
    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Titular</th>
                    <th>Plan</th>
                    <th>Fecha</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = $db->prepare("SELECT * FROM ventas WHERE usuario_id = ? ORDER BY id DESC LIMIT 5");
                $query->execute([$uid]);
                if($query->rowCount() > 0):
                    while($row = $query->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><b><?php echo $row['folio']; ?></b></td>
                    <td><?php echo $row['nombre_titular']; ?></td>
                    <td><?php echo $row['paquete_contratado']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['fecha_servicio'])); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $row['estatus']; ?>">
                            <?php echo strtoupper($row['estatus']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="generar-pdf.php?id=<?php echo $row['id']; ?>" target="_blank" class="action-btn pdf" title="Generar PDF">
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </a>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                        <p>No hay ventas registradas</p>
                        <a href="nueva-venta.php" class="btn btn-primary mt-2">
                            <i class="fas fa-plus"></i>
                            Crear Primera Venta
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</div>
</body>
</html>