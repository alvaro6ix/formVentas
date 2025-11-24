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

// 1. GENERAR QR
$tempDir = '../assets/img/temp/';
if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
$qrContent = "FOLIO: " . $v['folio'] . "\nCLIENTE: " . $v['nombre_titular'] . "\nPLAN: " . $v['paquete_contratado'];
$qrFile = $tempDir . 'qr_' . $v['folio'] . '.png';
QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 3);

// 2. CLASE PDF
class PDF extends FPDF {
    function Header() {
        $logoPath = 'C:\xampp\htdocs\FormVentas\assets\img-logo\bgital_logo_moderno.png'; 
        if(file_exists($logoPath)) $this->Image($logoPath, 10, 10, 30);
        
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 12, 102);
        $this->Cell(0, 10, utf8_decode('ORDEN DE SERVICIO BGITAL'), 0, 1, 'C');
        
        $this->SetDrawColor(0, 212, 255);
        $this->SetLineWidth(0.5);
        $this->Line(10, 25, 200, 25);
        $this->Ln(15);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' - BDIGITAL TELECOMUNICACIONES - ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
    
    // Títulos de Sección: FONDO AZUL CLARO (Corregido para acentos)
    function SectionTitle($title) {
        $this->Ln(4);
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(230, 240, 255);
        $this->SetTextColor(0, 12, 102);
        $titulo_mayus = mb_strtoupper($title, 'UTF-8');
        $this->Cell(0, 8, utf8_decode($titulo_mayus), 0, 1, 'L', true);
        $this->SetTextColor(0);
        $this->Ln(2);
    }
    
    function InfoRow($label, $value, $width = 50) {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($width, 5, utf8_decode($label), 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, utf8_decode($value), 0, 1);
    }

    function TableHeader($headers) {
        $this->SetFillColor(0, 12, 102);
        $this->SetTextColor(255);
        $this->SetFont('Arial', 'B', 8);
        foreach($headers as $header) {
            $this->Cell($header[1], 6, utf8_decode($header[0]), 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(0);
    }
    
    function TableRow($data) {
        $this->SetFont('Arial', '', 8);
        foreach($data as $cell) {
            $this->Cell($cell[1], 6, utf8_decode($cell[0]), 1);
        }
        $this->Ln();
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// QR y Datos Cabecera
$pdf->Image($qrFile, 165, 28, 30, 30);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 6, 'FOLIO: ' . $v['folio'], 0, 1);
$pdf->Cell(95, 6, 'FECHA: ' . date('d/m/Y', strtotime($v['fecha_servicio'])), 0, 1);
$pdf->Cell(95, 6, 'TIPO SERVICIO: ' . strtoupper($v['tipo_servicio']), 0, 1);
$pdf->Cell(95, 6, 'ESTATUS: ' . strtoupper($v['estatus']), 0, 1);
$pdf->Ln(5);

// TITULAR
$pdf->SectionTitle('Información del Titular');
$pdf->InfoRow('Nombre Completo:', $v['nombre_titular']);
$pdf->InfoRow('Dirección:', $v['calle'] . ' #' . $v['numero_exterior'] . ($v['numero_interior'] ? ' Int. ' . $v['numero_interior'] : ''));
$pdf->InfoRow('Ubicación:', $v['colonia'] . ', ' . $v['delegacion_municipio'] . ' CP: ' . $v['codigo_postal']);
$pdf->InfoRow('Teléfonos:', $v['telefono'] . ($v['celular'] ? ' / ' . $v['celular'] : ''));
$pdf->InfoRow('Correo Electrónico:', $v['correo_electronico'] ?: 'No registrado');
$pdf->InfoRow('Tipo de Vivienda:', ucfirst($v['tipo_vivienda']));
if($v['referencias']) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(50, 6, 'Referencias:', 0, 0);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 6, utf8_decode($v['referencias']));
}

// SERVICIO
$pdf->SectionTitle('Servicio Contratado');
$pdf->InfoRow('Paquete:', $v['paquete_contratado']);
if($v['numero_cuenta']) $pdf->InfoRow('No. Cuenta:', $v['numero_cuenta']);
if($v['puerto']) $pdf->InfoRow('Puerto:', $v['puerto']);
if($v['placa']) $pdf->InfoRow('Placa:', $v['placa']);

// AQUÍ ESTÁ LA CORRECCIÓN DE LA IDENTIFICACIÓN
// Ahora simplemente imprimimos lo que guardaste (que ya trae el número concatenado)
$pdf->InfoRow('Identificación:', $v['identificacion'] ?: 'No especificada');
$pdf->InfoRow('Contrato Entregado:', $v['contrato_entregado'] ? 'SI' : 'NO');

// EQUIPOS
$pdf->SectionTitle('Equipos y Materiales');
$headers = [['EQUIPO', 50], ['MODELO', 50], ['SERIE / MAC', 50], ['OBSERVACIONES', 40]];
$pdf->TableHeader($headers);
$equipos = [
    [['ONT', 50], [$v['ont_modelo'] ?: 'N/A', 50], [$v['ont_serie'] ?: 'N/A', 50], ['Principal', 40]],
    [['OTRO', 50], [$v['otro_equipo_modelo'] ?: 'N/A', 50], [$v['otro_equipo_serie'] ?: 'N/A', 50], ['Adicional', 40]]
];
foreach($equipos as $equipo) {
    $pdf->TableRow($equipo);
}
$pdf->Ln(3);

// MATERIALES
if($v['materiales_utilizados']) {
    $materiales = json_decode($v['materiales_utilizados'], true);
    if(is_array($materiales) && count($materiales) > 0) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, utf8_decode('MATERIALES UTILIZADOS:'), 0, 1);
        $pdf->SetFont('Arial', '', 9);
        foreach($materiales as $item) {
            if (is_array($item) && isset($item['material'])) {
                $texto = utf8_decode($item['material'] . ": " . $item['cantidad']);
                $pdf->Cell(0, 5, chr(149) . " " . $texto, 0, 1);
            }
        }
        $pdf->Ln(2);
    }
}

