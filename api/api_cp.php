<?php
header('Content-Type: application/json');

// 1. Conexión a la BD (Ajusta tus credenciales si es necesario)
$conn = new mysqli("localhost", "root", "", "bdigital_ventas");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}
$conn->set_charset("utf8mb4");

// 2. Obtener el CP enviado por el JS
$cp = isset($_GET['cp']) ? $conn->real_escape_string($_GET['cp']) : '';

if (strlen($cp) === 5) {
    // 3. Buscar en la tabla que acabas de llenar
    $sql = "SELECT colonia, municipio, estado FROM codigos_postales WHERE codigo_postal = '$cp'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $colonias = [];
        $municipio = '';
        $estado = '';

        // Recorremos los resultados
        while($row = $result->fetch_assoc()) {
            $colonias[] = $row['colonia'];
            // Asignamos municipio y estado (siempre son los mismos para el mismo CP)
            $municipio = $row['municipio'];
            $estado = $row['estado'];
        }

        // 4. Devolvemos el JSON tal como lo espera tu JS
        echo json_encode([
            'encontrado' => true,
            'estado' => $estado,
            'municipio' => $municipio,
            'colonias' => $colonias
        ]);
    } else {
        echo json_encode(['encontrado' => false]);
    }
} else {
    echo json_encode(['encontrado' => false]);
}

$conn->close();
?>