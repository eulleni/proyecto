<?php
include("mail_helper.php");

$ok = enviarCorreoSistema("destinatario.demo@example.local", "Prueba mail helper", "Si te ha llegado este correo, el helper de correo funciona correctamente.");

echo $ok ? "Correo enviado" : "No se ha podido enviar el correo";
?>
