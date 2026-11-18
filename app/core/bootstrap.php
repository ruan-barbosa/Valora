<?php

declare(strict_types=1);

// Preparação comum a todas as requisições: caminhos, autoload, erros, sessão.

define('BASE_PATH', dirname(__DIR__, 2));
define('APP_PATH', BASE_PATH . '/app');

// Carrega as classes sozinho: new Item() procura app/models/Item.php, e assim por diante.
spl_autoload_register(function (string $classe): void {
    foreach (['core', 'controllers', 'models', 'services'] as $pasta) {
        $arquivo = APP_PATH . '/' . $pasta . '/' . $classe . '.php';
        if (is_file($arquivo)) {
            require $arquivo;
            return;
        }
    }
});

require APP_PATH . '/core/helpers.php';

date_default_timezone_set('America/Sao_Paulo');

try {
    $debug = Config::debug();
} catch (Throwable $erro) {
    $debug = false;
}

// Em produção os erros vão para o log e nunca para a tela.
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $erro) use ($debug): void {
    error_log('Valora: ' . $erro->getMessage() . ' em ' . $erro->getFile() . ':' . $erro->getLine());
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Erro interno</h1>';
    echo $debug
        ? '<pre>' . e($erro->getMessage() . "\n" . $erro->getFile() . ':' . $erro->getLine()) . '</pre>'
        : '<p>Tente novamente mais tarde.</p>';
});

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

Session::iniciar();
