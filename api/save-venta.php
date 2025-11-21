<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    try {
        $db = (new Database())->getConnection();
        
        // 1. Generar Folio usando el procedimiento almacenado
        $stmt = $db->query("CALL generar_folio(@folio)");
        $res = $db->query("SELECT @folio as folio")->fetch(PDO::FETCH_ASSOC);
        $folio = $res['folio'];
        
        // 2. Insertar Venta
        $sql = "INSERT INTO ventas (
            folio, usuario_id, tipo_servicio, fecha_servicio, paquete_contratado,
            nombre_titular, calle, numero_exterior, numero_interior, colonia, 
            delegacion_municipio, codigo_postal, estado, telefono, celular, 
            correo_electronico, ont_modelo, ont_serie
        ) VALUES (
            :folio, :uid, :ts, :fs, :pc, 
            :nom, :calle, :ne, :ni, :col, 
            :mun, :cp, :est, :tel, :cel, 
            :email, :ontm, :onts
        )"; // Nota: Añadí campo 'estado' a la BD si no existe, ignóralo en el insert si no modificaste la tabla
        
        // Ajuste rápido: Asegúrate que tu tabla tenga todos los campos que envías o ajusta este INSERT
        // Usaré los campos de tu SQL original. 'estado' no estaba en 'ventas' original, lo omito.
        
        $sql = "INSERT INTO ventas (
            folio, usuario_id, tipo_servicio, fecha_servicio, paquete_contratado,
            nombre_titular, calle, numero_exterior, numero_interior, colonia, 
            delegacion_municipio, codigo_postal, telefono, celular, 
            correo_electronico, ont_modelo, ont_serie
        ) VALUES (
            :folio, :uid, :ts, :fs, :pc, 
            :nom, :calle, :ne, :ni, :col, 
            :mun, :cp, :tel, :cel, 
            :email, :ontm, :onts
        )";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':folio' => $folio,
            ':uid' => $_SESSION['user_id'],
            ':ts' => $_POST['tipo_servicio'],
            ':fs' => $_POST['fecha_servicio'],
            ':pc' => $_POST['paquete_contratado'],
            ':nom' => strtoupper($_POST['nombre_titular']),
            ':calle' => $_POST['calle'],
            ':ne' => $_POST['numero_exterior'],
            ':ni' => $_POST['numero_interior'] ?? '',
            ':col' => $_POST['colonia'],
            ':mun' => $_POST['delegacion_municipio'],
            ':cp' => $_POST['codigo_postal'],
            ':tel' => $_POST['telefono'],
            ':cel' => $_POST['celular'] ?? '',
            ':email' => $_POST['correo_electronico'] ?? '',
            ':ontm' => $_POST['ont_modelo'] ?? '',
            ':onts' => $_POST['ont_serie'] ?? ''
        ]);

        echo json_encode(['success' => true, 'folio' => $folio, 'id' => $db->lastInsertId()]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
}
?>