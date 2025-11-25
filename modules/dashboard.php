<?php
// modules/dashboard.php - EL ROUTER
require_once '../includes/header.php'; 

// Recuperar el rol de la sesión
$rol = $_SESSION['rol_id'] ?? 0;

// Lógica de Enrutamiento
switch($rol) {
    case 1: // Admin
    case 'admin':
        include 'dashboards/dashboard-admin.php';
        break;

    case 2: // Ventas
    case 'ventas':
        include 'dashboards/dashboard-ventas.php';
        break;

    case 3: // Despacho
    case 'despacho':
        include 'dashboards/dashboard-despacho.php';
        break;

    case 4: // Técnico
    case 'tecnico':
        include 'dashboards/dashboard-tecnicos.php';
        break;

    default:
        echo "<div class='container mt-5'><div class='alert alert-danger'>Rol no identificado. Contacte soporte.</div></div>";
        break;
}

require_once '../includes/footer.php'; 
?>