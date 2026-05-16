<?php

declare(strict_types=1);

namespace Pubvana\SiteHealth\Services;

use Pubvana\SiteHealth\Interfaces\CheckInterface;

class WritableDirectoriesCheck implements CheckInterface
{
    private array $directories;

    public function __construct(string $projectRoot)
    {
        $this->directories = [
            'uploads' => $projectRoot . '/public/uploads',
            'cache'   => $projectRoot . '/app/cache',
            'log'     => $projectRoot . '/app/log',
        ];
    }

    public function run(): CheckResult
    {
        $notWritable = [];
        $notExist = [];

        foreach ($this->directories as $label => $path) {
            if (!is_dir($path)) {
                $notExist[] = $label;
            } elseif (!is_writable($path)) {
                $notWritable[] = $label;
            }
        }

        if (!empty($notWritable)) {
            return new CheckResult(
                id: 'writable-directories',
                name: 'Writable Directories',
                category: CheckResult::CAT_CONFIGURATION,
                status: CheckResult::CRITICAL,
                message: 'Directories exist but are not writable: ' . implode(', ', $notWritable),
                remediation: 'The web server needs write access to these directories. Via command line: chown www-data:www-data <dir> && chmod 775 <dir>, or use your hosting panel/file manager.',
            );
        }

        if (!empty($notExist)) {
            return new CheckResult(
                id: 'writable-directories',
                name: 'Writable Directories',
                category: CheckResult::CAT_CONFIGURATION,
                status: CheckResult::WARNING,
                message: 'Directories do not exist: ' . implode(', ', $notExist),
                remediation: 'Create the missing directories and ensure the web server can write to them.',
            );
        }

        return new CheckResult(
            id: 'writable-directories',
            name: 'Writable Directories',
            category: CheckResult::CAT_CONFIGURATION,
            status: CheckResult::PASS,
            message: 'All required directories exist and are writable.',
        );
    }
}
