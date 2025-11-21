<?php include '../includes/header.php'; ?>
<?php
// Obtener resumen rápido
require_once '../config/database.php';
$db = (new Database())->getConnection();
$uid = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT COUNT(*) as total FROM ventas WHERE usuario_id = ? AND DATE(fecha_creacion) = CURDATE()");
$stmt->execute([$uid]);
$ventasHoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="card" style="background: linear-gradient(to right, #000C66, #000428); color: white;">
    <h1>Hola, <?php echo $_SESSION['nombre']; ?> 👋</h1>
    <p>Bienvenido al panel de ventas digital.</p>
</div>

<div class="form-grid">
    <div class="card text-center">
        <i class="fas fa-chart-line" style="font-size: 2rem; color: var(--accent);"></i>
        <h3><?php echo $ventasHoy; ?></h3>
        <p>Ventas Hoy</p>
    </div>
    <a href="nueva-venta.php" class="card text-center" style="text-decoration: none; border: 2px dashed var(--accent);">
        <i class="fas fa-plus-circle" style="font-size: 2rem; color: var(--primary);"></i>
        <h3 style="color: var(--primary);">Nueva Venta</h3>
        <p>Crear contrato</p>
    </a>
</div>

<h2 style="margin-top: 30px; margin-bottom: 15px;">Últimas Ventas</h2>
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
            while($row = $query->fetch(PDO::FETCH_ASSOC)):
            ?>
            <tr>
                <td><b><?php echo $row['folio']; ?></b></td>
                <td><?php echo $row['nombre_titular']; ?></td>
                <td><?php echo $row['paquete_contratado']; ?></td>
                <td><?php echo $row['fecha_servicio']; ?></td>
                <td><span class="status-badge status-<?php echo $row['estatus']; ?>"><?php echo strtoupper($row['estatus']); ?></span></td>
                <td>
                    <a href="generar-pdf.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</main> <!-- Cierra content-area -->
</div> <!-- Cierra main-layout -->
</body>
</html>