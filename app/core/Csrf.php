<?php

// Token que prova que o formulário veio do nosso site (protege contra envios forjados).
class Csrf
{
    public static function token(): string
    {
        if (!Session::get('csrf')) {
            Session::set('csrf', bin2hex(random_bytes(32)));
        }

        return Session::get('csrf');
    }

    /** Aceita o campo "csrf" do formulário ou o cabeçalho X-CSRF-Token (usado nos fetch). */
    public static function validar(): bool
    {
        $enviado = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $esperado = Session::get('csrf', '');

        return is_string($enviado) && $esperado !== '' && hash_equals($esperado, $enviado);
    }
}
