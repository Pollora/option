# Pollora Option

A modern PHP package for WordPress option management with validation and immutable value objects.

## Installation

```bash
composer require pollora/option
```

## Quick Start

```php
use Pollora\Option\Application\Service\OptionService;
use Pollora\Option\Adapter\Out\WordPress\WordPressOptionRepository;
use Pollora\Option\Domain\Service\OptionValidationService;

$service = new OptionService(
    new WordPressOptionRepository,
    new OptionValidationService
);

// Get with default
$value = $service->get('site_title', 'My Site');

// Smart upsert (creates or updates)
$service->set('site_title', 'New Title');

// Check existence
if ($service->exists('api_key')) {
    $service->delete('api_key');
}
```

## Documentation

See [docs/options.md](docs/options.md) for full documentation.

## Testing

```bash
composer test
```

## License

GPL-2.0-or-later
