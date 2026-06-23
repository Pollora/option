# Options

A modern package for WordPress option management with validation, immutable value objects, and hexagonal architecture.

## Basic Usage

```php
use Pollora\Option\Application\Service\OptionService;
use Pollora\Option\Adapter\Out\WordPress\WordPressOptionRepository;
use Pollora\Option\Domain\Service\OptionValidationService;

$service = new OptionService(
    new WordPressOptionRepository,
    new OptionValidationService
);

$value = $service->get('site_title', 'Default');
$service->set('site_title', 'New Title');
$service->update('site_title', 'Updated Title');
$service->delete('old_option');
$exists = $service->exists('site_title');
```

## Working with Option Objects

The `Option` value object is immutable — use `withValue()` and `withAutoload()` to derive new instances:

```php
use Pollora\Option\Domain\Model\Option;

$option = new Option('api_credentials', [
    'key' => 'abc123',
    'secret' => 'xyz789'
], autoload: false);

$updated = $option->withValue(['key' => 'new123', 'secret' => 'new789']);
```

## Validation

Keys and values are validated automatically by `OptionValidationService`:

- **Keys**: must be 1–191 characters, no null bytes
- **Values**: must be serializable (no resources, no closures)

```php
use Pollora\Option\Domain\Exception\InvalidOptionException;

try {
    $service->set('', 'value'); // throws: key cannot be empty
} catch (InvalidOptionException $e) {
    // handle
}
```

## Error Handling

```php
use Pollora\Option\Domain\Exception\OptionNotFoundException;
use Pollora\Option\Domain\Exception\InvalidOptionException;

try {
    $value = $service->get('required_key');
    if ($value === null) {
        throw new OptionNotFoundException('required_key');
    }
} catch (OptionNotFoundException $e) {
    // "Option 'required_key' not found"
}
```

## Architecture

This package follows Hexagonal Architecture (Ports & Adapters):

- **Domain** — `Option` value object, `OptionValidationService`, exceptions
- **Contract** — `OptionRepositoryInterface` port
- **Application** — `OptionService` orchestration
- **Adapter** — `WordPressOptionRepository` (WordPress `get_option()` / `update_option()`)
