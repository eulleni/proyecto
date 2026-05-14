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

$cliente_id = (int) $_SESSION['cliente_id'];
$ticket = null;

if (isset($_POST['solicitar_actualizacion'])) {
    $ticket_id_post = (int)($_POST['ticket_id'] ?? 0);
    $mensaje_cliente = trim($_POST['mensaje_actualizacion'] ?? '');

    if ($ticket_id_post > 0) {
        try {
            $stmt = $conexion->prepare("SELECT id, titulo, cliente_id FROM tickets WHERE id = ? AND cliente_id = ?");
            $stmt->bind_param("ii", $ticket_id_post, $cliente_id);
            $stmt->execute();
            $ticketPropio = $stmt->get_result()->fetch_assoc();

            if ($ticketPropio) {
                if ($mensaje_cliente === '') {
                    $mensaje_cliente = 'El cliente ha solicitado una actualización del estado de su ticket.';
                }

                $stmt = $conexion->prepare("
                    UPDATE tickets
                    SET solicitud_actualizacion = ?, fecha_solicitud_actualizacion = NOW()
                    WHERE id = ? AND cliente_id = ?
                ");
                $stmt->bind_param("sii", $mensaje_cliente, $ticket_id_post, $cliente_id);
                $stmt->execute();

                $nombreCliente = $_SESSION['cliente_nombre'] ?? 'Cliente';
                $correoCliente = $_SESSION['cliente_email'] ?? '';

                $asunto = 'Solicitud de actualización del ticket #' . $ticket_id_post;
                $mensajeAdmin = "El cliente ha solicitado una actualización de su ticket.\n\n";
                $mensajeAdmin .= "Ticket ID: {$ticket_id_post}\n";
                $mensajeAdmin .= "Título: " . ($ticketPropio['titulo'] ?? '') . "\n";
                $mensajeAdmin .= "Cliente: {$nombreCliente}\n";
                $mensajeAdmin .= "Correo: {$correoCliente}\n";
                $mensajeAdmin .= "Mensaje del cliente: {$mensaje_cliente}\n";

                enviarCorreoSistema(obtenerCorreoAdminPrincipal(), $asunto, $mensajeAdmin, $correoCliente);

                header("Location: ver.php?id=" . $ticket_id_post . "&solicitud=ok");
                exit();
            } else {
                $error = "No se puede solicitar actualización de ese ticket.";
            }
        } catch (Exception $e) {
            $error = "Error al registrar la solicitud de actualización: " . $e->getMessage();
        }
    }
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    if ($id > 0) {
        try {
            $stmt = $conexion->prepare("
                SELECT * FROM tickets
                WHERE id = ? AND cliente_id = ?
            ");
            $stmt->bind_param("ii", $id, $cliente_id);
            $stmt->execute();
            $resultado_ticket = $stmt->get_result();
            $ticket = $resultado_ticket->fetch_assoc();
        } catch (Exception $e) {
            die("Error al cargar el ticket: " . $e->getMessage());
        }
    }
}

try {
    $stmt = $conexion->prepare("
        SELECT id, titulo, descripcion, fecha, estado, prioridad
        FROM tickets
        WHERE cliente_id = ?
        ORDER BY fecha DESC
    ");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $resultado_lista = $stmt->get_result();
} catch (Exception $e) {
    die("Error al cargar la lista de tickets: " . $e->getMessage());
}

if ($ticket) {
    $clase_prioridad = 'prioridad-baja';
    if (($ticket['prioridad'] ?? '') === 'media') {
        $clase_prioridad = 'prioridad-media';
    } elseif (($ticket['prioridad'] ?? '') === 'alta') {
        $clase_prioridad = 'prioridad-alta';
    }

    $clase_estado = 'estado-abierto';
    if (($ticket['estado'] ?? '') === 'en_progreso') {
        $clase_estado = 'estado-progreso';
    } elseif (($ticket['estado'] ?? '') === 'cerrado') {
        $clase_estado = 'estado-cerrado';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tickets</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include("nav_public.php"); ?>

<div class="container">
    <h1>Seguimiento de mis tickets</h1>

    <?php if (!empty($_GET['creado']) && $_GET['creado'] === 'ok') { ?>
        <p class="success">Ticket creado correctamente. Se ha intentado enviar el correo de confirmación.</p>
    <?php } ?>

    <?php if (!empty($_GET['solicitud']) && $_GET['solicitud'] === 'ok') { ?>
        <p class="success">Tu solicitud de actualización se ha enviado correctamente.</p>
    <?php } ?>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <p>
        Sesión iniciada como:
        <strong><?= htmlspecialchars($_SESSION['cliente_nombre'] ?? '') ?></strong>
        <?php if (!empty($_SESSION['cliente_empresa'])) { ?>
            - Empresa:
            <strong><?= htmlspecialchars($_SESSION['cliente_empresa']) ?></strong>
        <?php } ?>
    </p>

    <h2>Listado de tickets</h2>

    <div class="tabla-responsive">
        <table>
            <tr>
                <th>Título</th>
                <th>Descripción breve</th>
                <th>Estado</th>
                <th>Prioridad</th>
                <th>Fecha de creación</th>
                <th>Seguimiento</th>
            </tr>

            <?php while ($fila = $resultado_lista->fetch_assoc()) { ?>
                <?php
                $descripcion_breve = $fila['descripcion'] ?? '';
                if (mb_strlen($descripcion_breve) > 80) {
                    $descripcion_breve = mb_substr($descripcion_breve, 0, 80) . '...';
                }

                $clase_prioridad_lista = 'prioridad-baja';
                if (($fila['prioridad'] ?? '') === 'media') {
                    $clase_prioridad_lista = 'prioridad-media';
                } elseif (($fila['prioridad'] ?? '') === 'alta') {
                    $clase_prioridad_lista = 'prioridad-alta';
                }

                $clase_estado_lista = 'estado-abierto';
                if (($fila['estado'] ?? '') === 'en_progreso') {
                    $clase_estado_lista = 'estado-progreso';
                } elseif (($fila['estado'] ?? '') === 'cerrado') {
                    $clase_estado_lista = 'estado-cerrado';
                }
                ?>
                <tr>
                    <td><?= htmlspecialchars($fila['titulo'] ?? '') ?></td>
                    <td><?= htmlspecialchars($descripcion_breve) ?></td>
                    <td><span class="<?= $clase_estado_lista ?>"><?= htmlspecialchars($fila['estado'] ?? 'abierto') ?></span></td>
                    <td><span class="<?= $clase_prioridad_lista ?>"><?= htmlspecialchars($fila['prioridad'] ?? 'baja') ?></span></td>
                    <td><?= htmlspecialchars($fila['fecha'] ?? '') ?></td>
                    <td>
                        <a class="btn-small" href="ver.php?id=<?= $fila['id'] ?>">Ver seguimiento</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <?php if (isset($_GET['id']) && !$ticket) { ?>
        <p class="error">No se ha encontrado ese ticket o no tienes permiso para verlo.</p>
    <?php } ?>

    <?php if ($ticket) { ?>
        <hr style="margin: 30px 0;">

        <h2>Detalle del ticket</h2>

        <div class="detalle-ticket">
            <p><strong>ID:</strong> <?= $ticket['id'] ?></p>
            <p><strong>Título:</strong> <?= htmlspecialchars($ticket['titulo'] ?? '') ?></p>
            <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($ticket['descripcion'] ?? '')) ?></p>
            <p><strong>Persona de contacto:</strong> <?= htmlspecialchars($ticket['persona_contacto'] ?? 'No indicada') ?></p>
            <p><strong>Teléfono de contacto:</strong> <?= htmlspecialchars($ticket['telefono_contacto'] ?? 'No indicado') ?></p>
            <p><strong>Sistema afectado:</strong> <?= htmlspecialchars($ticket['sistema_afectado'] ?? 'No indicado') ?></p>
            <p><strong>Usuarios afectados:</strong> <?= htmlspecialchars($ticket['usuarios_afectados'] ?? 'No indicado') ?></p>
            <p><strong>Nivel de bloqueo:</strong> <?= htmlspecialchars($ticket['nivel_bloqueo'] ?? 'No indicado') ?></p>
            <p><strong>¿Existe solución temporal?:</strong> <?= htmlspecialchars($ticket['solucion_temporal'] ?? 'No indicado') ?></p>

            <p>
                <strong>Prioridad:</strong>
                <span class="<?= $clase_prioridad ?>">
                    <?= htmlspecialchars($ticket['prioridad'] ?? 'baja') ?>
                </span>
            </p>

            <p>
                <strong>Estado:</strong>
                <span class="<?= $clase_estado ?>">
                    <?= htmlspecialchars($ticket['estado'] ?? 'abierto') ?>
                </span>
            </p>

            <p><strong>Técnico asignado:</strong> <?= htmlspecialchars($ticket['tecnico_asignado'] ?? 'Sin asignar') ?></p>
            <p><strong>Fecha de creación:</strong> <?= htmlspecialchars($ticket['fecha'] ?? '') ?></p>
            <p><strong>Fecha de cierre:</strong> <?= htmlspecialchars($ticket['fecha_cierre'] ?? 'Pendiente') ?></p>
            <p><strong>Última modificación:</strong> <?= htmlspecialchars($ticket['actualizado_en'] ?? 'Sin modificaciones') ?></p>

            <?php if (!empty($ticket['nota_edicion_admin'])) { ?>
                <div class="bloque-info">
                    <strong>Constancia de edición administrativa</strong>
                    <p><?= nl2br(htmlspecialchars($ticket['nota_edicion_admin'])) ?></p>
                </div>
            <?php } ?>

            <?php if (!empty($ticket['fecha_solicitud_actualizacion'])) { ?>
                <div class="bloque-info bloque-aviso">
                    <strong>Última solicitud de actualización enviada</strong>
                    <p><strong>Fecha:</strong> <?= htmlspecialchars($ticket['fecha_solicitud_actualizacion']) ?></p>
                    <p><?= nl2br(htmlspecialchars($ticket['solicitud_actualizacion'] ?? '')) ?></p>
                </div>
            <?php } ?>
        </div>

        <div class="form-admin">
            <h3>Solicitar actualización / consultar estado</h3>
            <form method="POST">
                <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
                <label>Mensaje para soporte</label>
                <textarea name="mensaje_actualizacion" placeholder="Ejemplo: Buenos días, quería saber cómo va el ticket y si hay alguna novedad."></textarea>
                <button type="submit" name="solicitar_actualizacion">Solicitar actualización</button>
            </form>
        </div>
    <?php } ?>
</div>

</body>
</html>
