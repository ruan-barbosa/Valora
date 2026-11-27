<?php

// Token que prova que o formulário veio do nosso site (protege contra envios forjados).
class Csrf
{
    public static function token(): string
    {
        $token = Session::get('csrf');
        if (!is_string($token) || $token === '') {
            return self::renovar();
        }

        return $token;
    }

    /** Gera um token novo após eventos sensíveis, como login e troca de senha. */
    public static function renovar(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set('csrf', $token);
        return $token;
    }

    /** Aceita o campo "csrf" do formulário ou o cabeçalho X-CSRF-Token (usado nos fetch). */
    public static function validar(): bool
    {
        $enviado = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $esperado = Session::get('csrf', '');

        return is_string($enviado) && $esperado !== '' && hash_equals($esperado, $enviado);
    }
}
