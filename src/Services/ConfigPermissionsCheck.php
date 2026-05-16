<?php

declare(strict_types=1);

namespace Pubvana\SiteHealth\Services;

use Pubvana\SiteHealth\Interfaces\CheckInterface;

class ConfigPermissionsCheck implements CheckInterface
{
    public function __construct(private string $configPath) {}

    public function run(): CheckResult
    {
        $displayPath = realpath($this->configPath) ?: $this->configPath;

        if (!file_exists($this->configPath)) {
            return new CheckResult(
                id: 'config-permissions',
                name: 'Config File Permissions',
                category: CheckResult::CAT_SECURITY,
                status: CheckResult::WARNING,
                message: 'Config file not found at expected path.',
                remediation: "Verify config file exists at: {$displayPath}",
            );
        }

        $perms = fileperms($this->configPath) & 0777;
        $worldReadable = ($perms & 0004) !== 0;
        $worldWritable = ($perms & 0002) !== 0;

        if ($worldWritable) {
            return new CheckResult(
                id: 'config-permissions',
                name: 'Config File Permissions',
                category: CheckResult::CAT_SECURITY,
                status: CheckResult::CRITICAL,
                message: sprintf('Config file is world-writable (permissions: %o). Anyone on the server can modify it.', $perms),
                remediation: 'Set permissions to 640 (owner read/write, group read, no world access). Via command line: chmod 640 ' . $displayPath . ', or adjust via your hosting panel/file manager.',
            );
        }

        if ($worldReadable) {
            return new CheckResult(
                id: 'config-permissions',
                name: 'Config File Permissions',
                category: CheckResult::CAT_SECURITY,
                status: CheckResult::WARNING,
                message: sprintf('Config file is world-readable (permissions: %o). Other users on the server can read credentials.', $perms),
                remediation: 'Set permissions to 640 (owner read/write, group read, no world access). Via command line: chmod 640 ' . $displayPath . ', or adjust via your hosting panel/file manager.',
            );
        }

        return new CheckResult(
            id: 'config-permissions',
            name: 'Config File Permissions',
            category: CheckResult::CAT_SECURITY,
            status: CheckResult::PASS,
            message: sprintf('Config file permissions are secure (%o).', $perms),
        );
    }
}
