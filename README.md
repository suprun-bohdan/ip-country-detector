# IP Country Detector

A robust Laravel package for detecting country information from IP addresses, with support for IPv4, IPv6, and VPN detection.

## Features

- Country detection from IP addresses (IPv4 and IPv6)
- VPN and proxy detection
- Timezone-based country detection
- Redis caching support
- Database-backed IP range lookup
- Fallback to external API service
- Comprehensive error handling
- Type-safe implementation

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher
- Redis server (for caching)
- MySQL/PostgreSQL database

## Installation

1. Install the package via Composer:

```bash
composer require wtg/ipcountrydetector
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --provider="IpCountryDetector\IpCountryDetectorServiceProvider" --tag="config"
```

3. Run the migrations:

```bash
php artisan migrate
```

4. Configure your Redis connection in `.env`:

```
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Configuration

The package configuration file (`config/ipcountry.php`) contains the following options:

```php
return [
    'redis' => [
        'prefix' => 'ip_country',
        'ttl' => 86400, // 24 hours
    ],
    'api' => [
        'timeout' => 5,
        'retry_attempts' => 3,
    ],
];
```

## Usage

### Basic Usage

```php
use IpCountryDetector\Services\IPCheckService;

class YourController
{
    public function __construct(
        private readonly IPCheckService $ipService
    ) {}

    public function detectCountry(Request $request)
    {
        $ipAddress = $request->ip();
        $country = $this->ipService->getCountryByIp($ipAddress);
        
        return response()->json(['country' => $country]);
    }
}
```

### VPN Detection

```php
$isVpn = $this->ipService->isVpnIp($ipAddress);
```

### Timezone to Country

```php
$country = $this->ipService->getCountryByTimezone('America/New_York');
```

### IP Validation

```php
$isValid = $this->ipService->validateIpAddress($ipAddress);
```

## Database Structure

The package uses a single table `ip_country` with the following structure:

```sql
CREATE TABLE ip_country (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_ip BIGINT NOT NULL,
    last_ip BIGINT NOT NULL,
    country VARCHAR(2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX ip_range_idx (first_ip, last_ip)
);
```

## Testing

The package includes a comprehensive test suite. To run the tests, follow these steps:

1. Install dependencies:
```bash
composer install
```

2. Run all tests:
```bash
composer test
```

3. Run specific test suites:
```bash
# Run only unit tests
./vendor/bin/phpunit --testsuite Unit

# Run only feature tests
./vendor/bin/phpunit --testsuite Feature
```

4. Run specific test files:
```bash
# Run IPCheckService tests
./vendor/bin/phpunit tests/Unit/Services/IPCheckServiceTest.php

# Run IPCacheService tests
./vendor/bin/phpunit tests/Unit/Services/IPCacheServiceTest.php

# Run feature tests
./vendor/bin/phpunit tests/Feature/IPCountryDetectorTest.php
```

5. Generate coverage reports:
```bash
# Generate HTML coverage report
./vendor/bin/phpunit --coverage-html coverage

# Generate text coverage report
./vendor/bin/phpunit --coverage-text
```

6. Run tests with specific filters:
```bash
# Run tests matching a specific name pattern
./vendor/bin/phpunit --filter testGetCountryByIp
```

7. Run tests in verbose mode:
```bash
./vendor/bin/phpunit -v
```

The test suite includes:
- Unit tests for IPCheckService
- Unit tests for IPCacheService
- Feature tests for package integration
- Database migration tests
- Service provider tests
- Configuration tests

Test Environment:
- Uses SQLite in-memory database for testing
- Mocks external dependencies using Mockery
- Uses Laravel's testing framework
- Includes PHPUnit assertions

## Error Handling

The package implements comprehensive error handling:

- Invalid IP addresses throw `InvalidArgumentException`
- API failures are logged and handled gracefully
- Cache errors are logged but don't break the application
- Database errors are properly caught and logged

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
