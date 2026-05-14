<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="navbar">
    <a href="index.php">Inicio</a>

    <?php if (isset($_SESSION['cliente_id'])) { ?>
        <a href="crear.php">Crear ticket</a>
        <a href="ver.php">Mis tickets</a>
        <a href="logout_cliente.php" class="logout">Cerrar sesión</a>
    <?php } else { ?>
        <a href="registro.php">Registrarse</a>
        <a href="login_cliente.php">Login cliente</a>
        <a href="login.php">Administración</a>
        <a href="login_tecnico.php">Técnicos</a>
    <?php } ?>
</div>
