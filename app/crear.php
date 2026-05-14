<?php
session_start();

if (!isset($_SESSION['cliente_id'])) {
    header("Location: login_cliente.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");
include("mail_helper.php");

if (isset($_POST['crear'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $persona_contacto = trim($_POST['persona_contacto'] ?? '');
    $telefono_contacto = trim($_POST['telefono_contacto'] ?? '');

    $sistema_valor = (int) ($_POST['sistema'] ?? 0);
    $usuarios_valor = (int) ($_POST['usuarios'] ?? 0);
    $bloqueo_valor = (int) ($_POST['bloqueo'] ?? 0);
    $solucion_valor = (int) ($_POST['solucion'] ?? 0);

    $sistema_texto = trim($_POST['sistema_texto'] ?? '');
    $usuarios_texto = trim($_POST['usuarios_texto'] ?? '');
    $bloqueo_texto = trim($_POST['bloqueo_texto'] ?? '');
    $solucion_texto = trim($_POST['solucion_texto'] ?? '');

    $cliente_id = (int) $_SESSION['cliente_id'];
    $empresa = $_SESSION['cliente_empresa'] ?? 'No indicada';

    $puntos = $sistema_valor + $usuarios_valor + $bloqueo_valor + $solucion_valor;

    if ($puntos <= 2) {
        $prioridad = "baja";
    } elseif ($puntos <= 5) {
        $prioridad = "media";
    } else {
        $prioridad = "alta";
    }

    if ($titulo === '' || $descripcion === '' || $persona_contacto === '' || $telefono_contacto === '') {
        $error = "Todos los campos son obligatorios.";
    } else {
        try {
            $stmt = $conexion->prepare("
                INSERT INTO tickets (
                    titulo,
                    descripcion,
                    prioridad,
                    sistema_afectado,
                    usuarios_afectados,
                    nivel_bloqueo,
                    solucion_temporal,
                    cliente_id,
                    empresa,
                    persona_contacto,
                    telefono_contacto
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sssssssisss",
                $titulo,
                $descripcion,
                $prioridad,
                $sistema_texto,
                $usuarios_texto,
                $bloqueo_texto,
                $solucion_texto,
                $cliente_id,
                $empresa,
                $persona_contacto,
                $telefono_contacto
            );

            $stmt->execute();
            $id_nuevo = $conexion->insert_id;

            $correoCliente = $_SESSION['cliente_email'] ?? '';
            $nombreCliente = $_SESSION['cliente_nombre'] ?? 'Cliente';

            $asuntoCliente = 'Ticket creado correctamente #' . $id_nuevo;
            $mensajeCliente = "Hola {$nombreCliente},\n\n";
            $mensajeCliente .= "Tu ticket se ha creado correctamente con los siguientes datos:\n\n";
            $mensajeCliente .= "ID: {$id_nuevo}\n";
            $mensajeCliente .= "Título: {$titulo}\n";
            $mensajeCliente .= "Empresa: {$empresa}\n";
            $mensajeCliente .= "Persona de contacto: {$persona_contacto}\n";
            $mensajeCliente .= "Teléfono de contacto: {$telefono_contacto}\n";
            $mensajeCliente .= "Prioridad calculada: {$prioridad}\n\n";
            $mensajeCliente .= "Pronto será revisado por el equipo técnico.\n";

            enviarCorreoSistema($correoCliente, $asuntoCliente, $mensajeCliente, $correoCliente);

            $asuntoAdmin = 'Nuevo ticket creado #' . $id_nuevo;
            $mensajeAdmin = "Se ha creado un nuevo ticket.\n\n";
            $mensajeAdmin .= "ID: {$id_nuevo}\n";
            $mensajeAdmin .= "Cliente: {$nombreCliente}\n";
            $mensajeAdmin .= "Correo del cliente: {$correoCliente}\n";
            $mensajeAdmin .= "Empresa: {$empresa}\n";
            $mensajeAdmin .= "Persona de contacto: {$persona_contacto}\n";
            $mensajeAdmin .= "Teléfono de contacto: {$telefono_contacto}\n";
            $mensajeAdmin .= "Título: {$titulo}\n";
            $mensajeAdmin .= "Descripción: {$descripcion}\n";
            $mensajeAdmin .= "Prioridad: {$prioridad}\n";

            enviarCorreoSistema(obtenerCorreoAdminPrincipal(), $asuntoAdmin, $mensajeAdmin, $correoCliente);

            header("Location: ver.php?id=" . $id_nuevo . "&creado=ok");
            exit();
        } catch (Exception $e) {
            $error = "Error al crear el ticket: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Ticket</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include("nav_public.php"); ?>

<div class="container">
    <h1>Crear Ticket</h1>

    <p>
        Sesión iniciada como:
        <strong><?= htmlspecialchars($_SESSION['cliente_nombre'] ?? '') ?></strong>
        <?php if (!empty($_SESSION['cliente_empresa'])) { ?>
            - Empresa:
            <strong><?= htmlspecialchars($_SESSION['cliente_empresa']) ?></strong>
        <?php } ?>
    </p>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form method="POST">
        <label>Título</label>
        <input type="text" name="titulo" required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">

        <label>Descripción</label>
        <textarea name="descripcion" required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>

        <label>Persona de contacto</label>
        <input type="text" name="persona_contacto" required value="<?= htmlspecialchars($_POST['persona_contacto'] ?? ($_SESSION['cliente_nombre'] ?? '')) ?>">

        <label>Teléfono de contacto</label>
        <input type="text" name="telefono_contacto" required value="<?= htmlspecialchars($_POST['telefono_contacto'] ?? '') ?>">

        <h3>Clasificación del problema</h3>

        <label>¿Qué sistema está afectado?</label>
        <select name="sistema" id="sistema" required>
            <option value="0">Página web informativa</option>
            <option value="1">Panel de administración</option>
            <option value="2">Base de datos</option>
            <option value="1">Correo electrónico</option>
            <option value="2">Red interna</option>
            <option value="3">Servidor completo</option>
        </select>
        <input type="hidden" name="sistema_texto" id="sistema_texto" value="Página web informativa">

        <label>¿A cuántos usuarios afecta?</label>
        <select name="usuarios" id="usuarios" required>
            <option value="0">Solo a mí</option>
            <option value="1">A mi departamento</option>
            <option value="2">A varios usuarios</option>
            <option value="3">A toda la empresa</option>
        </select>
        <input type="hidden" name="usuarios_texto" id="usuarios_texto" value="Solo a mí">

        <label>¿Puedes seguir trabajando?</label>
        <select name="bloqueo" id="bloqueo" required>
            <option value="0">Sí, sin problema</option>
            <option value="1">Sí, pero con limitaciones</option>
            <option value="2">No, estoy bloqueado</option>
        </select>
        <input type="hidden" name="bloqueo_texto" id="bloqueo_texto" value="Sí, sin problema">

        <label>¿Existe una solución temporal?</label>
        <select name="solucion" id="solucion" required>
            <option value="0">Sí</option>
            <option value="1">No</option>
        </select>
        <input type="hidden" name="solucion_texto" id="solucion_texto" value="Sí">

        <button type="submit" name="crear">Crear Ticket</button>
    </form>
</div>

<script>
function syncHidden(selectId, hiddenId) {
    const select = document.getElementById(selectId);
    const hidden = document.getElementById(hiddenId);

    function updateHidden() {
        hidden.value = select.options[select.selectedIndex].text;
    }

    updateHidden();
    select.addEventListener("change", updateHidden);
}

syncHidden("sistema", "sistema_texto");
syncHidden("usuarios", "usuarios_texto");
syncHidden("bloqueo", "bloqueo_texto");
syncHidden("solucion", "solucion_texto");
</script>

</body>
</html>
