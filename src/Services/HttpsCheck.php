<?php

declare(strict_types=1);

namespace Pubvana\SiteHealth\Services;

use Pubvana\SiteHealth\Interfaces\CheckInterface;

use flight\Engine;

class HttpsCheck implements CheckInterface
{
    public function __construct(private Engine $app) {}

    public function run(): CheckResult
    {
        $baseUrl = $this->app->get('flight.base_url') ?? '';
        $isHttps = str_starts_with($baseUrl, 'https://');
        $forceHttps = (bool) $this->app->get('flight.force_https');

        if ($isHttps && $forceHttps) {
            return new CheckResult(
                id: 'https',
                name: 'HTTPS',
                category: CheckResult::CAT_SECURITY,
                status: CheckResult::PASS,
                message: 'Site is configured for HTTPS with forced redirect enabled.',
            );
        }

        if ($isHttps && !$forceHttps) {
            return new CheckResult(
                id: 'https',
                name: 'HTTPS',
                category: CheckResult::CAT_SECURITY,
                status: CheckResult::WARNING,
                message: 'Base URL uses HTTPS but force_https is not enabled. HTTP requests will not be redirected.',
                remediation: "Set flight.force_https to true in your config to redirect all HTTP requests to HTTPS.",
            );
        }

        $env = defined('ENV') ? ENV : 'production';
        if ($env === 'development') {
            return new CheckResult(
                id: 'https',
                name: 'HTTPS',
                category: CheckResult::CAT_SECURITY,
                status: CheckResult::WARNING,
                message: 'Site is not using HTTPS. Acceptable for local development only.',
                remediation: 'Configure HTTPS before deploying to production.',
            );
        }

        return new CheckResult(
            id: 'https',
            name: 'HTTPS',
            category: CheckResult::CAT_SECURITY,
            status: CheckResult::CRITICAL,
            message: 'Site is not using HTTPS in a non-development environment.',
            remediation: 'Install an SSL certificate, update base_url to https://, and set flight.force_https to true.',
        );
    }
}
