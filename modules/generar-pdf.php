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
        // Ajusta esta ruta si es necesario, preferiblemente usa ruta relativa
        $logoPath = '../assets/img-logo/bgital_logo_moderno.png'; 
        if(file_exists($logoPath)) $this->Image($logoPath, 10, 10, 40);
        
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 51, 102);
        $this->Cell(0, 10, utf8_decode('ORDEN DE SERVICIO BGITAL'), 0, 1, 'C');
        
        $this->SetDrawColor(0, 212, 255);
        $this->SetLineWidth(0.5);
        $this->Line(10, 30, 200, 30);
        $this->Ln(15);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' - BDIGITAL TELECOMUNICACIONES - ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
    
    function SectionTitle($title) {
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(230, 240, 255);
        $this->SetTextColor(0, 51, 102);
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
        $this->SetFillColor(0, 51, 102);
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

// ==========================================================
// PARTE 1: CONTRATO DE VENTA (Diseño Original)
// ==========================================================

$pdf->Image($qrFile, 165, 32, 25, 25);
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

$pdf->InfoRow('Identificación:', $v['identificacion'] ?: 'No especificada');
$pdf->InfoRow('Contrato Entregado:', $v['contrato_entregado'] ? 'SI' : 'NO');

// EQUIPOS
$pdf->SectionTitle('Equipos Asignados Inicialmente');
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

// NOTAS Y LEGAL
$pdf->SectionTitle('Notas de Instalación');
$pdf->SetFont('Arial', '', 9);
$notas_legales = [
    "Es responsabilidad del contratante obtener los permisos necesarios.",
    "El cliente asignará a un mayor de 18 años para aprobar instalación.",
    "Servicio sujeto a cobertura.",
    "No incluye cableado telefónico o red de datos."
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

// Forzar nueva página si falta espacio para firmas
if($pdf->GetY() > 220) $pdf->AddPage();

$pdf->SectionTitle('Términos de Conformidad');
$pdf->SetFont('Arial', '', 8);
$texto_legal = "Por medio de la presente manifiesto de conformidad que las actividades de instalación del Servicio Bdigital en mi domicilio son de mi entera satisfacción, adicional manifiesto que de forma enunciativa más no limitativa los equipos, computadora, instalación eléctrica, así como el mobiliario de mi propiedad, se encuentran en buen estado sin sufrir alteración alguna.";
$pdf->MultiCell(0, 4, utf8_decode($texto_legal), 0, 'J');
$pdf->Ln(3);

$pdf->Write(5, utf8_decode("El titular "));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Write(5, utf8_decode(strtoupper($v['nombre_titular'])));
$pdf->SetFont('Arial', '', 8);
$pdf->Write(5, utf8_decode(" manifiesta de acuerdo en la instalación de cables, extensiones, decodificadores y demás accesorios."));
$pdf->Ln(6);

// EVALUACIÓN DEL SERVICIO
if($v['eval_servicios_explicados'] !== null) {
    if($pdf->GetY() > 210) $pdf->AddPage();
    
    $pdf->SectionTitle('Evaluación del Servicio');
    
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
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 6, utf8_decode('3. Calificación del servicio recibido:'), 0, 1);
    
    // Botones de colores simulados
    function drawColorRating($pdf, $titulo, $valor_bd) {
        $opciones = [
            'excelente' => ['r'=>46,  'g'=>204, 'b'=>113], 
            'bueno'     => ['r'=>52,  'g'=>152, 'b'=>219],
            'regular'   => ['r'=>243, 'g'=>156, 'b'=>18],
            'malo'      => ['r'=>231, 'g'=>76,  'b'=>60]
        ];
        $anchoBtn = 30;
        $pdf->SetX(25);
        foreach($opciones as $nombre => $color) {
            $seleccionado = (strtolower($valor_bd) == $nombre);
            if($seleccionado) {
                $pdf->SetFillColor($color['r'], $color['g'], $color['b']);
                $pdf->SetTextColor(255);
                $pdf->SetDrawColor(0);
            } else {
                $pdf->SetFillColor(245, 245, 245);
                $pdf->SetTextColor(150);
                $pdf->SetDrawColor(200);
            }
            $pdf->Cell($anchoBtn, 7, strtoupper($nombre), 1, 0, 'C', true);
            $pdf->SetX($pdf->GetX() + 2);
        }
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0);
        $pdf->Ln(10);
    }
    drawColorRating($pdf, '', $v['eval_trato_recibido']);
}

