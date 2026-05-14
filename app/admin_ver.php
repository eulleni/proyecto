<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");
include("mail_helper.php");

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID inválido.");
}

try {
    $resultado_tecnicos = $conexion->query("SELECT nombre FROM tecnicos WHERE activo = 1 ORDER BY nombre ASC");
    $tecnicos_disponibles = ["Sin asignar"];

    while ($fila_tecnico = $resultado_tecnicos->fetch_assoc()) {
        $tecnicos_disponibles[] = $fila_tecnico['nombre'];
    }
} catch (Exception $e) {
    die("Error al cargar técnicos: " . $e->getMessage());
}

if (isset($_POST['actualizar'])) {
    $estado = $_POST['estado'] ?? 'abierto';
    $tecnico = $_POST['tecnico_asignado'] ?? 'Sin asignar';

    try {
        $stmtLectura = $conexion->prepare("
            SELECT tickets.*, clientes.email AS cliente_email, CONCAT(clientes.nombre, ' ', clientes.apellidos) AS cliente_nombre
            FROM tickets
            LEFT JOIN clientes ON tickets.cliente_id = clientes.id
            WHERE tickets.id = ?
        ");
        $stmtLectura->bind_param("i", $id);
        $stmtLectura->execute();
        $ticketAntes = $stmtLectura->get_result()->fetch_assoc();

        if ($estado === 'cerrado') {
            $stmt = $conexion->prepare("
                UPDATE tickets
                SET estado = ?, tecnico_asignado = ?, fecha_cierre = NOW(), actualizado_en = NOW(), ultimo_editor_admin = ?
                WHERE id = ?
            ");
        } else {
            $stmt = $conexion->prepare("
                UPDATE tickets
                SET estado = ?, tecnico_asignado = ?, fecha_cierre = NULL, actualizado_en = NOW(), ultimo_editor_admin = ?
                WHERE id = ?
            ");
        }

        $adminUsuario = $_SESSION['admin_usuario'] ?? 'Administrador';
        $stmt->bind_param("sssi", $estado, $tecnico, $adminUsuario, $id);
        $stmt->execute();

        if ($ticketAntes && !empty($ticketAntes['cliente_email'])) {
            $asunto = 'Actualización de tu ticket #' . $id;
            $mensaje = "Hola " . trim($ticketAntes['cliente_nombre'] ?? 'cliente') . ",\n\n";
            $mensaje .= "Tu ticket ha sido actualizado por el equipo de soporte.\n\n";
            $mensaje .= "Nuevo estado: {$estado}\n";
            $mensaje .= "Técnico asignado: {$tecnico}\n";
            $mensaje .= "Título: " . ($ticketAntes['titulo'] ?? '') . "\n\n";
            $mensaje .= "Puedes acceder a la aplicación para revisar el seguimiento.\n";

            enviarCorreoSistema($ticketAntes['cliente_email'], $asunto, $mensaje);
        }

        header("Location: admin_ver.php?id=" . $id . "&actualizado=ok");
        exit();
    } catch (Exception $e) {
        $error = "Error al actualizar el ticket: " . $e->getMessage();
    }
}

if (isset($_POST['eliminar'])) {
    try {
        $stmt = $conexion->prepare("DELETE FROM tickets WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $error = "Error al eliminar el ticket: " . $e->getMessage();
    }
}

try {
    $stmt = $conexion->prepare("
        SELECT tickets.*, clientes.nombre AS cliente_nombre, clientes.apellidos AS cliente_apellidos, clientes.email AS cliente_email
        FROM tickets
        LEFT JOIN clientes ON tickets.cliente_id = clientes.id
        WHERE tickets.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $ticket = $resultado->fetch_assoc();

    if (!$ticket) {
        die("Ticket no encontrado.");
    }
} catch (Exception $e) {
    die("Error al cargar el ticket: " . $e->getMessage());
}

$prioridad = $ticket['prioridad'] ?? 'baja';
$estado = $ticket['estado'] ?? 'abierto';
$nombreCompleto = trim(($ticket['cliente_nombre'] ?? '') . ' ' . ($ticket['cliente_apellidos'] ?? ''));

$clase_prioridad = 'prioridad-baja';
if ($prioridad === 'media') {
    $clase_prioridad = 'prioridad-media';
} elseif ($prioridad === 'alta') {
    $clase_prioridad = 'prioridad-alta';
}

$clase_estado = 'estado-abierto';
if ($estado === 'en_progreso') {
    $clase_estado = 'estado-progreso';
} elseif ($estado === 'cerrado') {
    $clase_estado = 'estado-cerrado';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Ticket</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include("nav_admin.php"); ?>

<div class="container">
    <h1>Gestión de Ticket</h1>

    <?php if (!empty($_GET['actualizado']) && $_GET['actualizado'] === 'ok') { ?>
        <p class="success">Ticket actualizado correctamente.</p>
    <?php } ?>

    <?php if (!empty($_GET['editado']) && $_GET['editado'] === 'ok') { ?>
        <p class="success">Los datos del ticket se han editado correctamente.</p>
    <?php } ?>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <div class="detalle-ticket">
        <p><strong>ID:</strong> <?= $ticket['id'] ?></p>
        <p><strong>Cliente:</strong> <?= htmlspecialchars($nombreCompleto !== '' ? $nombreCompleto : 'No identificado') ?></p>
        <p><strong>Correo del cliente:</strong> <?= htmlspecialchars($ticket['cliente_email'] ?? 'No indicado') ?></p>
        <p><strong>Empresa:</strong> <?= htmlspecialchars($ticket['empresa'] ?? 'No indicada') ?></p>
        <p><strong>Persona de contacto:</strong> <?= htmlspecialchars($ticket['persona_contacto'] ?? 'No indicada') ?></p>
        <p><strong>Teléfono de contacto:</strong> <?= htmlspecialchars($ticket['telefono_contacto'] ?? 'No indicado') ?></p>
        <p><strong>Título:</strong> <?= htmlspecialchars($ticket['titulo'] ?? '') ?></p>
        <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($ticket['descripcion'] ?? '')) ?></p>
        <p><strong>Sistema afectado:</strong> <?= htmlspecialchars($ticket['sistema_afectado'] ?? 'No indicado') ?></p>
        <p><strong>Usuarios afectados:</strong> <?= htmlspecialchars($ticket['usuarios_afectados'] ?? 'No indicado') ?></p>
        <p><strong>Nivel de bloqueo:</strong> <?= htmlspecialchars($ticket['nivel_bloqueo'] ?? 'No indicado') ?></p>
        <p><strong>¿Existe solución temporal?:</strong> <?= htmlspecialchars($ticket['solucion_temporal'] ?? 'No indicado') ?></p>

        <p>
            <strong>Prioridad:</strong>
            <span class="<?= $clase_prioridad ?>"><?= htmlspecialchars($prioridad) ?></span>
        </p>

        <p>
            <strong>Estado actual:</strong>
            <span class="<?= $clase_estado ?>"><?= htmlspecialchars($estado) ?></span>
        </p>

        <p><strong>Técnico asignado:</strong> <?= htmlspecialchars($ticket['tecnico_asignado'] ?? 'Sin asignar') ?></p>
        <p><strong>Fecha de creación:</strong> <?= htmlspecialchars($ticket['fecha'] ?? '') ?></p>
        <p><strong>Fecha de cierre:</strong> <?= htmlspecialchars($ticket['fecha_cierre'] ?? 'Pendiente') ?></p>
        <p><strong>Última modificación:</strong> <?= htmlspecialchars($ticket['actualizado_en'] ?? 'Sin modificaciones') ?></p>
        <p><strong>Último editor admin:</strong> <?= htmlspecialchars($ticket['ultimo_editor_admin'] ?? 'Sin registro') ?></p>

        <?php if (!empty($ticket['nota_edicion_admin'])) { ?>
            <div class="bloque-info">
                <strong>Constancia de edición administrativa</strong>
                <p><?= nl2br(htmlspecialchars($ticket['nota_edicion_admin'])) ?></p>
            </div>
        <?php } ?>

        <?php if (!empty($ticket['fecha_solicitud_actualizacion'])) { ?>
            <div class="bloque-info bloque-aviso">
                <strong>Última solicitud de actualización del cliente</strong>
                <p><strong>Fecha:</strong> <?= htmlspecialchars($ticket['fecha_solicitud_actualizacion']) ?></p>
                <p><?= nl2br(htmlspecialchars($ticket['solicitud_actualizacion'] ?? 'El cliente ha solicitado una actualización del estado.')) ?></p>
            </div>
        <?php } ?>
    </div>

    <h2>Actualizar ticket</h2>
    <form method="POST" class="form-admin">
        <label>Estado</label>
        <select name="estado">
            <option value="abierto" <?= $estado === 'abierto' ? 'selected' : '' ?>>Abierto</option>
            <option value="en_progreso" <?= $estado === 'en_progreso' ? 'selected' : '' ?>>En progreso</option>
            <option value="cerrado" <?= $estado === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
        </select>

        <label>Técnico asignado</label>
        <select name="tecnico_asignado">
            <?php foreach ($tecnicos_disponibles as $tecnico) { ?>
                <option value="<?= htmlspecialchars($tecnico) ?>"
                    <?= (($ticket['tecnico_asignado'] ?? 'Sin asignar') === $tecnico) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tecnico) ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit" name="actualizar">Guardar cambios</button>
    </form>

    <div class="acciones-admin">
        <a class="btn" href="editar_ticket.php?id=<?= $ticket['id'] ?>">Editar ticket</a>
        <a class="btn" href="index.php">Volver al panel</a>

        <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este ticket?');">
            <button type="submit" name="eliminar" class="btn-danger">Eliminar ticket</button>
        </form>
    </div>
</div>

</body>
</html>
