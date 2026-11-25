<?php

// Leitura e validação simples dos campos enviados pelos formulários.
class Validador
{
    public const SEGMENTOS = ['industria', 'comercio', 'servicos'];
    public const PERFIS    = ['administrador', 'gestor', 'comprador', 'producao'];

    /** Campo de texto enviado por POST, sem espaços nas pontas. Se não for texto, devolve vazio. */
    public static function texto(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';
        return is_string($valor) ? trim($valor) : '';
    }

    /** Senha enviada por POST. Não remove espaços, porque eles podem fazer parte da senha. */
    public static function senha(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';
        return is_string($valor) ? $valor : '';
    }

    /** Número com vírgula ou ponto ("12,5" ou "12.5"). Devolve null se não for um número positivo. */
    public static function decimal(string $valor): ?float
    {
        $valor = str_replace(',', '.', trim($valor));
        return preg_match('/^\d+(\.\d+)?$/', $valor) ? (float) $valor : null;
    }

    /** Percentual entre 0 e 100. */
    public static function percentual(string $valor): ?float
    {
        $numero = self::decimal($valor);
        return ($numero !== null && $numero <= 100) ? $numero : null;
    }

    /** Login em minúsculas: letras, números, ponto, traço e sublinhado, de 3 a 50 caracteres. */
    public static function loginValido(string $login): bool
    {
        return (bool) preg_match('/^[a-z0-9._-]{3,50}$/', $login);
    }

    /** Senha de 8 caracteres ou mais (o bcrypt só considera os primeiros 72 bytes). */
    public static function senhaValida(string $senha): bool
    {
        return mb_strlen($senha) >= 8 && strlen($senha) <= 72;
    }

    public static function tamanho(string $texto, int $minimo, int $maximo): bool
    {
        $tamanho = mb_strlen($texto);
        return $tamanho >= $minimo && $tamanho <= $maximo;
    }
}