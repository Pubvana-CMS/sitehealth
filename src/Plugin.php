<?php

declare(strict_types=1);

namespace Pubvana\SiteHealth;

use Enlivenapp\FlightSchool\PluginInterface;
use flight\Engine;
use flight\net\Router;
use Flight;
use Pubvana\SiteHealth\Services\HealthService;

class Plugin implements PluginInterface
{
    public function register(Engine $app, Router $router, array $config = []): void
    {
        $app->map('health', function () use ($app, $config) {
            static $instance = null;
            if ($instance === null) {
                $instance = new HealthService($app, Flight::db(), $config);
            }
            return $instance;
        });

        $app->adext('menu', 'tools', 'pubvana.sitehealth', [
            'label'    => 'Site Health',
            'icon'     => 'ti-stethoscope',
            'url'      => '/site-health',
            'priority' => 10,
        ]);

        $app->adext('page', 'dashboard.cards', 'pubvana.sitehealth', [
            'label'    => 'Site Health',
            'priority' => 5,
            'callable' => fn(array $context) => $app->health()->dashboardCards(),
        ]);
    }
}
