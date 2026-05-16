<?php

declare(strict_types=1);

namespace Pubvana\SiteHealth\Services;

use Pubvana\SiteHealth\Interfaces\CheckInterface;

class ConfigDefaultsCheck implements CheckInterface
{
    public function __construct(private string $projectRoot) {}

    public function run(): CheckResult
    {
        $configFile = $this->projectRoot . '/app/config/config.php';
        $sampleFile = $this->projectRoot . '/app/config/config_sample.php';

        if (!file_exists($configFile)) {
            return new CheckResult(
                id: 'config-defaults',
                name: 'Configuration File',
                category: CheckResult::CAT_CONFIGURATION,
                status: CheckResult::CRITICAL,
                message: 'No config.php found. The application is running without proper configuration.',
                remediation: 'Copy config_sample.php to config.php and fill in your settings.',
            );
        }

        if (file_exists($configFile)) {
            $lines = file($configFile, FILE_IGNORE_NEW_LINES);
            $indicators = [
                'your_client_id'     => 'OAuth client_id placeholder',
                'your_client_secret' => 'OAuth client_secret placeholder',
                'your_redirect_uri'  => 'OAuth redirect_uri placeholder',
            ];

            $found = [];
            foreach ($lines as $line) {
                $trimmed = ltrim($line);
                if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
                    continue;
                }
                foreach ($indicators as $needle => $label) {
                    if (str_contains($line, $needle) && !isset($found[$needle])) {
                        $found[$needle] = $label;
                    }
                }
            }

            if (!empty($found)) {
                return new CheckResult(
                    id: 'config-defaults',
                    name: 'Configuration File',
                    category: CheckResult::CAT_CONFIGURATION,
                    status: CheckResult::WARNING,
                    message: 'Config contains active placeholder values: ' . implode(', ', array_values($found)),
                    remediation: 'Replace placeholder values with real credentials or remove the config sections.',
                );
            }
        }

        return new CheckResult(
            id: 'config-defaults',
            name: 'Configuration File',
            category: CheckResult::CAT_CONFIGURATION,
            status: CheckResult::PASS,
            message: 'Configuration file is present and contains no obvious placeholder values.',
        );
    }
}
