<?php

declare(strict_types=1);

namespace Pubvana\SiteHealth\Services;

use Pubvana\SiteHealth\Interfaces\CheckInterface;

use flight\Engine;

class RequiredSettingsCheck implements CheckInterface
{
    public function __construct(private Engine $app) {}

    public function run(): CheckResult
    {
        $missing = [];

        $baseUrl = $this->app->get('flight.base_url') ?? '';
        if (empty($baseUrl) || $baseUrl === 'http://example.com' || $baseUrl === 'https://example.com') {
            $missing[] = 'flight.base_url (still set to placeholder)';
        }

        $siteName = null;
        if (method_exists($this->app, 'settings') && $this->app->settings() !== null) {
            $siteName = $this->app->settings()->get('CMS.siteName');
        }
        if (empty($siteName)) {
            $siteName = $this->app->get('CMS.siteName');
        }
        if (empty($siteName) || $siteName === 'Pubvana' || $siteName === 'My Site') {
            $missing[] = 'CMS.siteName (still using default)';
        }

        if (!empty($missing)) {
            return new CheckResult(
                id: 'required-settings',
                name: 'Required Settings',
                category: CheckResult::CAT_CONFIGURATION,
                status: CheckResult::WARNING,
                message: 'Settings using default or placeholder values: ' . implode(', ', $missing),
                remediation: 'Update these settings in Admin > Settings with your actual site values.',
            );
        }

        return new CheckResult(
            id: 'required-settings',
            name: 'Required Settings',
            category: CheckResult::CAT_CONFIGURATION,
            status: CheckResult::PASS,
            message: 'All required settings are configured.',
        );
    }
}
