<?php

declare(strict_types=1);

namespace Pubvana\SiteHealth\Interfaces;

use Pubvana\SiteHealth\Services\CheckResult;

interface CheckInterface
{
    public function run(): CheckResult;
}
