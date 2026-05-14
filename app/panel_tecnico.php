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

include("db.php");
$tecnico_nombre = $_SESSION['tecnico_nombre'] ?? '';

$stmt_sin_asignar = $conexion->prepare("SELECT COUNT(*) AS total FROM tickets WHERE tecnico_asignado IS NULL OR tecnico_asignado = '' OR tecnico_asignado = 'Sin asignar'");
$stmt_sin_asignar->execute();
$total_sin_asignar = $stmt_sin_asignar->get_result()->fetch_assoc()['total'] ?? 0;

$stmt_mis_tickets = $conexion->prepare("SELECT COUNT(*) AS total FROM tickets WHERE tecnico_asignado = ?");
$stmt_mis_tickets->bind_param("s", $tecnico_nombre);
$stmt_mis_tickets->execute();
$total_mis_tickets = $stmt_mis_tickets->get_result()->fetch_assoc()['total'] ?? 0;

$stmt_todos = $conexion->prepare("SELECT COUNT(*) AS total FROM tickets");
$stmt_todos->execute();
$total_tickets = $stmt_todos->get_result()->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel técnico</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include("nav_tecnico.php"); ?>

<div class="container">
    <h1>Panel técnico</h1>
    <p class="subtexto">Sesión iniciada como <strong><?= htmlspecialchars($tecnico_nombre) ?></strong>. Usa el menú superior para consultar incidencias y asignarte tickets pendientes.</p>

    <?php if (!empty($_GET['password_cambiada']) && $_GET['password_cambiada'] === 'ok') { ?>
        <p class="success">Contraseña actualizada correctamente. Ya puedes usar el panel de técnicos.</p>
    <?php } ?>

    <?php if (!empty($_GET['asignado']) && $_GET['asignado'] === 'ok') { ?>
        <p class="success">Ticket asignado correctamente.</p>
    <?php } ?>

    <?php if (!empty($_GET['error']) && $_GET['error'] === 'ya_asignado') { ?>
        <p class="error">Este ticket ya está asignado a otro técnico.</p>
    <?php } ?>

    <?php if (!empty($_GET['error']) && $_GET['error'] === 'ticket_no_existe') { ?>
        <p class="error">No se ha encontrado el ticket solicitado.</p>
    <?php } ?>

    <div class="cards-admin-grid">
        <div class="card-admin">
            <h3>Tickets sin asignar</h3>
            <p class="contador-panel"><?= (int)$total_sin_asignar ?></p>
            <p>Incidencias disponibles para que un técnico las asuma.</p>
            <a class="btn" href="tickets_sin_asignar.php">Ver tickets sin asignar</a>
        </div>

        <div class="card-admin">
            <h3>Mis tickets asignados</h3>
            <p class="contador-panel"><?= (int)$total_mis_tickets ?></p>
            <p>Incidencias que tienes asignadas actualmente.</p>
            <a class="btn" href="mis_tickets_tecnico.php">Ver mis tickets</a>
        </div>

        <div class="card-admin">
            <h3>Todos los tickets</h3>
            <p class="contador-panel"><?= (int)$total_tickets ?></p>
            <p>Consulta general de tickets. Este apartado es solo de lectura.</p>
            <a class="btn" href="todos_tickets_tecnico.php">Ver todos los tickets</a>
        </div>
    </div>
</div>

</body>
</html>
