<?php

declare(strict_types=1);

namespace Pubvana\SiteHealth\Controllers;

use Pubvana\Admin\Controllers\AdminController;
use Pubvana\SiteHealth\Services\CheckResult;

class HealthAdminController extends AdminController
{
    public function index(): void
    {
        $data = $this->app->health()->runAll();
        $grouped = $this->app->health()->groupByCategory($data['results']);

        $this->render('health/index', [
            'pageTitle'  => 'Site Health',
            'results'    => $data['results'],
            'grouped'    => $grouped,
            'summary'    => $data['summary'],
            'cachedAt'   => $data['cached_at'],
            'categories' => [
                CheckResult::CAT_ENVIRONMENT   => ['label' => 'Environment', 'icon' => 'ti-server'],
                CheckResult::CAT_SECURITY      => ['label' => 'Security', 'icon' => 'ti-shield-lock'],
                CheckResult::CAT_CONFIGURATION => ['label' => 'Configuration', 'icon' => 'ti-settings'],
                CheckResult::CAT_PLUGINS       => ['label' => 'Plugins', 'icon' => 'ti-puzzle'],
            ],
        ]);
    }

    public function rerun(): void
    {
        $this->app->health()->clearCache();
        $this->app->health()->runAll(true);

        $this->app->redirect('/admin/site-health');
    }
}
