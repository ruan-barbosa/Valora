<?php

// Lê o config/config.php uma única vez.
class Config
{
    private static ?array $dados = null;

    public static function todos(): array
    {
        if (self::$dados === null) {
            $arquivo = BASE_PATH . '/config/config.php';
            if (!is_file($arquivo)) {
                throw new RuntimeException('config/config.php não encontrado.');
            }
            self::$dados = require $arquivo;
        }

        return self::$dados;
    }

    public static function get(string $chave, $padrao = null)
    {
        return self::todos()[$chave] ?? $padrao;
    }

    /** Modo de desenvolvimento: coloque 'debug' => true no config.php só no seu computador. */
    public static function debug(): bool
    {
        return (bool) self::get('debug', false);
    }
}
