<p align="center">
  <a href="https://pollora.dev">
    <img src="https://raw.githubusercontent.com/Pollora/.github/main/brand/banners/option.png" width="100%" alt="Pollora Option: validated WordPress options with a small static API">
  </a>
</p>

<p align="center">
  <a href="https://packagist.org/packages/pollora/option"><img src="https://img.shields.io/packagist/v/pollora/option" alt="Latest version"></a>
  <a href="https://packagist.org/packages/pollora/option"><img src="https://img.shields.io/packagist/dt/pollora/option" alt="Total downloads"></a>
  <a href="https://github.com/Pollora/option/actions/workflows/tests.yml"><img src="https://github.com/Pollora/option/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/Pollora/option" alt="License"></a>
</p>

A dependency-free PHP wrapper around the WordPress options API. Keys and values are validated before they reach the database, `set()` creates or updates in one call, and options are modeled as immutable value objects, so a typo'd key or an unserializable value fails loudly instead of being stored silently.

> Part of [Pollora](https://pollora.dev), the Laravel framework for WordPress. In a Pollora project it is already installed: use the `Pollora\Support\Facades\Option` facade instead. The standalone class emits a notice when the framework is present.

## Installation

```bash
composer require pollora/option
```

Requires PHP 8.3+ and WordPress (the adapter calls `get_option()`, `add_option()`, `update_option()` and `delete_option()`).

## Quick start

```php
use Pollora\Option\Option;

// Read, with a default when the option does not exist
$title = Option::get('site_title', 'My Site');

// Create or update (smart upsert)
Option::set('site_title', 'New Title');

// Update an existing option
Option::update('posts_per_page', 20);

// Check, then delete
if (Option::exists('api_key')) {
    Option::delete('api_key');
}
```

## What you get

- **Five static methods**: `get()`, `set()`, `update()`, `delete()`, `exists()`; writes return `bool`.
- **Key validation**: 1 to 191 characters, no null bytes, otherwise `InvalidOptionException`.
- **Value validation**: resources and objects that cannot be serialized (closures, for instance) are refused before reaching WordPress.
- **Immutable value object**: `Domain\Model\Option` carries `key`, `value` and `autoload`, derived with `withValue()` and `withAutoload()`.
- **Hexagonal core**: `OptionService` works against an `OptionRepositoryInterface` port; `WordPressOptionRepository` is the WordPress adapter, and you can swap it in tests.

```php
use Pollora\Option\Domain\Exception\InvalidOptionException;

try {
    Option::set('', 'value');
} catch (InvalidOptionException $e) {
    // "Option key cannot be empty"
}
```

## Documentation

- [docs/options.md](docs/options.md): the service, value objects, validation and error handling.
- Options in a Pollora project: [Options](https://pollora.dev/content/options/).

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome: see the [contributing guide](https://github.com/Pollora/.github/blob/main/CONTRIBUTING.md). Report security issues privately, as described in the [security policy](https://github.com/Pollora/.github/blob/main/SECURITY.md).

## License

Pollora Option is open-source software licensed under the [MIT license](LICENSE). © [RuBee group](https://rubee.group)
