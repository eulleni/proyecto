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

    $stmt = $conexion->prepare("
        SELECT tickets.*, clientes.email AS cliente_email, CONCAT(clientes.nombre, ' ', clientes.apellidos) AS cliente_nombre
        FROM tickets
        LEFT JOIN clientes ON tickets.cliente_id = clientes.id
        WHERE tickets.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();

    if (!$ticket) {
        die("Ticket no encontrado.");
    }
} catch (Exception $e) {
    die("Error al cargar el ticket: " . $e->getMessage());
}

if (isset($_POST['guardar_edicion'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $empresa = trim($_POST['empresa'] ?? '');
    $persona_contacto = trim($_POST['persona_contacto'] ?? '');
    $telefono_contacto = trim($_POST['telefono_contacto'] ?? '');
    $sistema_afectado = trim($_POST['sistema_afectado'] ?? '');
    $usuarios_afectados = trim($_POST['usuarios_afectados'] ?? '');
    $nivel_bloqueo = trim($_POST['nivel_bloqueo'] ?? '');
    $solucion_temporal = trim($_POST['solucion_temporal'] ?? '');
    $prioridad = trim($_POST['prioridad'] ?? 'baja');
    $estado = trim($_POST['estado'] ?? 'abierto');
    $tecnico_asignado = trim($_POST['tecnico_asignado'] ?? 'Sin asignar');
    $motivo_edicion = trim($_POST['motivo_edicion'] ?? '');

    if ($titulo === '' || $descripcion === '' || $empresa === '' || $persona_contacto === '' || $telefono_contacto === '') {
        $error = "Debes rellenar al menos los campos principales del ticket.";
    } elseif ($motivo_edicion === '') {
        $error = "Debes indicar el motivo o la constancia de la edición.";
    } else {
        try {
            $adminUsuario = $_SESSION['admin_usuario'] ?? 'Administrador';
            $notaEdicion = "Editado por {$adminUsuario} el " . date('d/m/Y H:i') . ". Motivo: {$motivo_edicion}";

            if ($estado === 'cerrado') {
                $sql = "
                    UPDATE tickets
                    SET titulo = ?, descripcion = ?, empresa = ?, persona_contacto = ?, telefono_contacto = ?,
                        sistema_afectado = ?, usuarios_afectados = ?, nivel_bloqueo = ?, solucion_temporal = ?,
                        prioridad = ?, estado = ?, tecnico_asignado = ?, actualizado_en = NOW(),
                        ultimo_editor_admin = ?, nota_edicion_admin = ?, fecha_cierre = COALESCE(fecha_cierre, NOW())
                    WHERE id = ?
                ";
            } else {
                $sql = "
                    UPDATE tickets
                    SET titulo = ?, descripcion = ?, empresa = ?, persona_contacto = ?, telefono_contacto = ?,
                        sistema_afectado = ?, usuarios_afectados = ?, nivel_bloqueo = ?, solucion_temporal = ?,
                        prioridad = ?, estado = ?, tecnico_asignado = ?, actualizado_en = NOW(),
                        ultimo_editor_admin = ?, nota_edicion_admin = ?, fecha_cierre = NULL
                    WHERE id = ?
                ";
            }

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param(
                "ssssssssssssssi",
                $titulo,
                $descripcion,
                $empresa,
                $persona_contacto,
                $telefono_contacto,
                $sistema_afectado,
                $usuarios_afectados,
                $nivel_bloqueo,
                $solucion_temporal,
                $prioridad,
                $estado,
                $tecnico_asignado,
                $adminUsuario,
                $notaEdicion,
                $id
            );
            $stmt->execute();

            if (!empty($ticket['cliente_email'])) {
                $asunto = 'Tu ticket #' . $id . ' ha sido editado por soporte';
                $mensaje = "Hola " . trim($ticket['cliente_nombre'] ?? 'cliente') . ",\n\n";
                $mensaje .= "Se han actualizado los datos de tu ticket.\n\n";
                $mensaje .= "Título: {$titulo}\n";
                $mensaje .= "Estado: {$estado}\n";
                $mensaje .= "Técnico asignado: {$tecnico_asignado}\n";
                $mensaje .= "Motivo de la edición: {$motivo_edicion}\n\n";
                $mensaje .= "Puedes consultar el seguimiento desde la aplicación.\n";

                enviarCorreoSistema($ticket['cliente_email'], $asunto, $mensaje);
            }

            header("Location: admin_ver.php?id=" . $id . "&editado=ok");
            exit();
        } catch (Exception $e) {
            $error = "Error al guardar la edición: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar ticket</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include("nav_admin.php"); ?>

<div class="container">
    <h1>Editar Ticket #<?= (int)$ticket['id'] ?></h1>

    <?php if (!empty($error)) { ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php } ?>

    <form method="POST" class="form-admin">
        <label>Título</label>
        <input type="text" name="titulo" required value="<?= htmlspecialchars($_POST['titulo'] ?? ($ticket['titulo'] ?? '')) ?>">

        <label>Descripción</label>
        <textarea name="descripcion" required><?= htmlspecialchars($_POST['descripcion'] ?? ($ticket['descripcion'] ?? '')) ?></textarea>

        <label>Empresa</label>
        <input type="text" name="empresa" required value="<?= htmlspecialchars($_POST['empresa'] ?? ($ticket['empresa'] ?? '')) ?>">

        <label>Persona de contacto</label>
        <input type="text" name="persona_contacto" required value="<?= htmlspecialchars($_POST['persona_contacto'] ?? ($ticket['persona_contacto'] ?? '')) ?>">

        <label>Teléfono de contacto</label>
        <input type="text" name="telefono_contacto" required value="<?= htmlspecialchars($_POST['telefono_contacto'] ?? ($ticket['telefono_contacto'] ?? '')) ?>">

        <label>Sistema afectado</label>
        <input type="text" name="sistema_afectado" value="<?= htmlspecialchars($_POST['sistema_afectado'] ?? ($ticket['sistema_afectado'] ?? '')) ?>">

        <label>Usuarios afectados</label>
        <input type="text" name="usuarios_afectados" value="<?= htmlspecialchars($_POST['usuarios_afectados'] ?? ($ticket['usuarios_afectados'] ?? '')) ?>">

        <label>Nivel de bloqueo</label>
        <input type="text" name="nivel_bloqueo" value="<?= htmlspecialchars($_POST['nivel_bloqueo'] ?? ($ticket['nivel_bloqueo'] ?? '')) ?>">

        <label>Solución temporal</label>
        <input type="text" name="solucion_temporal" value="<?= htmlspecialchars($_POST['solucion_temporal'] ?? ($ticket['solucion_temporal'] ?? '')) ?>">

        <label>Prioridad</label>
        <select name="prioridad">
            <option value="baja" <?= (($_POST['prioridad'] ?? ($ticket['prioridad'] ?? 'baja')) === 'baja') ? 'selected' : '' ?>>Baja</option>
            <option value="media" <?= (($_POST['prioridad'] ?? ($ticket['prioridad'] ?? 'baja')) === 'media') ? 'selected' : '' ?>>Media</option>
            <option value="alta" <?= (($_POST['prioridad'] ?? ($ticket['prioridad'] ?? 'baja')) === 'alta') ? 'selected' : '' ?>>Alta</option>
        </select>

        <label>Estado</label>
        <select name="estado">
            <option value="abierto" <?= (($_POST['estado'] ?? ($ticket['estado'] ?? 'abierto')) === 'abierto') ? 'selected' : '' ?>>Abierto</option>
            <option value="en_progreso" <?= (($_POST['estado'] ?? ($ticket['estado'] ?? 'abierto')) === 'en_progreso') ? 'selected' : '' ?>>En progreso</option>
            <option value="cerrado" <?= (($_POST['estado'] ?? ($ticket['estado'] ?? 'abierto')) === 'cerrado') ? 'selected' : '' ?>>Cerrado</option>
        </select>

        <label>Técnico asignado</label>
        <select name="tecnico_asignado">
            <?php
            $tecnicoSeleccionado = $_POST['tecnico_asignado'] ?? ($ticket['tecnico_asignado'] ?? 'Sin asignar');
            foreach ($tecnicos_disponibles as $tecnico) {
            ?>
                <option value="<?= htmlspecialchars($tecnico) ?>" <?= ($tecnicoSeleccionado === $tecnico) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tecnico) ?>
                </option>
            <?php } ?>
        </select>

        <label>Motivo / constancia de la edición</label>
        <textarea name="motivo_edicion" required><?= htmlspecialchars($_POST['motivo_edicion'] ?? '') ?></textarea>

        <div class="acciones-admin">
            <button type="submit" name="guardar_edicion">Guardar edición</button>
            <a class="btn" href="admin_ver.php?id=<?= (int)$ticket['id'] ?>">Cancelar</a>
        </div>
    </form>
</div>

</body>
</html>
