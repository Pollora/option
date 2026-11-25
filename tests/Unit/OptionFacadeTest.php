<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use Pollora\Option\Domain\Exception\InvalidOptionException;
use Pollora\Option\Option;
use Pollora\Option\Tests\Fixtures\RecordedNotices;
use Pollora\Option\Tests\Fixtures\StoredOptions;

// The notices raised while a test runs; a typed holder, shared with the closures below.
$notices = new RecordedNotices;

// The options the stubbed WordPress functions keep.
$store = new StoredOptions;

describe('Option static facade', function () use ($notices, $store): void {
    beforeEach(function () use ($notices, $store): void {
        Brain\Monkey\setUp();

        $store->clear();

        Functions\when('get_option')->alias(fn (string $key, mixed $default = false) => $store->has($key) ? $store->options[$key] : $default);
        Functions\when('add_option')->alias(function (string $key, mixed $value) use ($store): bool {
            $store->put($key, $value);

            return true;
        });
        Functions\when('update_option')->alias(function (string $key, mixed $value) use ($store): bool {
            $store->put($key, $value);

            return true;
        });
        Functions\when('delete_option')->alias(function (string $key) use ($store): bool {
            $store->forget($key);

            return true;
        });

        $property = new ReflectionProperty(Option::class, 'service');
        $property->setValue(null, null);

        // Once the framework facade stand-in is loaded (it cannot be unloaded),
        // every later service creation raises the notice: collect it here.
        $notices->clear();
        set_error_handler(function (int $level, string $message) use ($notices): bool {
            $notices->record($message);

            return true;
        }, E_USER_NOTICE);
    });

    afterEach(function (): void {
        restore_error_handler();
        $property = new ReflectionProperty(Option::class, 'service');
        $property->setValue(null, null);
        Brain\Monkey\tearDown();
    });

    it('returns the default when the option is missing', function (): void {
        expect(Option::get('facade_missing', 'fallback'))->toBe('fallback');
    });

    it('stores a new option with set and reads it back', function () use ($store): void {
        expect(Option::set('facade_new', 'value'))->toBeTrue()
            ->and($store->options['facade_new'])->toBe('value')
            ->and(Option::get('facade_new'))->toBe('value')
            ->and(Option::exists('facade_new'))->toBeTrue();
    });

    it('overwrites an existing option with set', function (): void {
        Option::set('facade_upsert', 'one');
        Option::set('facade_upsert', 'two');

        expect(Option::get('facade_upsert'))->toBe('two');
    });

    it('updates an option', function () use ($store): void {
        expect(Option::update('facade_update', ['a' => 1]))->toBeTrue()
            ->and($store->options['facade_update'])->toBe(['a' => 1]);
    });

    it('deletes an option', function (): void {
        Option::set('facade_delete', 'x');

        expect(Option::delete('facade_delete'))->toBeTrue()
            ->and(Option::exists('facade_delete'))->toBeFalse();
    });

    it('reuses the same service between calls', function (): void {
        Option::get('facade_once');
        $property = new ReflectionProperty(Option::class, 'service');
        $first = $property->getValue();
        Option::get('facade_twice');

        expect($property->getValue())->toBe($first);
    });

    it('rejects an empty key through the validator', function (): void {
        Option::get('');
    })->throws(InvalidOptionException::class);

    it('warns that the framework facade should be used when the framework is available', function () use ($notices): void {
        if (! class_exists(Pollora\Support\Facades\Option::class, false)) {
            require_once __DIR__.'/../Fixtures/FrameworkOptionFacade.php';
        }

        Option::get('facade_notice', 'fallback');

        expect($notices->messages)->toHaveCount(1)
            ->and($notices->messages[0])->toBe(
                'Using Pollora\Option\Option directly is not recommended when the Pollora framework is available. '
                .'Use the Pollora\Support\Facades\Option facade instead for DI container support.'
            );
    });

    it('raises the notice once, when the service is created, not on later calls', function () use ($notices): void {
        if (! class_exists(Pollora\Support\Facades\Option::class, false)) {
            require_once __DIR__.'/../Fixtures/FrameworkOptionFacade.php';
        }

        Option::get('facade_first');
        Option::get('facade_second');

        expect($notices->messages)->toHaveCount(1);
    });
});
