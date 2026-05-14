<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");

if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$usuario = "";

if (isset($_POST['login'])) {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = "Debes rellenar todos los campos.";
    } else {
        try {
            $stmt = $conexion->prepare("SELECT * FROM admins WHERE usuario = ?");
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $admin = $resultado->fetch_assoc();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];

                header("Location: index.php");
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos.";
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
    <title>Login administrador</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include("nav_public.php"); ?>

<div class="container">
    <h1>Acceso administración</h1>
    <p class="subtexto">Inicia sesión como administrador para gestionar tickets, técnicos y administradores.</p>

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

        <button type="submit" name="login">Entrar</button>
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
