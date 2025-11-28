<?php
// modules/reporte-ticket.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['rol_id']) || !isset($_GET['id'])) {
    die("Acceso denegado");
}

$db = (new Database())->getConnection();
$id = $_GET['id'];

// 1. Obtener datos completos
$sql = "SELECT 
            t.*, 
            v.nombre_titular, v.calle, v.colonia, v.numero_exterior, v.delegacion_municipio, v.telefono, v.paquete_contratado,
            u.nombre_completo as tecnico,
            ud.nombre_completo as despacho
        FROM tickets_soporte t
        INNER JOIN ventas v ON t.venta_id = v.id
        LEFT JOIN usuarios u ON t.asignado_a = u.id
        LEFT JOIN usuarios ud ON t.usuario_despacho = ud.id
        WHERE t.id = ?";

$stmt = $db->prepare($sql);
$stmt->execute([$id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$t) die("Ticket no encontrado");

// Decodificar fotos (vienen en formato JSON text: ["ruta1.jpg", "ruta2.jpg"])
$fotos = json_decode($t['evidencia_fotos'], true);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ticket #<?php echo $t['numero_ticket']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eee; font-family: Arial, sans-serif; -webkit-print-color-adjust: exact; }
        .hoja {
            background: white;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logo-box { max-width: 150px; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; }
        .foto-evidencia { 
            width: 150px; 
            height: 150px; 
            object-fit: cover; 
            border-radius: 8px; 
            border: 2px solid #eee;
            margin: 5px;
        }
        @media print {
            body { background: white; margin: 0; }
            .hoja { margin: 0; box-shadow: none; border: none; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="text-center mt-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg shadow">
            <i class="fas fa-print"></i> Imprimir / Guardar PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg shadow ms-2">Cerrar</button>
    </div>

    <div class="hoja">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div class="logo-box">
                <h2 class="fw-bold text-primary">Bgital</h2>
                <small class="text-muted">Telecomunicaciones</small>
            </div>
            <div class="text-end">
                <h4 class="m-0 fw-bold">REPORTE DE SERVICIO</h4>
                <h5 class="text-danger">#<?php echo $t['numero_ticket']; ?></h5>
                <small>Fecha Cierre: <?php echo date('d/m/Y H:i', strtotime($t['fecha_cierre'])); ?></small>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <div class="info-box h-100">
                    <h6 class="fw-bold text-uppercase text-primary border-bottom pb-2">Cliente</h6>
                    <p class="mb-1"><strong><?php echo $t['nombre_titular']; ?></strong></p>
                    <p class="mb-1 text-muted small"><?php echo $t['calle']." #".$t['numero_exterior'].", ".$t['colonia']; ?></p>
                    <p class="mb-1 text-muted small"><?php echo $t['delegacion_municipio']; ?></p>
                    <p class="mb-0 fw-bold"><i class="fas fa-phone"></i> <?php echo $t['telefono']; ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="info-box h-100">
                    <h6 class="fw-bold text-uppercase text-primary border-bottom pb-2">Detalles del Servicio</h6>
                    <p class="mb-1"><strong>Plan:</strong> <?php echo $t['paquete_contratado']; ?></p>
                    <p class="mb-1"><strong>Técnico:</strong> <?php echo $t['tecnico'] ?? 'No asignado'; ?></p>
                    <p class="mb-1"><strong>Tipo:</strong> <?php echo strtoupper($t['categoria']); ?></p>
                    <p class="mb-0"><strong>Prioridad:</strong> <?php echo strtoupper($t['prioridad']); ?></p>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <h6 class="fw-bold bg-light p-2 border-start border-4 border-warning">MOTIVO DEL REPORTE</h6>
            <p class="ps-2"><strong>Asunto:</strong> <?php echo $t['asunto']; ?></p>
            <p class="ps-2 text-muted fst-italic">"<?php echo nl2br($t['descripcion']); ?>"</p>
        </div>

        <div class="mb-4">
            <h6 class="fw-bold bg-light p-2 border-start border-4 border-success">SOLUCIÓN TÉCNICA</h6>
            <div class="p-3 border rounded bg-white">
                <?php echo nl2br($t['solucion']); ?>
            </div>
        </div>

        <?php if(!empty($fotos)): ?>
        <div class="mb-4">
            <h6 class="fw-bold border-bottom pb-2">Evidencia Fotográfica</h6>
            <div class="d-flex flex-wrap">
                <?php foreach($fotos as $foto): ?>
                    <img src="../<?php echo $foto; ?>" class="foto-evidencia" alt="Evidencia">
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mt-5 pt-5">
            <div class="col-6 text-center">
                <div class="border-top border-dark w-75 mx-auto pt-2">
                    Firma del Técnico
                </div>
            </div>
            <div class="col-6 text-center">
                <div class="border-top border-dark w-75 mx-auto pt-2">
                    Firma de Conformidad (Cliente)
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5 text-muted small">
            <p>Este documento es un comprobante digital generado por el sistema ERP de Bgital.</p>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        // Opcional: Imprimir automáticamente al abrir
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>