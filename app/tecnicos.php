<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
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

$mensaje_reglas_password = "La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y al menos un número o un carácter especial.";

if (isset($_POST['crear_tecnico'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if ($nombre === '' || $usuario === '' || $password === '' || $confirmar_password === '') {
        $error = "Nombre, usuario y contraseña son obligatorios.";
    } elseif ($password !== $confirmar_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (!passwordTecnicoSegura($password)) {
        $error = $mensaje_reglas_password;
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $email_bd = $email !== '' ? $email : null;

            $stmt = $conexion->prepare("INSERT INTO tecnicos (nombre, usuario, email, password, activo, debe_cambiar_password) VALUES (?, ?, ?, ?, 1, 1)");
            $stmt->bind_param("ssss", $nombre, $usuario, $email_bd, $hash);
            $stmt->execute();

            header("Location: tecnicos.php?creado=ok");
            exit();
        } catch (Exception $e) {
            $error = "Error al crear el técnico: " . $e->getMessage();
        }
    }
}

if (isset($_POST['cambiar_password'])) {
    $id_tecnico = (int) ($_POST['id_tecnico'] ?? 0);
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_nueva_password = $_POST['confirmar_nueva_password'] ?? '';

    if ($id_tecnico <= 0) {
        $error = "Técnico inválido.";
    } elseif ($nueva_password === '' || $confirmar_nueva_password === '') {
        $error = "Debes introducir y confirmar la nueva contraseña.";
    } elseif ($nueva_password !== $confirmar_nueva_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (!passwordTecnicoSegura($nueva_password)) {
        $error = $mensaje_reglas_password;
    } else {
        try {
            $hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("UPDATE tecnicos SET password = ?, debe_cambiar_password = 1 WHERE id = ?");
            $stmt->bind_param("si", $hash, $id_tecnico);
            $stmt->execute();

            header("Location: tecnicos.php?password=ok");
            exit();
        } catch (Exception $e) {
            $error = "Error al cambiar la contraseña: " . $e->getMessage();
        }
    }
}

if (isset($_POST['cambiar_estado'])) {
    $id_tecnico = (int) ($_POST['id_tecnico'] ?? 0);
    $nuevo_estado = (int) ($_POST['nuevo_estado'] ?? 0);

    try {
        $stmt = $conexion->prepare("UPDATE tecnicos SET activo = ? WHERE id = ?");
        $stmt->bind_param("ii", $nuevo_estado, $id_tecnico);
        $stmt->execute();

        header("Location: tecnicos.php");
        exit();
    } catch (Exception $e) {
        $error = "Error al cambiar el estado del técnico: " . $e->getMessage();
    }
}

if (isset($_POST['eliminar_tecnico'])) {
    $id_tecnico = (int) ($_POST['id_tecnico'] ?? 0);

    try {
        $stmt = $conexion->prepare("DELETE FROM tecnicos WHERE id = ?");
        $stmt->bind_param("i", $id_tecnico);
        $stmt->execute();

        header("Location: tecnicos.php");
        exit();
    } catch (Exception $e) {
        $error = "Error al eliminar el técnico: " . $e->getMessage();
    }
}

try {
    $resultado = $conexion->query("SELECT * FROM tecnicos ORDER BY nombre ASC");
} catch (Exception $e) {
    die("Error al cargar técnicos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Técnicos</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include("nav_admin.php"); ?>

<div class="container">
    <h1>Gestión de Técnicos</h1>

    <?php if (!empty($_GET['creado']) && $_GET['creado'] === 'ok') { ?>
        <p class="success">Técnico creado correctamente.</p>
    <?php } ?>

    <?php if (!empty($_GET['password']) && $_GET['password'] === 'ok') { ?>
        <p class="success">Contraseña actualizada correctamente.</p>
    <?php } ?>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <div class="form-admin">
        <h2>Añadir técnico</h2>
        <form method="POST">
            <label>Nombre del técnico</label>
            <input type="text" name="nombre" required>

            <label>Usuario de acceso</label>
            <input type="text" name="usuario" required>

            <label>Email</label>
            <input type="email" name="email">

            <div class="password-wrapper">
                <label>Contraseña</label>
                <input type="password" name="password" id="password_tecnico" required>
                <button type="button" class="toggle-password" onclick="togglePassword('password_tecnico', this)">Mostrar</button>
            </div>

            <div class="password-wrapper">
                <label>Confirmar contraseña</label>
                <input type="password" name="confirmar_password" id="confirmar_password_tecnico" required>
                <button type="button" class="toggle-password" onclick="togglePassword('confirmar_password_tecnico', this)">Mostrar</button>
            </div>

            <p class="subtexto">La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula y al menos un número o un carácter especial. El técnico tendrá que cambiarla en su primer inicio de sesión.</p>

            <button type="submit" name="crear_tecnico">Añadir técnico</button>
        </form>
    </div>

    <h2>Listado de técnicos</h2>

    <div class="tabla-responsive">
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Cambio obligatorio</th>
                <th>Acciones</th>
            </tr>

            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <?php
                $activo = (int) ($fila['activo'] ?? 0);
                $clase_estado = $activo === 1 ? 'estado-cerrado' : 'prioridad-media';
                $texto_estado = $activo === 1 ? 'Activo' : 'Inactivo';
                $nuevo_estado = $activo === 1 ? 0 : 1;
                $texto_boton = $activo === 1 ? 'Desactivar' : 'Activar';
                ?>
                <tr>
                    <td><?= $fila['id'] ?></td>
                    <td><?= htmlspecialchars($fila['nombre'] ?? '') ?></td>
                    <td><?= htmlspecialchars($fila['usuario'] ?? 'Sin usuario') ?></td>
                    <td><?= htmlspecialchars($fila['email'] ?? 'Sin email') ?></td>
                    <td>
                        <span class="<?= $clase_estado ?>"><?= $texto_estado ?></span>
                    </td>
                    <td>
                        <?php if (!empty($fila['debe_cambiar_password'])) { ?>
                            <span class="prioridad-alta">Sí</span>
                        <?php } else { ?>
                            <span class="estado-cerrado">No</span>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="acciones-admin">
                            <form method="POST">
                                <input type="hidden" name="id_tecnico" value="<?= $fila['id'] ?>">
                                <input type="hidden" name="nuevo_estado" value="<?= $nuevo_estado ?>">
                                <button type="submit" name="cambiar_estado" class="btn-small"><?= $texto_boton ?></button>
                            </form>

                            <form method="POST" class="form-inline-password">
                                <input type="hidden" name="id_tecnico" value="<?= $fila['id'] ?>">
                                <input type="password" name="nueva_password" placeholder="Nueva contraseña" required>
                                <input type="password" name="confirmar_nueva_password" placeholder="Confirmar" required>
                                <button type="submit" name="cambiar_password" class="btn-small">Cambiar contraseña</button>
                            </form>

                            <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este técnico?');">
                                <input type="hidden" name="id_tecnico" value="<?= $fila['id'] ?>">
                                <button type="submit" name="eliminar_tecnico" class="btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
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
