<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="navbar">
    <a href="index.php">Panel</a>
    <a href="tecnicos.php">Técnicos</a>
    <a href="gestionar_admins.php">Administradores</a>
    <a href="logout.php" class="logout">Cerrar sesión</a>
</div>
