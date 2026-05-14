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

function clasePrioridad($prioridad) {
    if ($prioridad === 'media') return 'prioridad-media';
    if ($prioridad === 'alta') return 'prioridad-alta';
    return 'prioridad-baja';
}

function claseEstado($estado) {
    if ($estado === 'en_progreso') return 'estado-progreso';
    if ($estado === 'cerrado') return 'estado-cerrado';
    return 'estado-abierto';
}

try {
    $stmt = $conexion->prepare("\n        SELECT tickets.*, clientes.nombre AS cliente_nombre, clientes.apellidos AS cliente_apellidos\n        FROM tickets\n        LEFT JOIN clientes ON tickets.cliente_id = clientes.id\n        ORDER BY tickets.fecha DESC\n    ");
    $stmt->execute();
    $tickets = $stmt->get_result();
} catch (Exception $e) {
    die("Error al cargar los tickets: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Todos los tickets</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include("nav_tecnico.php"); ?>

<div class="container">
    <h1>Todos los tickets</h1>
    <p class="subtexto">Este apartado es solo de consulta. Los técnicos no pueden editar los datos de los tickets desde aquí.</p>

    <div class="tabla-responsive">
        <table>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Empresa</th>
                <th>Título</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Técnico</th>
                <th>Acción</th>
            </tr>

            <?php if ($tickets->num_rows === 0) { ?>
                <tr>
                    <td colspan="8">No hay tickets registrados.</td>
                </tr>
            <?php } ?>

            <?php while ($fila = $tickets->fetch_assoc()) { ?>
                <?php
                $prioridad = $fila['prioridad'] ?? 'baja';
                $estado = $fila['estado'] ?? 'abierto';
                $nombreCompleto = trim(($fila['cliente_nombre'] ?? '') . ' ' . ($fila['cliente_apellidos'] ?? ''));
                $tecnico = trim((string)($fila['tecnico_asignado'] ?? ''));
                ?>
                <tr>
                    <td><?= $fila['id'] ?></td>
                    <td><?= htmlspecialchars($nombreCompleto !== '' ? $nombreCompleto : 'No identificado') ?></td>
                    <td><?= htmlspecialchars($fila['empresa'] ?? 'No indicada') ?></td>
                    <td><?= htmlspecialchars($fila['titulo'] ?? '') ?></td>
                    <td><span class="<?= clasePrioridad($prioridad) ?>"><?= htmlspecialchars($prioridad) ?></span></td>
                    <td><span class="<?= claseEstado($estado) ?>"><?= htmlspecialchars($estado) ?></span></td>
                    <td><?= htmlspecialchars($tecnico !== '' ? $tecnico : 'Sin asignar') ?></td>
                    <td><a class="btn-small" href="tecnico_ver_ticket.php?id=<?= $fila['id'] ?>">Ver</a></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>
