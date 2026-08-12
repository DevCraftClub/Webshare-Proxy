# WebShare Proxy API Wrapper

PHP wrapper for the [WebShare Proxy API](https://apidocs.webshare.io) with rate-limit retries, GET response caching, filtering, pagination iterators, and typed request helpers.

Domain terms: see [CONTEXT.md](CONTEXT.md).

## Requirements

- PHP >= 8.3
- Composer

## Installation

```bash
composer require devcraft/webshare-io-api-wrapper
```

## Configuration

```bash
export API_KEY="your_webshare_api_key"
```

Or `.env`:

```
API_KEY=your_webshare_api_key
API_URL=https://proxy.webshare.io/api/
API_VERSION=v2
CACHE_ENABLED=1
CACHE_LIFETIME=3600
CACHE_DIRECTORY=./cache
ITERATE_MAX_PAGES=100
DEBUG=0
```

## Quick start

```php
<?php

require_once 'vendor/autoload.php';

use Devcraft\Webshare\Classes\App;
use Devcraft\Webshare\Enums\FilterOperator;

App::init();

/** @var \Devcraft\Webshare\Requests\ProxyRequest $proxies */
$proxies = App::Requests('Proxy');
$proxies->query()
    ->withFilter('mode', 'direct')
    ->withPageSize(25)
    ->withFilter('country_code', ['US', 'DE'], FilterOperator::IN);

$list = $proxies->getList();

foreach ($proxies->iterate() as $proxy) {
    echo $proxy->getConnectionString(backbone: true), "\n";
}
```

`App::Client()` returns the shared HTTP client. `App::Requests('Profile'|'Notification'|…)` auto-discovers request classes.

## Request surface

| App::Requests key | Coverage |
|-------------------|----------|
| `Proxy` | list, iterate, download, refresh |
| `Profile` | profile + preferences |
| `Notification` | list/retrieve/dismiss/restore |
| `ProxyReplacement` | list/retrieve/create (v3) |
| `ReplacedProxy` | list/download |
| `ProxyConfig` | config/status/listStats/update (v3) |
| `IpAuthorization` | list/iterate/CRUD + whatsMyIp |
| `ProxyStats` | list/aggregate/activities/download |
| `DownloadToken` | get/reset token by scope |
| `ApiKey` | list/iterate/CRUD |
| `SubUser` | list/iterate/CRUD/refresh + `masqueradeAs()` |

**Proxy Connection** is not a REST resource — use `Proxy::getConnectionString()` (backbone/direct). See CONTEXT.md.

## Caching

Successful **GET** responses are cached (`CACHE_LIFETIME`). Mutations invalidate the resource tag. Paths containing `/download/` are never cached. Bypass with `forceRefresh: true` on `request()` / `Client::send()`, or `CACHE_ENABLED=0`.

## Filtering, ordering & search

```php
$proxies->query()
    ->withFilter('country_code', ['US', 'DE'], FilterOperator::IN)
    ->withFilter('created_at', '2024-01-01', FilterOperator::GT)
    ->withOrderBy('-created_at', 'country_code')
    ->withSearch('residential');
```

| Operator | API syntax |
|----------|------------|
| `EQUAL` | `?field=value` |
| `IN` | `?field__in=a,b` |
| `GT` / `LT` | `?field__gt=` / `__lt=` |
| `CONTAINS` | `?field__contains=` |

## Pagination

Manual pages via `withPage` / `withStartingAfter`, or `iterate()` / `PageIterator::items()` (default max pages from `ITERATE_MAX_PAGES`, default 100).

## Sub-user masquerade

```php
App::Requests('SubUser')->masqueradeAs(7);
// subsequent Client calls send X-Subuser: 7
App::Requests('SubUser')->masqueradeAs(null);
```

## Error handling

```php
use Devcraft\Webshare\Exceptions\ApiException;
use Devcraft\Webshare\Exceptions\RateLimitException;

try {
    App::Requests('Proxy')->query()->withFilter('mode', 'direct');
    App::Requests('Proxy')->getList();
} catch (RateLimitException $e) {
    echo "Retry after {$e->retryAfterSeconds}s\n";
} catch (ApiException $e) {
    foreach ($e->getValidationErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "$field: {$error['message']}\n";
        }
    }
}
```

429/5xx: up to 3 retries with exponential backoff; `Retry-After` preferred; `x-ratelimit-reset` timestamps are converted and clamped (1–120s).

## License

AGPL-3.0-or-later — see [LICENSE](LICENSE).
