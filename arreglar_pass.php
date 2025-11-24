<?php
// 1. Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "bdigital_ventas");

// Verificar conexión
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// 2. Definir usuario y contraseña EXACTOS
$usuario = "Bgital"; // Ojo con la mayúscula
$password_plano = "Bgital2025";

// 3. Generar el hash correctamente con TU servidor
$password_hash = password_hash($password_plano, PASSWORD_DEFAULT);

// 4. Actualizar en la base de datos
$sql = "UPDATE usuarios SET password = '$password_hash' WHERE usuario = '$usuario'";

if ($mysqli->query($sql) === TRUE) {
    echo "<h1>¡Éxito!</h1>";
    echo "<p>La contraseña para el usuario <b>$usuario</b> se ha restablecido a: <b>$password_plano</b></p>";
    echo "<p>El nuevo hash generado es: $password_hash</p>";
    echo "<br><a href='login.php'>Ir al Login</a>"; // Cambia login.php por tu archivo de inicio
} else {
    echo "Error actualizando: " . $mysqli->error;
}

$mysqli->close();
?>