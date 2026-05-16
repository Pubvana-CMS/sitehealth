<?php

use Enlivenapp\FlightCsrf\Middlewares\CsrfMiddleware;
use Enlivenapp\FlightShield\Middlewares\SessionAuthMiddleware;
use Pubvana\SiteHealth\Controllers\HealthAdminController;

/** @var \flight\net\Router $router */
/** @var \flight\Engine $app */
/** @var string $configPrepend */

$router->get('/site-health', function () use ($app, $configPrepend) {
    (new HealthAdminController($app, $configPrepend))->index();
})->addMiddleware(new SessionAuthMiddleware($app));

$router->post('/site-health/rerun', function () use ($app, $configPrepend) {
    (new HealthAdminController($app, $configPrepend))->rerun();
})->addMiddleware(new SessionAuthMiddleware($app))
  ->addMiddleware(new CsrfMiddleware($app));
