<?php
declare(strict_types=1);

// Servidor embutido do PHP (php -S): entrega arquivos reais (css, js, imagens) sem passar pelo roteador.
if (PHP_SAPI === 'cli-server') {
    $arquivo = __DIR__ . (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
    if (is_file($arquivo)) {
        return false;
    }
}

require __DIR__ . '/../app/core/bootstrap.php';

$router = new Router();
require APP_PATH . '/routes.php';
$router->despachar($_SERVER['REQUEST_METHOD'], caminho_requisicao());