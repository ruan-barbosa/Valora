<?php

// Funções usadas nas views e nos controllers.

/** Parte do endereço que vem antes das rotas: "" no domínio raiz ou "/public" quando acessado por /public/. */
function base_url(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $pastaDoScript = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    $acessadoPelaPasta = $pastaDoScript !== ''
        && ($caminho === $pastaDoScript || str_starts_with($caminho, $pastaDoScript . '/'));

    $base = $acessadoPelaPasta ? $pastaDoScript : '';
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
