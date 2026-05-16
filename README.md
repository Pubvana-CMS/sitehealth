[![Stable? Not Quite Yet](https://img.shields.io/badge/stable%3F-not%20quite%20yet-blue?style=for-the-badge)](https://packagist.org/packages/pubvana/sitehealth)
[![License](https://img.shields.io/packagist/l/pubvana/sitehealth?style=for-the-badge)](https://packagist.org/packages/pubvana/sitehealth)
[![PHP Version](https://img.shields.io/packagist/php-v/pubvana/sitehealth?style=for-the-badge)](https://packagist.org/packages/pubvana/sitehealth)
[![Monthly Downloads](https://img.shields.io/packagist/dm/pubvana/sitehealth?style=for-the-badge)](https://packagist.org/packages/pubvana/sitehealth)
[![Total Downloads](https://img.shields.io/packagist/dt/pubvana/sitehealth?style=for-the-badge)](https://packagist.org/packages/pubvana/sitehealth)
[![GitHub Issues](https://img.shields.io/github/issues/Pubvana-CMS/sitehealth?style=for-the-badge)](https://github.com/Pubvana-CMS/sitehealth/issues)
[![Contributors](https://img.shields.io/github/contributors/Pubvana-CMS/sitehealth?style=for-the-badge)](https://github.com/Pubvana-CMS/sitehealth/graphs/contributors)
[![Latest Release](https://img.shields.io/github/v/release/Pubvana-CMS/sitehealth?style=for-the-badge)](https://github.com/Pubvana-CMS/sitehealth/releases)
[![Contributions Welcome](https://img.shields.io/badge/contributions-welcome-blue?style=for-the-badge)](https://github.com/Pubvana-CMS/sitehealth/pulls)

# Pubvana Site Health

**I noticed folks downloading some of these packages. I'm super grateful, Thank You!  I would like to let folks know until this notice disappears I'm doing a lot of breaking changes without worrying about them.  Once versions are up around 0.5.x things should settle down.**

Site health and diagnostics package for [Pubvana](https://pubvanacms.com) CMS. Surfaces configuration and security problems early so admins can fix issues before they become incidents.

## Requirements

- PHP 8.1+
- [Flight School](https://github.com/enlivenapp/flight-school) ^0.3
- [Flight Settings](https://github.com/enlivenapp/flight-settings) ^0.2

## Installation

```bash
composer require pubvana/sitehealth
```

Enable in `app/config/config.php`:

```php
'plugins' => [
    'pubvana/sitehealth' => [
        'enabled'  => true,
        'priority' => 50,
    ],
],
```

## Flight School Config

This package uses Flight School's return-array config format. `src/Config/Config.php` returns the package defaults as an array, Flight School stores that array under `pubvana.sitehealth` on `$app`, and the current admin route prefix is defined there with `'routePrepend' => 'site-health'`.

## Service

Mapped as `$app->health()`. Provides:

- **runAll()** - Run all registered health checks (returns cached results unless forced)
- **clearCache()** - Invalidate cached check results
- **addCheck()** - Register additional checks programmatically
- **groupByCategory()** - Group results by category for display
- **dashboardCards()** - Returns dashboard card data when issues exist, empty array when all clear

## Checks

14 built-in checks across 4 categories:

**Environment** (`CheckResult::CAT_ENVIRONMENT`) - PHP version, required/recommended PHP extensions, database connectivity and version, disk space on uploads partition

**Security** (`CheckResult::CAT_SECURITY`) - HTTPS and forced redirect, debug mode in production, config file permissions, Shield authentication installed, session configuration (httponly, secure, samesite, lifetime)

**Configuration** (`CheckResult::CAT_CONFIGURATION`) - Required settings populated (base URL, site name), writable directories (uploads, cache, log), config file not using placeholder values

**Plugins** (`CheckResult::CAT_PLUGINS`) - All plugin migrations current, all plugin composer dependencies satisfied

## Severity Levels

Each check returns one of three levels:

| Level | Constant | Meaning |
|-------|----------|---------|
| **Critical** | `CheckResult::CRITICAL` | Something is broken or dangerously misconfigured. Needs immediate attention. |
| **Warning** | `CheckResult::WARNING` | Not ideal but not broken. Should be addressed when convenient. |
| **Pass** | `CheckResult::PASS` | Meets requirements. No action needed. |

## Dashboard Integration

When issues exist, a card appears on the admin dashboard linking to the detail page. When everything passes, nothing is shown on the dashboard.

## Extensibility

Other plugins can register their own health checks. Both approaches go in your plugin's `src/Plugin.php` `register()` method.

Via adext:

```php
// In src/Plugin.php register()
$app->adext('health', 'checks', 'vendor.plugin-name', [
    'priority' => 50,
    'callable' => fn() => (new YourCustomCheck())->run(),
]);
```

Custom checks implement `Pubvana\SiteHealth\Interfaces\CheckInterface`:

```php
use Pubvana\SiteHealth\Interfaces\CheckInterface;
use Pubvana\SiteHealth\Services\CheckResult;

class YourCustomCheck implements CheckInterface
{
    public function run(): CheckResult
    {
        return new CheckResult(
            id: 'your-check-id',
            name: 'Your Check Name',
            category: CheckResult::CAT_ENVIRONMENT,
            status: CheckResult::PASS,
            message: 'Everything is fine.',
            remediation: 'What to do if it fails.',
        );
    }
}
```

## Config

| Key | Default | Description |
|-----|---------|-------------|
| `cache_ttl` | `3600` | Seconds to cache check results before re-running |

## Admin Routes

- `GET /admin/site-health` - Detail page with all checks and remediation
- `POST /admin/site-health/rerun` - Clear cache and re-run all checks

## License

MIT
