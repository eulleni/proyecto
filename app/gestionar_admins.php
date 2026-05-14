<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");

$error = "";
$exito = "";

function passwordSegura(string $password): bool {
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

if (isset($_POST['crear_admin'])) {
    $nuevo_usuario = trim($_POST['nuevo_usuario'] ?? '');
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if ($nuevo_usuario === '' || $nueva_password === '' || $confirmar_password === '') {
        $error = "Debes rellenar todos los campos para crear un administrador.";
    } elseif ($nueva_password !== $confirmar_password) {
        $error = "Las contraseñas del nuevo administrador no coinciden.";
    } elseif (!passwordSegura($nueva_password)) {
        $error = "La contraseña del nuevo administrador debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.";
    } else {
        try {
            $stmt = $conexion->prepare("SELECT id FROM admins WHERE usuario = ?");
            $stmt->bind_param("s", $nuevo_usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $error = "Ya existe un administrador con ese usuario.";
            } else {
                $hash = password_hash($nueva_password, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("INSERT INTO admins (usuario, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $nuevo_usuario, $hash);
                $stmt->execute();
                $exito = "Administrador creado correctamente.";
            }
        } catch (Exception $e) {
            $error = "Error al crear el administrador: " . $e->getMessage();
        }
    }
}

if (isset($_POST['cambiar_password'])) {
    $admin_id_cambio = (int)($_POST['admin_id_cambio'] ?? 0);
    $password_cambio = $_POST['password_cambio'] ?? '';
    $confirmar_cambio = $_POST['confirmar_cambio'] ?? '';

    if ($admin_id_cambio <= 0 || $password_cambio === '' || $confirmar_cambio === '') {
        $error = "Debes rellenar todos los campos para cambiar la contraseña.";
    } elseif ($password_cambio !== $confirmar_cambio) {
        $error = "Las nuevas contraseñas no coinciden.";
    } elseif (!passwordSegura($password_cambio)) {
        $error = "La nueva contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.";
    } else {
        try {
            $hash = password_hash($password_cambio, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $admin_id_cambio);
            $stmt->execute();

            if ($stmt->affected_rows >= 0) {
                $exito = "Contraseña actualizada correctamente.";
            }
        } catch (Exception $e) {
            $error = "Error al cambiar la contraseña: " . $e->getMessage();
        }
    }
}

if (isset($_POST['eliminar_admin'])) {
    $admin_id_eliminar = (int)($_POST['admin_id_eliminar'] ?? 0);

    try {
        $resultado_total = $conexion->query("SELECT COUNT(*) AS total FROM admins");
        $total_admins = (int)$resultado_total->fetch_assoc()['total'];

        if ($admin_id_eliminar <= 0) {
            $error = "Administrador no válido.";
        } elseif ($admin_id_eliminar === (int)$_SESSION['admin_id']) {
            $error = "No puedes eliminar el administrador que tiene la sesión iniciada.";
        } elseif ($total_admins <= 1) {
            $error = "No se puede eliminar el único administrador del sistema.";
        } else {
            $stmt = $conexion->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id_eliminar);
            $stmt->execute();
            $exito = "Administrador eliminado correctamente.";
        }
    } catch (Exception $e) {
        $error = "Error al eliminar el administrador: " . $e->getMessage();
    }
}

try {
    $resultado_admins = $conexion->query("SELECT id, usuario FROM admins ORDER BY usuario ASC");
} catch (Exception $e) {
    die("Error al cargar administradores: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de administradores</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include("nav_admin.php"); ?>

<div class="container">
    <h1>Gestión de administradores</h1>
    <p class="subtexto">Desde aquí puedes crear administradores, cambiar contraseñas y eliminar cuentas de administración.</p>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <?php if (!empty($exito)) { ?>
        <p class="success"><?= htmlspecialchars($exito) ?></p>
    <?php } ?>

    <div class="cards-admin-grid">
        <div class="form-admin">
            <h2>Crear nuevo administrador</h2>
            <form method="POST" autocomplete="off">
                <label for="nuevo_usuario">Usuario</label>
                <input type="text" name="nuevo_usuario" id="nuevo_usuario" required>

                <div class="password-wrapper">
                    <label for="nueva_password">Contraseña</label>
                    <input type="password" name="nueva_password" id="nueva_password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('nueva_password', this)">Mostrar</button>
                </div>

                <div class="password-wrapper">
                    <label for="confirmar_password">Confirmar contraseña</label>
                    <input type="password" name="confirmar_password" id="confirmar_password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirmar_password', this)">Mostrar</button>
                </div>

                <p class="password-help">La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.</p>

                <button type="submit" name="crear_admin">Crear administrador</button>
            </form>
        </div>

        <div class="form-admin">
            <h2>Cambiar contraseña de administrador</h2>
            <form method="POST" autocomplete="off">
                <label for="admin_id_cambio">Administrador</label>
                <select name="admin_id_cambio" id="admin_id_cambio" required>
                    <option value="">Selecciona un administrador</option>
                    <?php
                    $resultado_admins->data_seek(0);
                    while ($admin = $resultado_admins->fetch_assoc()) {
                    ?>
                        <option value="<?= (int)$admin['id'] ?>"><?= htmlspecialchars($admin['usuario']) ?></option>
                    <?php } ?>
                </select>

                <div class="password-wrapper">
                    <label for="password_cambio">Nueva contraseña</label>
                    <input type="password" name="password_cambio" id="password_cambio" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password_cambio', this)">Mostrar</button>
                </div>

                <div class="password-wrapper">
                    <label for="confirmar_cambio">Confirmar nueva contraseña</label>
                    <input type="password" name="confirmar_cambio" id="confirmar_cambio" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirmar_cambio', this)">Mostrar</button>
                </div>

                <p class="password-help">Usa una contraseña segura y distinta de las anteriores.</p>

                <button type="submit" name="cambiar_password">Actualizar contraseña</button>
            </form>
        </div>
    </div>

    <div class="form-admin">
        <h2>Administradores existentes</h2>
        <div class="tabla-responsive">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                </tr>
                <?php
                $resultado_admins->data_seek(0);
                while ($admin = $resultado_admins->fetch_assoc()) {
                    $es_admin_actual = ((int)$admin['id'] === (int)$_SESSION['admin_id']);
                ?>
                <tr>
                    <td><?= (int)$admin['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($admin['usuario']) ?>
                        <?php if ($es_admin_actual) { ?>
                            <span class="badge-admin-actual">Sesión actual</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($es_admin_actual) { ?>
                            <span class="texto-suave">No se puede eliminar</span>
                        <?php } else { ?>
                            <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este administrador?');">
                                <input type="hidden" name="admin_id_eliminar" value="<?= (int)$admin['id'] ?>">
                                <button type="submit" name="eliminar_admin" class="btn-danger">Eliminar</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
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
