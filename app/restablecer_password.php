<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");

if (isset($_SESSION['cliente_id'])) {
    header("Location: ver.php");
    exit();
}

function passwordSegura(string $password): bool {
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

$token = trim($_GET['token'] ?? '');
$cliente = null;

if ($token === '') {
    $error = "Token no válido.";
} else {
    try {
        $stmt = $conexion->prepare("SELECT id, reset_expira FROM clientes WHERE reset_token = ? LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $cliente = $resultado->fetch_assoc();

        if (!$cliente) {
            $error = "El enlace no es válido.";
        } elseif (empty($cliente['reset_expira']) || strtotime($cliente['reset_expira']) < time()) {
            $error = "El enlace ha caducado. Solicita uno nuevo.";
        }
    } catch (Exception $e) {
        $error = "Error al validar el enlace: " . $e->getMessage();
    }
}

if (!$error && isset($_POST['guardar_password'])) {
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if ($password === '' || $confirmar_password === '') {
        $error = "Debes rellenar todos los campos.";
    } elseif ($password !== $confirmar_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (!passwordSegura($password)) {
        $error = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.";
    } else {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("UPDATE clientes SET password = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?");
            $stmt->bind_param("si", $password_hash, $cliente['id']);
            $stmt->execute();

            header("Location: login_cliente.php?reset=ok");
            exit();
        } catch (Exception $e) {
            $error = "Error al actualizar la contraseña: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include("nav_public.php"); ?>

<div class="container">
    <h1>Restablecer contraseña</h1>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <?php if (empty($error) && $cliente) { ?>
        <form method="POST" class="form-admin">
            <label>Nueva contraseña</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('password', this)">Mostrar</button>
            </div>

            <label>Confirmar contraseña</label>
            <div class="password-wrapper">
                <input type="password" name="confirmar_password" id="confirmar_password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('confirmar_password', this)">Mostrar</button>
            </div>

            <p class="subtexto">
                La contraseña debe tener mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número.
            </p>

            <button type="submit" name="guardar_password">Guardar nueva contraseña</button>
        </form>
    <?php } ?>
</div>

<script>
function togglePassword(id, button) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = 'Ocultar';
    } else {
        input.type = 'password';
        button.textContent = 'Mostrar';
    }
}
</script>

</body>
</html>
