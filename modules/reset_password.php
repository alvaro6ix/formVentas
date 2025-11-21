<?php
// modules/reset_password.php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$usuario = "BGITAL";
$nueva_password = "admin";

// 1. Encriptamos la contraseña "admin"
$password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

// 2. Actualizamos la base de datos
$stmt = $db->prepare("UPDATE usuarios SET password = :pass WHERE usuario = :user");
$stmt->bindParam(":pass", $password_hash);
$stmt->bindParam(":user", $usuario);

if($stmt->execute()) {
    echo "✅ Contraseña actualizada correctamente.<br>";
    echo "Usuario: " . $usuario . "<br>";
    echo "Nueva Contraseña: " . $nueva_password . "<br>";
    echo "<br><a href='login.php'>Ir al Login</a>";
} else {
    echo "❌ Error al actualizar.";
}
?>