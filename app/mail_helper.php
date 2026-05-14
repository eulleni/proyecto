<?php
if (!function_exists('enviarCorreoSistema')) {
    function enviarCorreoSistema(string $destinatario, string $asunto, string $mensaje, ?string $replyTo = null): bool
    {
        $destinatario = trim($destinatario);
        if ($destinatario === '' || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $from = getenv('MAIL_FROM') ?: 'soporte@example.local';
        $defaultReplyTo = getenv('MAIL_REPLY_TO') ?: 'soporte@example.local';
        $headers = [];
        $headers[] = 'From: Ticketing <' . $from . '>';
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        } else {
            $headers[] = 'Reply-To: ' . $defaultReplyTo;
        }
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';

        return @mail($destinatario, $asunto, $mensaje, implode("\r\n", $headers));
    }
}

if (!function_exists('obtenerCorreoAdminPrincipal')) {
    function obtenerCorreoAdminPrincipal(): string
    {
        return getenv('ADMIN_EMAIL') ?: 'admin@example.local';
    }
}
?>
