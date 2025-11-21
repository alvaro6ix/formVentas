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
$qrContent = "FOLIO: " . $v['folio'] . "\nCLIENTE: " . $v['nombre_titular'] . "\nPLAN: " . $v['paquete_contratado'] . "\nDIRECCIÓN: " . $v['calle'] . " " . $v['numero_exterior'];
$qrFile = $tempDir . 'qr_' . $v['folio'] . '.png';
QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 3);

// 2. Configurar PDF
class PDF extends FPDF {
    private $title = 'ORDEN DE SERVICIO BDIGITAL';
    
    function Header() {
        // Logo
        if(file_exists('../assets/img/logo.png')) {
            $this->Image('../assets/img/logo.png', 10, 8, 25);
        }
        
        // Título
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 12, 102);
        $this->Cell(0, 10, $this->title, 0, 1, 'C');
        
        // Línea decorativa
        $this->SetDrawColor(0, 212, 255);
        $this->SetLineWidth(0.5);
        $this->Line(10, 20, 200, 20);
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' - BDIGITAL TELECOMUNICACIONES - ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
    
    function SectionTitle($title) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(230, 240, 255);
        $this->SetTextColor(0, 12, 102);
        $this->Cell(0, 8, $title, 0, 1, 'L', true);
        $this->Ln(2);
    }
    
    function InfoRow($label, $value, $width = 45) {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($width, 6, utf8_decode($label), 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, utf8_decode($value), 0, 1);
        $this->Ln(1);
    }
    
    function TableHeader($headers) {
        $this->SetFillColor(0, 12, 102);
        $this->SetTextColor(255);
        $this->SetFont('Arial', 'B', 9);
        
        foreach($headers as $header) {
            $this->Cell($header[1], 7, utf8_decode($header[0]), 1, 0, 'C', true);
        }
        $this->Ln();
    }
    
    function TableRow($data) {
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 9);
        
        foreach($data as $cell) {
            $this->Cell($cell[1], 6, utf8_decode($cell[0]), 1);
        }
        $this->Ln();
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Encabezado con datos principales
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 6, 'FOLIO: ' . $v['folio'], 0, 0);
$pdf->Cell(95, 6, 'FECHA: ' . date('d/m/Y', strtotime($v['fecha_servicio'])), 0, 1);
$pdf->Cell(95, 6, 'TIPO SERVICIO: ' . strtoupper($v['tipo_servicio']), 0, 0);
$pdf->Cell(95, 6, 'ESTATUS: ' . strtoupper($v['estatus']), 0, 1);
$pdf->Ln(5);

// Sección Cliente
$pdf->SectionTitle('INFORMACION DEL TITULAR');
$pdf->InfoRow('Nombre del Titular:', $v['nombre_titular'], 40);
$pdf->InfoRow('Dirección:', $v['calle'] . ' No. ' . $v['numero_exterior'] . 
    ($v['numero_interior'] ? ' Int. ' . $v['numero_interior'] : ''), 40);
$pdf->InfoRow('Colonia:', $v['colonia'] . ' CP: ' . $v['codigo_postal'], 40);
$pdf->InfoRow('Municipio:', $v['delegacion_municipio'], 40);
$pdf->InfoRow('Contacto:', 'Tel: ' . $v['telefono'] . ($v['celular'] ? ' / Cel: ' . $v['celular'] : ''), 40);
$pdf->InfoRow('Email:', $v['correo_electronico'] ?: 'No especificado', 40);
$pdf->InfoRow('Tipo Vivienda:', ucfirst($v['tipo_vivienda']) . 
    ($v['tipo_vivienda_otro'] ? ' (' . $v['tipo_vivienda_otro'] . ')' : ''), 40);

if($v['referencias']) {
    $pdf->InfoRow('Referencias:', $v['referencias'], 40);
}
$pdf->Ln(2);

// Sección Servicio
$pdf->SectionTitle('INFORMACION DEL SERVICIO CONTRATADO');
$pdf->InfoRow('Paquete Contratado:', $v['paquete_contratado'], 40);
if($v['tipo_promocion']) {
    $pdf->InfoRow('Promoción:', $v['tipo_promocion'], 40);
}
if($v['numero_cuenta']) {
    $pdf->InfoRow('Número de Cuenta:', $v['numero_cuenta'], 40);
}
if($v['puerto']) {
    $pdf->InfoRow('Puerto:', $v['puerto'], 40);
}
if($v['placa']) {
    $pdf->InfoRow('Placa:', $v['placa'], 40);
}
$pdf->InfoRow('Contrato Entregado:', $v['contrato_entregado'] ? 'SI' : 'NO', 40);
if($v['identificacion']) {
    $pdf->InfoRow('Identificación:', $v['identificacion'], 40);
}
$pdf->Ln(2);

