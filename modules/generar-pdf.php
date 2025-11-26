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

// ==========================================================
// MODIFICACIÓN A: OBTENER DATOS DE LA EMPRESA (CONFIGURACIÓN)
// ==========================================================
$stmtEmpresa = $db->query("SELECT * FROM configuracion_empresa LIMIT 1");
$empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

// Variables globales para usar dentro de la clase PDF
$emp_nombre = $empresa['nombre_empresa'] ?? 'BGITAL TELECOMUNICACIONES';
$emp_dir    = $empresa['direccion'] ?? 'Dirección no configurada';
$emp_tel    = $empresa['telefono_contacto'] ?? '';
$emp_logo   = $empresa['logo_path'] ?? 'assets/img-logo/bgital_logo_moderno.png';
// ==========================================================

// 1. GENERAR QR
$tempDir = '../assets/img/temp/';
if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);
$qrContent = "FOLIO: " . $v['folio'] . "\nCLIENTE: " . $v['nombre_titular'] . "\nPLAN: " . $v['paquete_contratado'];
$qrFile = $tempDir . 'qr_' . $v['folio'] . '.png';
QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 3);

// 2. CLASE PDF
class PDF extends FPDF {
    
    // ==========================================================
    // MODIFICACIÓN B: HEADER DINÁMICO
    // ==========================================================
    function Header() {
        // Traemos las variables de la empresa usando 'global'
        global $emp_nombre, $emp_dir, $emp_tel, $emp_logo; 
        
        // 1. LOGO DINÁMICO
        // La ruta viene de la BD (ej: assets/img/logo.png), le agregamos '../' porque estamos en modules/
        $logoPath = '../' . $emp_logo; 
        
        // Verificamos si existe la imagen, si no, no la ponemos para evitar error
        if(file_exists($logoPath)) {
            $this->Image($logoPath, 10, 10, 35); 
        }
        
        // 2. DATOS DE LA EMPRESA (Alineados a la derecha)
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(0, 51, 102);
        
        // Mover cursor a la derecha para el título (X=50, Y=10)
        $this->SetXY(50, 10); 
        $this->Cell(0, 6, utf8_decode($emp_nombre), 0, 1, 'R');
        
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(80); // Color gris oscuro
        
        // Dirección
        $this->SetX(50);
        $this->Cell(0, 4, utf8_decode($emp_dir), 0, 1, 'R');
        
        // Teléfono (si existe)
        if($emp_tel) {
            $this->SetX(50);
            $this->Cell(0, 4, utf8_decode('Tel: ' . $emp_tel), 0, 1, 'R');
        }
        
        // Título del Documento y Línea divisoria
        $this->Ln(8); // Salto de línea
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0);
        $this->Cell(0, 10, utf8_decode('ORDEN DE SERVICIO Y CONTRATO'), 0, 1, 'C');
        
        $this->SetDrawColor(0, 51, 102);
        $this->SetLineWidth(0.5);
        $this->Line(10, 42, 200, 42); // Línea horizontal azul
        $this->Ln(5);
    }
    // ==========================================================

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        global $emp_nombre; // Usamos el nombre de la empresa también en el footer
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' - ' . utf8_decode($emp_nombre) . ' - ' . date('d/m/Y H:i'), 0, 0, 'C');
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
        $this->Cell(0, 5, utf8_decode($value ?: 'No especificado'), 0, 1);
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
// INFORMACIÓN GENERAL
// ==========================================================
$pdf->Image($qrFile, 165, 45, 25, 25); // Ajusté un poco la posición Y del QR
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 6, 'FOLIO: ' . $v['folio'], 0, 1);
$pdf->Cell(95, 6, 'FECHA SERVICIO: ' . date('d/m/Y', strtotime($v['fecha_servicio'])), 0, 1);
$pdf->Cell(95, 6, 'TIPO SERVICIO: ' . strtoupper($v['tipo_servicio']), 0, 1);
$pdf->Cell(95, 6, 'ESTATUS: ' . strtoupper($v['estatus']), 0, 1);
$pdf->Ln(5);

// ==========================================================
// DATOS DEL TITULAR
// ==========================================================
$pdf->SectionTitle('Información del Titular');
$pdf->InfoRow('Nombre Completo:', $v['nombre_titular']);
$pdf->InfoRow('Dirección Completa:', $v['calle'] . ' #' . $v['numero_exterior'] . 
    ($v['numero_interior'] ? ' Int. ' . $v['numero_interior'] : ''));
