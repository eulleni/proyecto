<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");

if (isset($_SESSION['cliente_id'])) {
    header("Location: ver.php");
    exit();
}

if (isset($_POST['login_cliente'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Debes rellenar todos los campos.";
    } else {
        try {
            $stmt = $conexion->prepare("SELECT * FROM clientes WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $cliente = $resultado->fetch_assoc();

            if ($cliente && password_verify($password, $cliente['password'])) {
                session_regenerate_id(true);

                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nombre'] = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellidos'] ?? ''));
                $_SESSION['cliente_email'] = $cliente['email'];
                $_SESSION['cliente_empresa'] = $cliente['empresa'];

                header("Location: ver.php");
                exit();
            } else {
                $error = "Correo o contraseña incorrectos.";
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
    <title>Login cliente</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include("nav_public.php"); ?>

<div class="container">
    <h1>Acceso de cliente</h1>

    <?php if (!empty($_GET['registro']) && $_GET['registro'] === 'ok') { ?>
        <p class="success">Registro completado correctamente. Ya puedes iniciar sesión.</p>
    <?php } ?>

    <?php if (!empty($_GET['reset']) && $_GET['reset'] === 'ok') { ?>
        <p class="success">La contraseña se ha actualizado correctamente.</p>
    <?php } ?>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form method="POST" class="form-admin">
        <label>Correo electrónico</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label>Contraseña</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" required>
            <button type="button" class="toggle-password" onclick="togglePassword('password', this)">Mostrar</button>
        </div>

        <div class="form-links">
            <a href="olvide_password.php">Olvidé la contraseña</a>
        </div>

        <button type="submit" name="login_cliente">Entrar</button>
    </form>
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