// Sección Equipos
$pdf->SectionTitle('EQUIPOS Y MATERIALES UTILIZADOS');

// Tabla de equipos
$headers = [
    ['EQUIPO', 50],
    ['MODELO', 50],
    ['No. DE SERIE', 50],
    ['OBSERVACIONES', 40]
];

$pdf->TableHeader($headers);

$equipos = [
    [
        ['ONT', 50],
        [$v['ont_modelo'] ?: 'N/A', 50],
        [$v['ont_serie'] ?: 'N/A', 50],
        ['Equipo principal', 40]
    ],
    [
        ['OTRO EQUIPO', 50],
        [$v['otro_equipo_modelo'] ?: 'N/A', 50],
        [$v['otro_equipo_serie'] ?: 'N/A', 50],
        ['Equipo adicional', 40]
    ]
];

foreach($equipos as $equipo) {
    $pdf->TableRow($equipo);
}

$pdf->Ln(5);

// Materiales utilizados
if($v['materiales_utilizados']) {
    $materiales = json_decode($v['materiales_utilizados'], true);
    if($materiales) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, 'MATERIALES UTILIZADOS:', 0, 1);
        $pdf->SetFont('Arial', '', 9);
        
        foreach($materiales as $material => $cantidad) {
            $pdf->Cell(0, 5, '• ' . ucfirst($material) . ': ' . $cantidad, 0, 1);
        }
        $pdf->Ln(3);
    }
}

// Notas de instalación
if($v['notas_instalacion']) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 6, 'NOTAS DE INSTALACION:', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 5, utf8_decode($v['notas_instalacion']));
    $pdf->Ln(3);
}

// Insertar QR
$pdf->Image($qrFile, 160, 40, 35, 35);

// Evaluación del servicio si existe
if($v['eval_servicios_explicados'] !== null) {
    $pdf->AddPage();
    $pdf->SectionTitle('EVALUACION DEL SERVICIO');
    
    $pdf->InfoRow('Servicios explicados:', $v['eval_servicios_explicados'] ? 'SI' : 'NO', 50);
    $pdf->InfoRow('Manual entregado:', $v['eval_manual_entregado'] ? 'SI' : 'NO', 50);
    $pdf->InfoRow('Trato recibido:', ucfirst($v['eval_trato_recibido']) ?: 'No evaluado', 50);
    $pdf->InfoRow('Eficiencia:', ucfirst($v['eval_eficiencia']) ?: 'No evaluado', 50);
    
    $pdf->Ln(10);
    
    // Términos y condiciones
    $pdf->SectionTitle('TERMINOS Y CONDICIONES');
    $pdf->SetFont('Arial', '', 9);
    $terminos = "Por medio de la presente manifiesto mi conformidad con la instalación del servicio BDIGITAL en mi domicilio. "
        . "Acepto que todos los equipos son propiedad de BDIGITAL y cualquier componente no entregado o dañado será susceptible de cobro. "
        . "Autorizo la instalación de cables, equipos y accesorios necesarios para el servicio.";
    
    $pdf->MultiCell(0, 5, utf8_decode($terminos));
    $pdf->Ln(10);
}

// Firmas
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 5, 'FIRMA DEL CLIENTE', 0, 0, 'C');
$pdf->Cell(95, 5, 'FIRMA DEL TECNICO', 0, 1, 'C');
$pdf->Ln(8);

// Líneas para firmas
$pdf->Cell(95, 0, '', 'T');
$pdf->Cell(5, 0, '');
$pdf->Cell(95, 0, '', 'T');
$pdf->Ln(5);

// Datos del instalador si existen
if($v['instalador_nombre']) {
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(95, 5, 'Nombre: ' . $v['nombre_titular'], 0, 0, 'C');
    $pdf->Cell(95, 5, 'Instalador: ' . $v['instalador_nombre'], 0, 1, 'C');
    
    if($v['instalador_numero']) {
        $pdf->Cell(95, 5, '', 0, 0);
        $pdf->Cell(95, 5, 'No. Empleado: ' . $v['instalador_numero'], 0, 1, 'C');
    }
}

$pdf->Output('I', 'Orden_Servicio_' . $v['folio'] . '.pdf');

// Limpiar archivo QR temporal después de 5 minutos (opcional)
// unlink($qrFile);
?>