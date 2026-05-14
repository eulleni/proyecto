<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");
include("mail_helper.php");

if (isset($_SESSION['cliente_id'])) {
    header("Location: ver.php");
    exit();
}

if (isset($_POST['enviar_enlace'])) {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = "Debes introducir tu correo electrónico.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } else {
        try {
            $stmt = $conexion->prepare("SELECT id, nombre, apellidos, email FROM clientes WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $cliente = $resultado->fetch_assoc();

            if ($cliente) {
                $token = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $conexion->prepare("UPDATE clientes SET reset_token = ?, reset_expira = ? WHERE id = ?");
                $stmt->bind_param("ssi", $token, $expira, $cliente['id']);
                $stmt->execute();

                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $enlace = 'http://' . $host . $basePath . '/restablecer_password.php?token=' . urlencode($token);

                $nombreCompleto = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellidos'] ?? ''));
                $asunto = 'Recuperación de contraseña - Ticketing';
                $mensajeCorreo = "Hola {$nombreCompleto},\n\n";
                $mensajeCorreo .= "Has solicitado restablecer tu contraseña.\n";
                $mensajeCorreo .= "Pulsa en este enlace o cópialo en el navegador:\n{$enlace}\n\n";
                $mensajeCorreo .= "Este enlace caduca en 1 hora.\n\n";
                $mensajeCorreo .= "Si no solicitaste este cambio, puedes ignorar este correo.";

                enviarCorreoSistema($cliente['email'], $asunto, $mensajeCorreo);
            }

            $success = "Si el correo existe en el sistema, se ha enviado un enlace para restablecer la contraseña.";
        } catch (Exception $e) {
            $error = "Error al procesar la solicitud: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Olvidé mi contraseña</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include("nav_public.php"); ?>

<div class="container">
    <h1>Recuperar contraseña</h1>
    <p class="subtexto">Introduce tu correo y te enviaremos un enlace para cambiar la contraseña.</p>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <?php if (!empty($success)) { ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php } ?>

    <form method="POST" class="form-admin">
        <label>Correo electrónico</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <button type="submit" name="enviar_enlace">Enviar enlace</button>
    </form>
</div>

</body>
</html>
