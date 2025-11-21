<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../libs/fpdf/fpdf.php';
require_once '../libs/phpqrcode/qrlib.php';

if (!isset($_GET['id'])) die("ID de venta no especificado");

$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM ventas WHERE id = ?");
$stmt->execute([$_GET['id']]);
$v = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$v) die("Venta no encontrada");

// 1. Generar QR temporal
$tempDir = '../assets/img/temp/';
if (!file_exists($tempDir)) mkdir($tempDir);
$qrContent = "FOLIO: " . $v['folio'] . "\nCLIENTE: " . $v['nombre_titular'] . "\nPLAN: " . $v['paquete_contratado'];
$qrFile = $tempDir . 'qr_' . $v['folio'] . '.png';
QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 3);

// 2. Configurar PDF
class PDF extends FPDF {
    function Header() {
        // Logo
        if(file_exists('../assets/img/logo.png')) {
            $this->Image('../assets/img/logo.png', 10, 6, 30);
        }
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, 'ORDEN DE SERVICIO', 0, 0, 'C');
        $this->Ln(20);
        
        // Fondo futurista encabezado (Línea azul)
        $this->SetFillColor(0, 12, 102);
        $this->Rect(0, 25, 210, 2, 'F');
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb} - BDIGITAL TELECOMUNICACIONES', 0, 0, 'C');
    }
    
    function SectionTitle($label) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(200, 220, 255);
        $this->Cell(0, 8, "  $label", 0, 1, 'L', true);
        $this->Ln(4);
    }
    
    function DataField($label, $value) {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(45, 6, utf8_decode($label), 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, utf8_decode($value), 0, 1);
        $this->Line($this->GetX(), $this->GetY(), 190, $this->GetY()); // Línea subrayado
        $this->Ln(2);
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Datos Generales
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 10, 'FOLIO: ' . $v['folio'], 0, 0);
$pdf->Cell(60, 10, 'FECHA: ' . $v['fecha_servicio'], 0, 1);

// Sección Cliente
$pdf->SectionTitle('DATOS DEL CLIENTE');
$pdf->DataField('TITULAR:', $v['nombre_titular']);
$pdf->DataField('DIRECCION:', $v['calle'] . ' No. ' . $v['numero_exterior'] . ' ' . $v['numero_interior']);
$pdf->DataField('COLONIA:', $v['colonia'] . ' CP: ' . $v['codigo_postal']);
$pdf->DataField('MUNICIPIO:', $v['delegacion_municipio']);
$pdf->DataField('CONTACTO:', 'Tel: ' . $v['telefono'] . ' / Cel: ' . $v['celular']);

// Sección Servicio
$pdf->Ln(5);
$pdf->SectionTitle('DETALLES DEL SERVICIO');
$pdf->DataField('TIPO:', strtoupper($v['tipo_servicio']));
$pdf->DataField('PAQUETE:', $v['paquete_contratado']);
$pdf->DataField('ONT MODELO:', $v['ont_modelo']);
$pdf->DataField('ONT SERIE:', $v['ont_serie']);

// Insertar QR
$pdf->Image($qrFile, 160, 40, 35, 35);

// Firmas
$pdf->Ln(40);
$pdf->Cell(90, 0, '', 'T'); // Línea firma 1
$pdf->Cell(10, 0, '', 0);
$pdf->Cell(90, 0, '', 'T'); // Línea firma 2
$pdf->Ln(2);
$pdf->Cell(90, 5, 'FIRMA DEL CLIENTE', 0, 0, 'C');
$pdf->Cell(10, 5, '', 0);
$pdf->Cell(90, 5, 'FIRMA DEL TECNICO', 0, 0, 'C');

$pdf->Output('I', 'Orden_' . $v['folio'] . '.pdf');

// Limpieza (opcional borrar QR)
// unlink($qrFile);
?>