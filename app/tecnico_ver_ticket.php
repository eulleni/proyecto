<?php
session_start();

if (!isset($_SESSION['tecnico_id'])) {
    header("Location: login_tecnico.php");
    exit();
}

if (!empty($_SESSION['tecnico_debe_cambiar_password'])) {
    header("Location: cambiar_password_tecnico.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID inválido.");
}

try {
    $stmt = $conexion->prepare("\n        SELECT tickets.*, clientes.nombre AS cliente_nombre, clientes.apellidos AS cliente_apellidos, clientes.email AS cliente_email\n        FROM tickets\n        LEFT JOIN clientes ON tickets.cliente_id = clientes.id\n        WHERE tickets.id = ?\n    ");
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
$tecnicoActual = trim((string) ($ticket['tecnico_asignado'] ?? ''));
$sinAsignar = ($tecnicoActual === '' || $tecnicoActual === 'Sin asignar');

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
    <title>Ver ticket - Técnico</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include("nav_tecnico.php"); ?>

<div class="container">
    <h1>Detalle del ticket</h1>
    <p class="subtexto">Vista de solo lectura para técnicos. Desde esta pantalla no se pueden editar los datos del ticket.</p>

    <?php if (!empty($_GET['asignado']) && $_GET['asignado'] === 'ok') { ?>
        <p class="success">Ticket asignado correctamente.</p>
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

        <p><strong>Técnico asignado:</strong> <?= htmlspecialchars($sinAsignar ? 'Sin asignar' : $tecnicoActual) ?></p>
        <p><strong>Fecha de creación:</strong> <?= htmlspecialchars($ticket['fecha'] ?? '') ?></p>
        <p><strong>Fecha de cierre:</strong> <?= htmlspecialchars($ticket['fecha_cierre'] ?? 'Pendiente') ?></p>
        <p><strong>Última modificación:</strong> <?= htmlspecialchars($ticket['actualizado_en'] ?? 'Sin modificaciones') ?></p>

        <?php if (!empty($ticket['fecha_solicitud_actualizacion'])) { ?>
            <div class="bloque-info bloque-aviso">
                <strong>Última solicitud de actualización del cliente</strong>
                <p><strong>Fecha:</strong> <?= htmlspecialchars($ticket['fecha_solicitud_actualizacion']) ?></p>
                <p><?= nl2br(htmlspecialchars($ticket['solicitud_actualizacion'] ?? 'El cliente ha solicitado una actualización del estado.')) ?></p>
            </div>
        <?php } ?>
    </div>

    <div class="acciones-admin">
        <?php if ($sinAsignar) { ?>
            <a class="btn" href="tecnico_asignar_ticket.php?id=<?= $ticket['id'] ?>" onclick="return confirm('¿Quieres asignarte este ticket?');">Asignarme este ticket</a>
        <?php } ?>
        <a class="btn" href="panel_tecnico.php">Volver al panel técnico</a>
    </div>
</div>

</body>
</html>
