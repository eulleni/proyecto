<?php
session_start();

unset($_SESSION['tecnico_id']);
unset($_SESSION['tecnico_nombre']);
unset($_SESSION['tecnico_usuario']);

header("Location: login_tecnico.php");
exit();
?>
