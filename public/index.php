<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Verifica se a aplicação está em modo de manutenção
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Registra o autoloader do Composer
require __DIR__.'/../vendor/autoload.php';

// Inicializa a aplicação Laravel
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Processa a requisição HTTP e envia a resposta
$app->handleRequest(Request::capture());
