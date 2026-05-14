<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");

if (isset($_SESSION['tecnico_id'])) {
    if (!empty($_SESSION['tecnico_debe_cambiar_password'])) {
        header("Location: cambiar_password_tecnico.php");
        exit();
    }

    header("Location: panel_tecnico.php");
    exit();
}

$error = "";
$usuario = "";

if (isset($_POST['login_tecnico'])) {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = "Debes rellenar todos los campos.";
    } else {
        try {
            $stmt = $conexion->prepare("SELECT * FROM tecnicos WHERE usuario = ? AND activo = 1");
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $tecnico = $resultado->fetch_assoc();

            if ($tecnico && password_verify($password, $tecnico['password'])) {
                session_regenerate_id(true);
                $_SESSION['tecnico_id'] = $tecnico['id'];
                $_SESSION['tecnico_nombre'] = $tecnico['nombre'];
                $_SESSION['tecnico_usuario'] = $tecnico['usuario'];
                $_SESSION['tecnico_debe_cambiar_password'] = (int) ($tecnico['debe_cambiar_password'] ?? 0);

                if (!empty($_SESSION['tecnico_debe_cambiar_password'])) {
                    header("Location: cambiar_password_tecnico.php");
                    exit();
                }

                header("Location: panel_tecnico.php");
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos, o técnico inactivo.";
            }
        } catch (Exception $e) {
            $error = "Error al iniciar sesión: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login técnico</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include("nav_public.php"); ?>

<div class="container">
    <h1>Acceso técnicos</h1>
    <p class="subtexto">Inicia sesión como técnico para ver incidencias y asignarte tickets pendientes.</p>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form method="POST" class="form-admin" autocomplete="on">
        <label for="usuario">Usuario</label>
        <input
            type="text"
            name="usuario"
            id="usuario"
            value="<?= htmlspecialchars($usuario) ?>"
            required
        >

        <div class="password-wrapper">
            <label for="password">Contraseña</label>
            <input
                type="password"
                name="password"
                id="password"
                required
            >
            <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                Mostrar
            </button>
        </div>

        <button type="submit" name="login_tecnico">Entrar</button>
    </form>
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
