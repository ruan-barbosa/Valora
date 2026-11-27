<?php

// Sessão do PHP com cookie mais seguro, mais as mensagens que valem para uma única tela (flash).
class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Evita aceitar IDs de sessão fornecidos pelo cliente que não foram criados pelo PHP.
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        session_name('valora_sessao');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function get(string $chave, $padrao = null)
    {
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function set(string $chave, $valor): void
    {
        $_SESSION[$chave] = $valor;
    }

    public static function remover(string $chave): void
    {
        unset($_SESSION[$chave]);
    }

    /** Guarda um valor que será lido uma única vez (ex.: mensagem de sucesso). */
    public static function flash(string $chave, $valor): void
    {
        $_SESSION['_flash'][$chave] = $valor;
    }

    /** Lê e apaga um valor guardado com flash(). */
    public static function pegarFlash(string $chave, $padrao = null)
    {
        $valor = $_SESSION['_flash'][$chave] ?? $padrao;
        unset($_SESSION['_flash'][$chave]);
        return $valor;
    }

    /** Troca o identificador da sessão (usar depois do login). */
    public static function regenerar(): void
    {
        session_regenerate_id(true);
    }

    public static function destruir(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'] ?: '/',
                'domain'   => $p['domain'],
                'secure'   => (bool) $p['secure'],
                'httponly' => (bool) $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
}