// FIRMAS PAGINA 1
$pdf->SetY(-40);
$pdf->SetDrawColor(0);
$pdf->Line(20, $pdf->GetY(), 90, $pdf->GetY());
$pdf->Line(120, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(80, 4, 'FIRMA DEL CLIENTE', 0, 0, 'C');
$pdf->Cell(20, 4, '', 0, 0);
$pdf->Cell(80, 4, 'FIRMA DEL VENDEDOR', 0, 1, 'C');


// ==========================================================
// PARTE 2: REPORTE DE CIERRE TÉCNICO (MATERIALES Y FOTOS)
// ==========================================================
// Se agrega solo si hay materiales o fotos
$hayMateriales = !empty($v['materiales_utilizados']) && $v['materiales_utilizados'] != '[]';
$hayFotos = !empty($v['evidencia_fotos']) && $v['evidencia_fotos'] != '[]';

if ($hayMateriales || $hayFotos) {
    $pdf->AddPage();
    
    // CABECERA REPORTE TÉCNICO
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(0, 51, 102);
    $pdf->Cell(0, 10, utf8_decode('REPORTE TÉCNICO DE INSTALACIÓN'), 0, 1, 'C');
    $pdf->SetTextColor(0);
    
    $pdf->SetFont('Arial', '', 10);
    $fecha_cierre = $v['fecha_completada'] ? date('d/m/Y H:i', strtotime($v['fecha_completada'])) : 'N/A';
    $pdf->Cell(0, 6, utf8_decode('Fecha de Cierre: ' . $fecha_cierre), 0, 1, 'C');
    
    // DATOS DEL TÉCNICO
    if($v['instalador_nombre']) {
        $pdf->Ln(5);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 8, utf8_decode('  TÉCNICO RESPONSABLE'), 0, 1, 'L', true);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, utf8_decode('  Nombre: ' . $v['instalador_nombre']), 0, 1, 'L');
    }

    // MATERIALES
    if ($hayMateriales) {
        $pdf->SectionTitle('Materiales Utilizados');
        
        $materiales = json_decode($v['materiales_utilizados'], true);
        
        // Cabecera
        $pdf->SetFillColor(0, 51, 102);
        $pdf->SetTextColor(255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(140, 7, utf8_decode('DESCRIPCIÓN / MATERIAL'), 1, 0, 'L', true);
        $pdf->Cell(50, 7, utf8_decode('CANTIDAD'), 1, 1, 'C', true);
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Ln();

        foreach ($materiales as $mat) {
            $nombre = is_array($mat) ? ($mat['material'] ?? 'N/A') : 'Material';
            $cant = is_array($mat) ? ($mat['cantidad'] ?? '1') : '1';
            
            $pdf->Cell(140, 6, utf8_decode($nombre), 1);
            $pdf->Cell(50, 6, $cant, 1, 1, 'C');
            $pdf->Ln();
        }
    }

    // NOTAS TÉCNICAS
    if($v['notas_tecnico']) {
        $pdf->SectionTitle('Notas del Técnico');
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, utf8_decode($v['notas_tecnico']), 1, 'L');
    }

    // EVIDENCIA FOTOGRÁFICA
    if ($hayFotos) {
        $pdf->SectionTitle('Evidencia Fotográfica');
        $fotos = json_decode($v['evidencia_fotos'], true);
        
        $x_start = 15;
        $y_start = $pdf->GetY() + 5;
        $img_width = 55;
        $img_height = 55;
        $margin = 10;
        $count = 0;
        
        foreach ($fotos as $foto) {
            $ruta_img = '../assets/evidencias/' . $foto;
            
            if (file_exists($ruta_img)) {
                // Control salto de página si la imagen se sale
                if ($y_start + $img_height > 260) {
                    $pdf->AddPage();
                    $y_start = 20;
                    $x_start = 15;
                    $count = 0;
                }

                // Control salto de línea (3 fotos por fila)
                if ($count > 0 && $count % 3 == 0) {
                    $y_start += $img_height + $margin;
                    $x_start = 15;
                }
                
                $pdf->Image($ruta_img, $x_start, $y_start, $img_width, $img_height);
                $pdf->Rect($x_start, $y_start, $img_width, $img_height); // Marco
                
                $x_start += $img_width + $margin;
                $count++;
            }
        }
        // Mover el cursor abajo de las imágenes para lo que siga
        $pdf->SetY($y_start + $img_height + 10);
    }
    
    // FIRMA DE CONFORMIDAD FINAL
    // Asegurar que la firma no quede cortada entre páginas
    if($pdf->GetY() > 240) $pdf->AddPage();
    
    $pdf->SetY(-40);
    $pdf->SetDrawColor(0);
    $pdf->Line(60, $pdf->GetY(), 150, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 4, utf8_decode('FIRMA DE RECIBIDO / CLIENTE'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 4, utf8_decode('Acepto la instalación y los equipos en custodia'), 0, 1, 'C');
}

$pdf->Output('I', 'Orden_' . $v['folio'] . '.pdf');

if(file_exists($qrFile)) unlink($qrFile);
?>