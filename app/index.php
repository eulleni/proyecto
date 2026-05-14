<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");

try {
    $resultado = $conexion->query("
        SELECT 
            tickets.*,
            clientes.nombre AS cliente_nombre,
            clientes.apellidos AS cliente_apellidos,
            clientes.email AS cliente_email
        FROM tickets
        LEFT JOIN clientes ON tickets.cliente_id = clientes.id
        ORDER BY tickets.fecha DESC
    ");
} catch (Exception $e) {
    die("Error en la consulta SQL: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Tickets</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include("nav_admin.php"); ?>

<div class="container">
    <h1>Panel de Administración</h1>

    <div class="tabla-responsive">
        <table>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Empresa</th>
                <th>Contacto</th>
                <th>Título</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Técnico</th>
                <th>Última mod.</th>
                <th>Gestión</th>
            </tr>

            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <?php
                $prioridad = $fila['prioridad'] ?? 'baja';
                $estado = $fila['estado'] ?? 'abierto';
                $nombreCompleto = trim(($fila['cliente_nombre'] ?? '') . ' ' . ($fila['cliente_apellidos'] ?? ''));

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
                <tr>
                    <td><?= $fila['id'] ?></td>
                    <td><?= htmlspecialchars($nombreCompleto !== '' ? $nombreCompleto : 'No identificado') ?></td>
                    <td><?= htmlspecialchars($fila['empresa'] ?? 'No indicada') ?></td>
                    <td>
                        <?= htmlspecialchars($fila['persona_contacto'] ?? 'No indicado') ?><br>
                        <span class="texto-suave"><?= htmlspecialchars($fila['telefono_contacto'] ?? 'Sin teléfono') ?></span>
                    </td>
                    <td><?= htmlspecialchars($fila['titulo'] ?? '') ?></td>

                    <td>
                        <span class="<?= $clase_prioridad ?>">
                            <?= htmlspecialchars($prioridad) ?>
                        </span>
                    </td>

                    <td>
                        <span class="<?= $clase_estado ?>">
                            <?= htmlspecialchars($estado) ?>
                        </span>
                    </td>

                    <td><?= htmlspecialchars($fila['tecnico_asignado'] ?? 'Sin asignar') ?></td>
                    <td><?= htmlspecialchars($fila['actualizado_en'] ?? ($fila['fecha'] ?? '')) ?></td>

                    <td>
                        <a class="btn-small" href="admin_ver.php?id=<?= $fila['id'] ?>">Gestionar</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>
