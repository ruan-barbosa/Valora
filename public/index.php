<?php
declare(strict_types=1);

require __DIR__ . '/../app/core/bootstrap.php';

$router = new Router();
require APP_PATH . '/routes.php';
$router->despachar($_SERVER['REQUEST_METHOD'], caminho_requisicao());