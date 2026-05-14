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
$tecnico_nombre = $_SESSION['tecnico_nombre'] ?? '';

if ($id <= 0) {
    header("Location: panel_tecnico.php?error=ticket_no_existe");
    exit();
}

try {
    $stmt = $conexion->prepare("SELECT id, tecnico_asignado FROM tickets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();

    if (!$ticket) {
        header("Location: panel_tecnico.php?error=ticket_no_existe");
        exit();
    }

    $tecnico_actual = trim((string) ($ticket['tecnico_asignado'] ?? ''));

    if ($tecnico_actual !== '' && $tecnico_actual !== 'Sin asignar') {
        header("Location: panel_tecnico.php?error=ya_asignado");
        exit();
    }

    $stmt_update = $conexion->prepare("\n        UPDATE tickets\n        SET tecnico_asignado = ?, actualizado_en = NOW()\n        WHERE id = ?\n          AND (tecnico_asignado IS NULL OR tecnico_asignado = '' OR tecnico_asignado = 'Sin asignar')\n    ");
    $stmt_update->bind_param("si", $tecnico_nombre, $id);
    $stmt_update->execute();

    if ($stmt_update->affected_rows === 0) {
        header("Location: panel_tecnico.php?error=ya_asignado");
        exit();
    }

    header("Location: tecnico_ver_ticket.php?id=" . $id . "&asignado=ok");
    exit();
} catch (Exception $e) {
    die("Error al asignar el ticket: " . $e->getMessage());
}
?>
