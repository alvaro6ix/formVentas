<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    try {
        $db = (new Database())->getConnection();
        
        // 1. Generar Folio
        $stmt = $db->query("CALL generar_folio(@folio)");
        $res = $db->query("SELECT @folio as folio")->fetch(PDO::FETCH_ASSOC);
        $folio = $res['folio'];
        
        // 2. Query de Inserción COMPLETO (Todos los campos de tu BD)
        $sql = "INSERT INTO ventas (
            folio, usuario_id, numero_cuenta, fecha_servicio, puerto, placa,
            tipo_servicio, nombre_titular, calle, numero_interior, numero_exterior,
            colonia, delegacion_municipio, codigo_postal, telefono, celular,
            tipo_vivienda, referencias, paquete_contratado, tipo_promocion,
            correo_electronico, identificacion, contrato_entregado,
            ont_modelo, ont_serie, otro_equipo_modelo, otro_equipo_serie,
            notas_instalacion, instalador_nombre, materiales_utilizados
        ) VALUES (
            :folio, :uid, :num_cuenta, :fs, :puerto, :placa,
            :ts, :nom, :calle, :ni, :ne,
            :col, :mun, :cp, :tel, :cel,
            :tv, :ref, :pc, :promo,
            :email, :ident, :contrato,
            :ontm, :onts, :otrom, :otros,
            :notas, :inst_nom, :materiales
        )";

        $stmt = $db->prepare($sql);
        
        // Ejecución con mapeo de todos los datos
        $stmt->execute([
            ':folio' => $folio,
            ':uid' => $_SESSION['user_id'],
            ':num_cuenta' => $_POST['numero_cuenta'] ?? null,
            ':fs' => $_POST['fecha_servicio'],
            ':puerto' => $_POST['puerto'] ?? null,
            ':placa' => $_POST['placa'] ?? null,
            ':ts' => $_POST['tipo_servicio'],
            ':nom' => strtoupper($_POST['nombre_titular']),
            ':calle' => $_POST['calle'],
            ':ni' => $_POST['numero_interior'] ?? '',
            ':ne' => $_POST['numero_exterior'],
            ':col' => $_POST['colonia'],
            ':mun' => $_POST['delegacion_municipio'],
            ':cp' => $_POST['codigo_postal'],
            ':tel' => $_POST['telefono'],
            ':cel' => $_POST['celular'] ?? '',
            ':tv' => $_POST['tipo_vivienda'] ?? 'casa',
            ':ref' => $_POST['referencias'] ?? '',
            ':pc' => $_POST['paquete_contratado'],
            ':promo' => $_POST['tipo_promocion'] ?? '',
            ':email' => $_POST['correo_electronico'] ?? '',
            ':ident' => $_POST['identificacion'] ?? '',
            ':contrato' => isset($_POST['contrato_entregado']) ? 1 : 0,
            ':ontm' => $_POST['ont_modelo'] ?? '',
            ':onts' => $_POST['ont_serie'] ?? '',
            ':otrom' => $_POST['otro_equipo_modelo'] ?? '',
            ':otros' => $_POST['otro_equipo_serie'] ?? '',
            ':notas' => $_POST['notas_instalacion'] ?? '',
            ':inst_nom' => $_POST['instalador_nombre'] ?? '',
            ':materiales' => null // Enviamos NULL porque el JSON es complejo de armar desde un form simple
        ]);

        echo json_encode(['success' => true, 'folio' => $folio, 'id' => $db->lastInsertId()]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error DB: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
}
?>