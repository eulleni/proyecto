<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pagina_actual = basename($_SERVER['PHP_SELF']);
function activoTecnico($archivo, $pagina_actual) {
    return $archivo === $pagina_actual ? 'active' : '';
}
?>

<div class="navbar">
    <a class="<?= activoTecnico('panel_tecnico.php', $pagina_actual) ?>" href="panel_tecnico.php">Inicio técnico</a>
    <a class="<?= activoTecnico('tickets_sin_asignar.php', $pagina_actual) ?>" href="tickets_sin_asignar.php">Tickets sin asignar</a>
    <a class="<?= activoTecnico('mis_tickets_tecnico.php', $pagina_actual) ?>" href="mis_tickets_tecnico.php">Mis tickets asignados</a>
    <a class="<?= activoTecnico('todos_tickets_tecnico.php', $pagina_actual) ?>" href="todos_tickets_tecnico.php">Todos los tickets</a>
    <a href="logout_tecnico.php" class="logout">Cerrar sesión</a>
</div>