// NOTAS Y LEGAL
$pdf->SectionTitle('Notas de Instalación');
$pdf->SetFont('Arial', '', 9);
$notas_legales = [
    "Es responsabilidad del contratante obtener los permisos necesarios.",
    "El cliente asignará a un mayor de 18 años para aprobar instalación.",
    "Servicio sujeto a cobertura.",
    "No incluye cableado telefónico o red de datos.",
    "No incluyeconfiguración de equipos depropiedad de cliente."
];
foreach ($notas_legales as $nota) {
    $pdf->SetX(15);
    $pdf->Cell(5, 5, chr(149), 0, 0);
    $pdf->MultiCell(170, 5, utf8_decode($nota));
}
if($v['notas_instalacion']) {
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Write(5, "Observaciones: ");
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 5, utf8_decode($v['notas_instalacion']));
}

// Forzar nueva página si no hay espacio suficiente
if($pdf->GetY() > 220) $pdf->AddPage();

$pdf->SectionTitle('Términos de Conformidad');
$pdf->SetFont('Arial', '', 8);

// ==========================================
// PÁRRAFO 1: Texto Legal General
// ==========================================
// Nota: Todo el texto está en una sola línea de código para evitar saltos raros
$texto_legal = "Por medio de la presente manifiesto de conformidad que las actividades de instalación del Servicio Bdigital en mi domicilio son de mi entera satisfacción, adicional manifiesto que de forma enunciativa más no limitativa los equipos, computadora, instalación eléctrica, así como el mobiliario de mi propiedad, se encuentran en buen estado sin sufrir alteración alguna y funcionando adecuadamente, así como doy fe que todos los bienes de mi propiedad ubicados en las zonas de trabajo donde se instaló el servicio, se encuentran en el mismo lugar de origen y no se reportan faltantes.";

// Usamos MultiCell para que quede JUSTIFICADO (alineado a ambos lados)
$pdf->MultiCell(0, 4, utf8_decode($texto_legal), 0, 'J');

$pdf->Ln(3); // Espacio entre párrafos

// ==========================================
// PÁRRAFO 2: Titular con Negritas
// ==========================================

// 1. "El titular "
$pdf->Write(5, utf8_decode("El titular "));

// 2. NOMBRE (En Negrita)
$pdf->SetFont('Arial', 'B', 8);
$pdf->Write(5, utf8_decode(strtoupper($v['nombre_titular'])));

// 3. Resto del texto (Normal)
$pdf->SetFont('Arial', '', 8);

// IMPORTANTE: Este texto no tiene 'Enters' dentro de las comillas para que fluya continuo
$texto_resto = " manifiesta de acuerdo en la instalación de cables, extensiones, decodificadores y demás accesorios y en caso de que modifique, corte y/o desconecte los equipos o instalaciones del cableado, Bdigital no se hace responsable de la calidad en la recepción del servicio hasta que el suscriptor reporte la falla para la atención y soporte correspondiente.";

$pdf->Write(5, utf8_decode($texto_resto));

$pdf->Ln(8); // Espacio final antes de la evaluación

