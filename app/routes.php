<?php
declare(strict_types=1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use App\Clippings\Controllers\IndexController;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', IndexController::class . ':indexGet')
        ->setName('indexGet');
    $app->post('/', IndexController::class . ':indexPost')
        ->setName('indexPost');
};
