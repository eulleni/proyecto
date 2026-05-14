<?php
session_start();

if (!isset($_SESSION['tecnico_id'])) {
    header("Location: login_tecnico.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");

function passwordTecnicoSegura(string $password): bool {
    return strlen($password) >= 8
        && preg_match('/[a-z]/', $password)
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[0-9\W_]/', $password);
}

$error = "";
$mensaje_ok = "";

if (isset($_POST['cambiar_password_tecnico'])) {
    $password_actual = $_POST['password_actual'] ?? '';
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    $id_tecnico = (int) $_SESSION['tecnico_id'];

    if ($password_actual === '' || $nueva_password === '' || $confirmar_password === '') {
        $error = "Debes rellenar todos los campos.";
    } elseif ($nueva_password !== $confirmar_password) {
        $error = "La nueva contraseña y la confirmación no coinciden.";
    } elseif (!passwordTecnicoSegura($nueva_password)) {
        $error = "La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y al menos un número o un carácter especial.";
    } elseif ($password_actual === $nueva_password) {
        $error = "La nueva contraseña debe ser distinta de la contraseña temporal.";
    } else {
        try {
            $stmt = $conexion->prepare("SELECT password FROM tecnicos WHERE id = ? AND activo = 1");
            $stmt->bind_param("i", $id_tecnico);
            $stmt->execute();
            $tecnico = $stmt->get_result()->fetch_assoc();

            if (!$tecnico || !password_verify($password_actual, $tecnico['password'])) {
                $error = "La contraseña actual no es correcta.";
            } else {
                $hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                $stmt_update = $conexion->prepare("UPDATE tecnicos SET password = ?, debe_cambiar_password = 0 WHERE id = ?");
                $stmt_update->bind_param("si", $hash, $id_tecnico);
                $stmt_update->execute();

                $_SESSION['tecnico_debe_cambiar_password'] = 0;

                header("Location: panel_tecnico.php?password_cambiada=ok");
                exit();
            }
        } catch (Exception $e) {
            $error = "Error al cambiar la contraseña: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar contraseña - Técnico</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="container">
    <h1>Cambiar contraseña obligatoria</h1>
    <p class="subtexto">
        Por seguridad, debes cambiar la contraseña temporal antes de acceder al panel de técnicos.
    </p>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form method="POST" class="form-admin" autocomplete="off">
        <div class="password-wrapper">
            <label for="password_actual">Contraseña actual</label>
            <input type="password" name="password_actual" id="password_actual" required>
            <button type="button" class="toggle-password" onclick="togglePassword('password_actual', this)">Mostrar</button>
        </div>

        <div class="password-wrapper">
            <label for="nueva_password">Nueva contraseña</label>
            <input type="password" name="nueva_password" id="nueva_password" required>
            <button type="button" class="toggle-password" onclick="togglePassword('nueva_password', this)">Mostrar</button>
        </div>

        <div class="password-wrapper">
            <label for="confirmar_password">Confirmar nueva contraseña</label>
            <input type="password" name="confirmar_password" id="confirmar_password" required>
            <button type="button" class="toggle-password" onclick="togglePassword('confirmar_password', this)">Mostrar</button>
        </div>

        <p class="subtexto">
            La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y al menos un número o un carácter especial.
        </p>

        <button type="submit" name="cambiar_password_tecnico">Cambiar contraseña</button>
    </form>

    <p><a href="logout_tecnico.php">Cerrar sesión</a></p>
</div>

<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);

    if (input.type === "password") {
        input.type = "text";
        button.textContent = "Ocultar";
    } else {
        input.type = "password";
        button.textContent = "Mostrar";
    }
}
</script>

</body>
</html>
