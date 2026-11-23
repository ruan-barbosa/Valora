<?php

// Funções usadas nas views e nos controllers.

/**
 * Parte do endereço que vem antes das rotas.
 * Exemplos: "" (domínio na raiz), "/public" (acessado por /public/), "/Valora" (projeto em htdocs/Valora).
 */
function base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    // Onde a pasta public/ está em relação à raiz do servidor: "", "/public" ou "/Valora/public".
    $raizServidor = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $pastaPublic = realpath(__DIR__ . '/../../public') ?: '';
    $publicWeb = ($raizServidor !== '' && str_starts_with($pastaPublic, $raizServidor))
        ? str_replace('\\', '/', substr($pastaPublic, strlen($raizServidor)))
        : '';

    if ($publicWeb === '') {
        $base = '';                                         // o servidor já aponta para a pasta public/
    } elseif ($caminho === $publicWeb || str_starts_with($caminho, $publicWeb . '/')) {
        $base = $publicWeb;                                 // acessado por .../public/...
    } else {
        $base = rtrim(str_replace('\\', '/', dirname($publicWeb)), '/');   // acessado pela raiz do projeto
    }

    return $base;
}

/** Endereço de uma rota. Ex.: url('insumos') */
function url(string $caminho = ''): string
{
    return base_url() . '/' . ltrim($caminho, '/');
}

/** Endereço de um arquivo de public/assets. Ex.: asset('css/app.css') */
function asset(string $caminho): string
{
    return base_url() . '/assets/' . ltrim($caminho, '/');
}

/** Escapa texto para mostrar com segurança em HTML. */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Caminho da requisição sem a base. Ex.: /fichas/editar/3 */
function caminho_requisicao(): string
{
    $caminho = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $base = base_url();

    if ($base !== '' && str_starts_with($caminho, $base)) {
        $caminho = substr($caminho, strlen($base));
    }

    return '/' . trim($caminho, '/');
}