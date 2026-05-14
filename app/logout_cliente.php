<?php
session_start();

unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nombre']);
unset($_SESSION['cliente_email']);
unset($_SESSION['cliente_empresa']);

header("Location: login_cliente.php");
exit();
?>
