# Pollora Option

A modern PHP package for WordPress option management with validation and immutable value objects.

## Installation

```bash
composer require pollora/option
```

## Quick Start

```php
use Pollora\Option\Option;

// Get with default
$value = Option::get('site_title', 'My Site');

// Smart upsert (creates or updates)
Option::set('site_title', 'New Title');

// Check existence
if (Option::exists('api_key')) {
    Option::delete('api_key');
}

// Update existing
Option::update('posts_per_page', 20);
```

> **Pollora framework users:** When the framework is available, prefer the Laravel facade `Pollora\Support\Facades\Option` for full DI container support. A notice is emitted if you use the standalone class within the framework.

## Documentation

See [docs/options.md](docs/options.md) for full documentation.

## Testing

```bash
composer test
```

## License

MIT — see [LICENSE](LICENSE).
