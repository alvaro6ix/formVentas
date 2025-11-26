<?php
header('Content-Type: application/json');
session_start();

// Ajusta la ruta si tu archivo config está en otro nivel
require_once '../config/database.php';
require_once '../includes/functions.php'; // 1. Esto ya lo tenías, es correcto

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    try {
        $db = (new Database())->getConnection();
        
        // 1. Generar Folio usando el Stored Procedure
        $stmt = $db->query("CALL generar_folio(@folio)");
        $res = $db->query("SELECT @folio as folio")->fetch(PDO::FETCH_ASSOC);
        $folio = $res['folio'];
        
        // 2. Query de Inserción COMPLETO
        $sql = "INSERT INTO ventas (
            folio, usuario_id, numero_cuenta, fecha_servicio, puerto, placa,
            tipo_servicio, nombre_titular, calle, numero_interior, numero_exterior,
            colonia, delegacion_municipio, estado, codigo_postal, telefono, celular,
            tipo_vivienda, referencias, paquete_contratado, tipo_promocion,
            correo_electronico, identificacion, numero_identificacion, contrato_entregado,
            ont_modelo, ont_serie, otro_equipo_modelo, otro_equipo_serie,
            notas_instalacion, instalador_nombre, instalador_numero, 
            eval_servicios_explicados, eval_manual_entregado, eval_trato_recibido, eval_eficiencia,
            materiales_utilizados
        ) VALUES (
            :folio, :uid, :num_cuenta, :fs, :puerto, :placa,
            :ts, :nom, :calle, :ni, :ne,
            :col, :mun, :est, :cp, :tel, :cel,
            :tv, :ref, :pc, :promo,
            :email, :ident, :num_ident, :contrato,
            :ontm, :onts, :otrom, :otros,
            :notas, :inst_nom, :inst_num,
            :ev_serv, :ev_man, :ev_trato, :ev_efi,
            :materiales
        )";

        $stmt = $db->prepare($sql);
        
        // Ejecución con mapeo
        $stmt->execute([
            ':folio'       => $folio,
            ':uid'         => $_SESSION['user_id'],
            ':num_cuenta'  => $_POST['numero_cuenta'] ?? null,
            ':fs'          => $_POST['fecha_servicio'],
            ':puerto'      => $_POST['puerto'] ?? null,
            ':placa'       => $_POST['placa'] ?? null,
            ':ts'          => $_POST['tipo_servicio'],
            ':nom'         => strtoupper($_POST['nombre_titular']),
            ':calle'       => $_POST['calle'],
            ':ni'          => $_POST['numero_interior'] ?? '',
            ':ne'          => $_POST['numero_exterior'],
            ':col'         => $_POST['colonia'],
            ':mun'         => $_POST['delegacion_municipio'],
            ':est'         => $_POST['estado'] ?? '', 
            ':cp'          => $_POST['codigo_postal'],
            ':tel'         => $_POST['telefono'],
            ':cel'         => $_POST['celular'] ?? '',
            ':tv'          => $_POST['tipo_vivienda'] ?? 'casa',
            ':ref'         => $_POST['referencias'] ?? '',
            ':pc'          => $_POST['paquete_contratado'],
            ':promo'       => $_POST['tipo_promocion'] ?? '',
            ':email'       => $_POST['correo_electronico'] ?? '',
            ':ident'       => $_POST['identificacion'] ?? '', 
            ':num_ident'   => $_POST['numero_identificacion'] ?? '',
            ':contrato'    => isset($_POST['contrato_entregado']) ? 1 : 0,
            ':ontm'        => $_POST['ont_modelo'] ?? '',
            ':onts'        => $_POST['ont_serie'] ?? '',
            ':otrom'       => $_POST['otro_equipo_modelo'] ?? '',
            ':otros'       => $_POST['otro_equipo_serie'] ?? '',
            ':notas'       => $_POST['notas_instalacion'] ?? '',
            ':inst_nom'    => $_POST['instalador_nombre'] ?? '',
            ':inst_num'    => $_POST['instalador_numero'] ?? '',
            ':ev_serv'     => $_POST['eval_servicios_explicados'] ?? null,
            ':ev_man'      => $_POST['eval_manual_entregado'] ?? null,
            ':ev_trato'    => $_POST['eval_trato_recibido'] ?? null,
            ':ev_efi'      => $_POST['eval_eficiencia'] ?? null,
            ':materiales'  => $_POST['materiales_utilizados'] ?? '[]'
        ]);

        // ======================================================
        // 2. AQUÍ ESTÁ EL CAMBIO IMPORTANTE: REGISTRO DEL LOG
        // ======================================================
        // Solo llegamos aquí si el execute funcionó (si falla, salta al catch)
        registrarLog($db, 'NUEVA_VENTA', "Se creó venta folio $folio para " . strtoupper($_POST['nombre_titular']));

        echo json_encode(['success' => true, 'folio' => $folio, 'id' => $db->lastInsertId()]);

    } catch (Exception $e) {
        // En producción podrías ocultar el getMessage()
        echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
}
?>