// ==========================================
// EVALUACIÓN VISUAL (Colores y Botones)
// ==========================================
if($v['eval_servicios_explicados'] !== null) {
    if($pdf->GetY() > 210) $pdf->AddPage();
    
    $pdf->SectionTitle('Evaluación del Servicio');
    
    // Checkboxes Simples
    function printCheckRow($pdf, $text, $val) {
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(130, 6, utf8_decode($text), 0, 0);
        $pdf->Cell(8, 6, 'SI', 0, 0, 'R');
        $pdf->Rect($pdf->GetX()+1, $pdf->GetY()+1, 4, 4);
        if($val == 1) { $pdf->SetFont('Arial','B',9); $pdf->Text($pdf->GetX()+1.5, $pdf->GetY()+4, 'X'); }
        $pdf->SetX($pdf->GetX()+6);
        $pdf->Cell(8, 6, 'NO', 0, 0, 'R');
        $pdf->Rect($pdf->GetX()+1, $pdf->GetY()+1, 4, 4);
        if($val == 0) { $pdf->SetFont('Arial','B',9); $pdf->Text($pdf->GetX()+1.5, $pdf->GetY()+4, 'X'); }
        $pdf->Ln(7);
    }
    
    printCheckRow($pdf, '1. ¿El instalador explicó los servicios contratados?', $v['eval_servicios_explicados']);
    printCheckRow($pdf, '2. ¿El instalador entregó el manual de bienvenida?', $v['eval_manual_entregado']);
    
    $pdf->Ln(3);

    // --- FUNCIÓN PARA DIBUJAR ETIQUETAS DE COLORES ---
    function drawColorRating($pdf, $titulo, $valor_bd) {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 6, utf8_decode($titulo), 0, 1);
        $pdf->Ln(1);

        $opciones = [
            'excelente' => ['r'=>46,  'g'=>204, 'b'=>113], // Verde Esmeralda
            'bueno'     => ['r'=>52,  'g'=>152, 'b'=>219], // Azul
            'regular'   => ['r'=>243, 'g'=>156, 'b'=>18],  // Naranja
            'malo'      => ['r'=>231, 'g'=>76,  'b'=>60]   // Rojo
        ];

        $anchoBtn = 35;
        $altoBtn = 8;
        $margenX = 25; // Centrado
        $pdf->SetX($margenX);

        foreach($opciones as $nombre => $color) {
            $seleccionado = (strtolower($valor_bd) == $nombre);
            
            if($seleccionado) {
                // Si está seleccionado: Color Fuerte y Texto Blanco
                $pdf->SetFillColor($color['r'], $color['g'], $color['b']);
                $pdf->SetTextColor(255);
                $pdf->SetFont('Arial', 'B', 8);
                // Borde negro para resaltar
                $pdf->SetDrawColor(0);
            } else {
                // No seleccionado: Gris claro y Texto Gris
                $pdf->SetFillColor(245, 245, 245);
                $pdf->SetTextColor(150);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetDrawColor(200);
            }

            // Dibujar Celda
            $pdf->Cell($anchoBtn, $altoBtn, strtoupper($nombre), 1, 0, 'C', true);
            
            // Separación
            $pdf->SetX($pdf->GetX() + 2);
        }
        // Resetear estilos
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0);
        $pdf->Ln(12);
    }

    drawColorRating($pdf, '3. Trato recibido por el instalador:', $v['eval_trato_recibido']);
    drawColorRating($pdf, '4. Eficiencia en la instalación:', $v['eval_eficiencia']);
}

// FIRMAS
if($pdf->GetY() > 240) $pdf->AddPage();
$pdf->SetY(-40);

$pdf->SetDrawColor(0);
$pdf->Line(20, $pdf->GetY(), 90, $pdf->GetY());
$pdf->Line(120, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(80, 4, 'FIRMA DEL CLIENTE', 0, 0, 'C');
$pdf->Cell(20, 4, '', 0, 0);
$pdf->Cell(80, 4, 'FIRMA DEL TECNICO', 0, 1, 'C');

$pdf->SetFont('Arial', '', 7);
$pdf->Ln(4);
$pdf->Cell(80, 3, utf8_decode($v['nombre_titular']), 0, 0, 'C');
$pdf->Cell(20, 3, '', 0, 0);
$pdf->Cell(80, 3, utf8_decode($v['instalador_nombre']), 0, 1, 'C');

if($v['instalador_numero']) {
    $pdf->Ln(3);
    $pdf->Cell(100, 3, '', 0, 0);
    $pdf->Cell(80, 3, 'No. Empleado: ' . $v['instalador_numero'], 0, 1, 'C');
}

$pdf->Output('I', 'Orden_' . $v['folio'] . '.pdf');
if(file_exists($qrFile)) unlink($qrFile);
?>