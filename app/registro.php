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

if (isset($_POST['registrar'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $empresa = trim($_POST['empresa'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if ($nombre === '' || $apellidos === '' || $email === '' || $empresa === '' || $password === '' || $confirmar_password === '') {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif ($password !== $confirmar_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (!passwordSegura($password)) {
        $error = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.";
    } else {
        try {
            $stmt = $conexion->prepare("SELECT id FROM clientes WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->fetch_assoc()) {
                $error = "Ya existe un cliente registrado con ese correo.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conexion->prepare("
                    INSERT INTO clientes (nombre, apellidos, email, password, empresa)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("sssss", $nombre, $apellidos, $email, $password_hash, $empresa);
                $stmt->execute();

                header("Location: login_cliente.php?registro=ok");
                exit();
            }
        } catch (Exception $e) {
            $error = "Error al registrar el cliente: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de cliente</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include("nav_public.php"); ?>

<div class="container">
    <h1>Registro de cliente</h1>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form method="POST" class="form-admin">
        <label>Nombre</label>
        <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">

        <label>Apellidos</label>
        <input type="text" name="apellidos" required value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>">

        <label>Correo electrónico</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label>Empresa</label>
        <input type="text" name="empresa" required value="<?= htmlspecialchars($_POST['empresa'] ?? '') ?>">

        <label>Contraseña</label>
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

        <button type="submit" name="registrar">Registrarse</button>
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
