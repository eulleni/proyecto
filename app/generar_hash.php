<?php
$password_demo = $argv[1] ?? "CambiaEstaPasswordDemo123!";
echo password_hash($password_demo, PASSWORD_DEFAULT);
?>
