<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $db_host = getenv('DB_HOST') ?: 'localhost';
    $db_user = getenv('DB_USER') ?: 'usuario_ticketing';
    $db_pass = getenv('DB_PASSWORD') ?: 'CAMBIAR_PASSWORD_DB';
    $db_name = getenv('DB_NAME') ?: 'ticketing';

    $conexion = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conexion->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error de conexion a la base de datos: " . $e->getMessage());
}
?>