$pdf->InfoRow('Colonia:', $v['colonia']);
$pdf->InfoRow('Municipio/Delegación:', $v['delegacion_municipio']);
$pdf->InfoRow('Estado:', $v['estado']);
$pdf->InfoRow('Código Postal:', $v['codigo_postal']);
$pdf->InfoRow('Teléfono Casa:', $v['telefono']);
$pdf->InfoRow('Celular:', $v['celular']);
$pdf->InfoRow('Correo Electrónico:', $v['correo_electronico']);
$pdf->InfoRow('Tipo de Vivienda:', ucfirst($v['tipo_vivienda']) . 
    ($v['tipo_vivienda_otro'] ? ' - ' . $v['tipo_vivienda_otro'] : ''));

if($v['referencias']) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(50, 6, 'Referencias:', 0, 0);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 6, utf8_decode($v['referencias']));
}

// ==========================================================
// SERVICIO CONTRATADO
// ==========================================================
$pdf->SectionTitle('Servicio Contratado');
$pdf->InfoRow('Paquete Contratado:', $v['paquete_contratado']);
$pdf->InfoRow('Tipo de Promoción:', $v['tipo_promocion']);
$pdf->InfoRow('Número de Cuenta:', $v['numero_cuenta']);
$pdf->InfoRow('Puerto:', $v['puerto']);
$pdf->InfoRow('Placa:', $v['placa']);
$pdf->InfoRow('Tipo de Identificación:', $v['identificacion']);
$pdf->InfoRow('Número de Identificación:', $v['numero_identificacion']);
$pdf->InfoRow('Contrato Entregado:', $v['contrato_entregado'] ? 'SI' : 'NO');

// ==========================================================
// EQUIPOS INSTALADOS
// ==========================================================
$pdf->SectionTitle('Equipos Instalados');
$headers = [['TIPO', 40], ['MODELO', 60], ['SERIE / MAC', 60], ['ESTADO', 30]];
$pdf->TableHeader($headers);

$equipos = [
    [['ONT', 40], [$v['ont_modelo'] ?: 'N/A', 60], [$v['ont_serie'] ?: 'N/A', 60], ['Principal', 30]],
    [['OTRO EQUIPO', 40], [$v['otro_equipo_modelo'] ?: 'N/A', 60], [$v['otro_equipo_serie'] ?: 'N/A', 60], ['Adicional', 30]]
];

foreach($equipos as $equipo) {
    $pdf->TableRow($equipo);
}
$pdf->Ln(5);

// ==========================================================
// MATERIALES UTILIZADOS EN VENTA
// ==========================================================
if(!empty($v['materiales_utilizados']) && $v['materiales_utilizados'] != '[]') {
    $pdf->SectionTitle('Materiales Utilizados (Planificados)');
    
    $materiales_venta = json_decode($v['materiales_utilizados'], true);
    
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(140, 7, utf8_decode('MATERIAL'), 1, 0, 'L', true);
    $pdf->Cell(50, 7, utf8_decode('CANTIDAD'), 1, 1, 'C', true);
    $pdf->SetFont('Arial', '', 9);

    foreach($materiales_venta as $mat) {
        $nombre = is_array($mat) ? ($mat['material'] ?? 'N/A') : 'N/A';
        $cant = is_array($mat) ? ($mat['cantidad'] ?? '0') : '0';
        
        $pdf->Cell(140, 6, utf8_decode($nombre), 1);
        $pdf->Cell(50, 6, $cant, 1, 1, 'C');
    }
    $pdf->Ln(3);
}

// ==========================================================
// NOTAS DE INSTALACIÓN
// ==========================================================
if($v['notas_instalacion']) {
    $pdf->SectionTitle('Notas de Instalación');
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 5, utf8_decode($v['notas_instalacion']), 1, 'L');
    $pdf->Ln(3);
}

// ==========================================================
// DATOS DEL INSTALADOR
// ==========================================================
$pdf->SectionTitle('Datos del Instalador/Técnico');
$pdf->InfoRow('Nombre del Instalador:', $v['instalador_nombre']);
$pdf->InfoRow('Número del Instalador:', $v['instalador_numero']);

if($v['fecha_asignacion_tecnico']) {
    $pdf->InfoRow('Fecha Asignación:', date('d/m/Y H:i', strtotime($v['fecha_asignacion_tecnico'])));
}
if($v['fecha_completada']) {
    $pdf->InfoRow('Fecha Completada:', date('d/m/Y H:i', strtotime($v['fecha_completada'])));
}

// ==========================================================
// EVALUACIÓN DEL SERVICIO
// ==========================================================
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
    $pdf->Cell(0, 6, utf8_decode('3. Trato recibido:'), 0, 1);
    
    function drawColorRating($pdf, $valor_bd, $titulo) {
        $opciones = [
            'excelente' => ['r'=>46,  'g'=>204, 'b'=>113, 'label'=>'Excelente'], 
            'bueno'     => ['r'=>52,  'g'=>152, 'b'=>219, 'label'=>'Bueno'],
            'regular'   => ['r'=>243, 'g'=>156, 'b'=>18,  'label'=>'Regular'],
            'malo'      => ['r'=>231, 'g'=>76,  'b'=>60,  'label'=>'Malo']
        ];
        $anchoBtn = 30;
        
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(40, 6, utf8_decode($titulo), 0, 0);
        
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
            $pdf->Cell($anchoBtn, 6, utf8_decode($color['label']), 1, 0, 'C', true);
            $pdf->SetX($pdf->GetX() + 2);
        }
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(0);
        $pdf->Ln(8);
    }
    
    drawColorRating($pdf, $v['eval_trato_recibido'], 'Trato recibido:');
    drawColorRating($pdf, $v['eval_eficiencia'], 'Eficiencia:');
}

// ==========================================================
// TÉRMINOS LEGALES
// ==========================================================
if($pdf->GetY() > 220) $pdf->AddPage();

$pdf->SectionTitle('Términos y Condiciones');
$pdf->SetFont('Arial', '', 8);
$notas_legales = [
    "Es responsabilidad del contratante obtener los permisos necesarios para la instalación.",
    "El cliente asignará a un mayor de 18 años para aprobar la instalación.",
    "El servicio está sujeto a disponibilidad de cobertura en la zona.",
    "No incluye cableado telefónico o red de datos interna.",
    "Los equipos instalados quedan en custodia del cliente.",
    "El cliente se compromete a mantener los equipos en buen estado."
];

foreach ($notas_legales as $nota) {
    $pdf->SetX(15);
    $pdf->Cell(5, 5, chr(149), 0, 0);
    $pdf->MultiCell(170, 5, utf8_decode($nota));
}

$pdf->Ln(5);
$pdf->SetFont('Arial', '', 8);
$texto_legal = "Por medio de la presente manifiesto de conformidad que las actividades de instalación del Servicio en mi domicilio son de mi entera satisfacción. Adicional manifiesto que de forma enunciativa más no limitativa, los equipos, computadora, instalación eléctrica, así como el mobiliario de mi propiedad, se encuentran en buen estado sin sufrir alteración alguna.";
$pdf->MultiCell(0, 4, utf8_decode($texto_legal), 0, 'J');
$pdf->Ln(3);

$pdf->Write(5, utf8_decode("El titular "));
$pdf->SetFont('Arial', 'B', 8);
$pdf->Write(5, utf8_decode(strtoupper($v['nombre_titular'])));
$pdf->SetFont('Arial', '', 8);
$pdf->Write(5, utf8_decode(" manifiesta estar de acuerdo con la instalación de cables, extensiones, decodificadores y demás accesorios necesarios para la prestación del servicio."));
$pdf->Ln(6);

// FIRMAS PÁGINA 1
$pdf->SetY(-45);
$pdf->SetDrawColor(0);
$pdf->Line(20, $pdf->GetY(), 90, $pdf->GetY());
$pdf->Line(120, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(80, 4, 'FIRMA DEL CLIENTE', 0, 0, 'C');
$pdf->Cell(20, 4, '', 0, 0);
$pdf->Cell(80, 4, 'FIRMA DEL VENDEDOR', 0, 1, 'C');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(80, 4, utf8_decode($v['nombre_titular']), 0, 0, 'C');
$pdf->Cell(20, 4, '', 0, 0);
$pdf->Cell(80, 4, utf8_decode($v['instalador_nombre'] ?: 'BGITAL'), 0, 1, 'C');

// ==========================================================
// PÁGINA 2: REPORTE TÉCNICO (si existe)
// ==========================================================
$hayMaterialesTecnico = !empty($v['materiales_tecnicos']) && $v['materiales_tecnicos'] != '[]';
$hayFotos = !empty($v['evidencia_fotos']) && $v['evidencia_fotos'] != '[]';
$hayNotasTecnico = !empty($v['notas_tecnico']);

if ($hayMaterialesTecnico || $hayFotos || $hayNotasTecnico) {
    $pdf->AddPage();
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(0, 51, 102);
    $pdf->Cell(0, 10, utf8_decode('REPORTE TÉCNICO DE INSTALACIÓN'), 0, 1, 'C');
    $pdf->SetTextColor(0);
    
    $pdf->SetFont('Arial', '', 10);
    $fecha_cierre = $v['fecha_completada'] ? date('d/m/Y H:i', strtotime($v['fecha_completada'])) : 'Pendiente';
    $pdf->Cell(0, 6, utf8_decode('Fecha de Cierre: ' . $fecha_cierre), 0, 1, 'C');
    $pdf->Ln(3);
    
    // DATOS DEL TÉCNICO
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 8, utf8_decode('  TÉCNICO RESPONSABLE'), 0, 1, 'L', true);
    $pdf->SetFont('Arial', '', 9);
    $pdf->InfoRow('  Nombre:', $v['instalador_nombre']);
    $pdf->InfoRow('  Contacto:', $v['instalador_numero']);
    $pdf->Ln(3);

    // MATERIALES REALES UTILIZADOS
    if ($hayMaterialesTecnico) {
        $pdf->SectionTitle('Materiales Utilizados (Reporte Real del Técnico)');
        
        $materiales = json_decode($v['materiales_tecnicos'], true);
        
        $pdf->SetFillColor(0, 51, 102);
        $pdf->SetTextColor(255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(140, 7, utf8_decode('DESCRIPCIÓN DEL MATERIAL'), 1, 0, 'L', true);
        $pdf->Cell(50, 7, utf8_decode('CANTIDAD'), 1, 1, 'C', true);
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial', '', 9);

        foreach ($materiales as $mat) {
            $nombre = is_array($mat) ? ($mat['material'] ?? 'N/A') : $mat;
            $cant = is_array($mat) ? ($mat['cantidad'] ?? '1') : '1';
            
            $pdf->Cell(140, 6, utf8_decode($nombre), 1);
            $pdf->Cell(50, 6, $cant, 1, 1, 'C');
        }
        $pdf->Ln(5);
    }

    // NOTAS DEL TÉCNICO
    if($hayNotasTecnico) {
        $pdf->SectionTitle('Notas y Observaciones del Técnico');
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, utf8_decode($v['notas_tecnico']), 1, 'L');
        $pdf->Ln(5);
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
                if ($y_start + $img_height > 260) {
                    $pdf->AddPage();
                    $y_start = 20;
                    $x_start = 15;
                    $count = 0;
                }

                if ($count > 0 && $count % 3 == 0) {
                    $y_start += $img_height + $margin;
                    $x_start = 15;
                }
                
                $pdf->Image($ruta_img, $x_start, $y_start, $img_width, $img_height);
                $pdf->Rect($x_start, $y_start, $img_width, $img_height);
                
                $x_start += $img_width + $margin;
                $count++;
            }
        }
        $pdf->SetY($y_start + $img_height + 10);
    }
    
    // FIRMA DE CONFORMIDAD FINAL
    if($pdf->GetY() > 240) $pdf->AddPage();
    
    $pdf->SetY(-45);
    $pdf->SetDrawColor(0);
    $pdf->Line(60, $pdf->GetY(), 150, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 4, utf8_decode('FIRMA DE RECIBIDO / CLIENTE'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 4, utf8_decode('Acepto la instalación y los equipos en custodia'), 0, 1, 'C');
    $pdf->Cell(0, 4, utf8_decode($v['nombre_titular']), 0, 1, 'C');
}

$pdf->Output('I', 'Orden_' . $v['folio'] . '.pdf');

if(file_exists($qrFile)) unlink($qrFile);
?>