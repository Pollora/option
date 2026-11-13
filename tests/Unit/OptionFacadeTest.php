<?php

declare(strict_types=1);

use Brain\Monkey\Functions;
use Pollora\Option\Domain\Exception\InvalidOptionException;
use Pollora\Option\Option;

describe('Option static facade', function (): void {
    beforeEach(function (): void {
        Brain\Monkey\setUp();

        $this->store = new ArrayObject;
        $store = $this->store;

        Functions\when('get_option')->alias(fn ($key, $default = false) => $store->offsetExists($key) ? $store[$key] : $default);
        Functions\when('add_option')->alias(function ($key, $value) use ($store): bool {
            $store[$key] = $value;

            return true;
        });
        Functions\when('update_option')->alias(function ($key, $value) use ($store): bool {
            $store[$key] = $value;

            return true;
        });
        Functions\when('delete_option')->alias(function ($key) use ($store): bool {
            $store->offsetUnset($key);

            return true;
        });

        $property = new ReflectionProperty(Option::class, 'service');
        $property->setValue(null, null);
    });

    afterEach(function (): void {
        $property = new ReflectionProperty(Option::class, 'service');
        $property->setValue(null, null);
        Brain\Monkey\tearDown();
    });

    it('returns the default when the option is missing', function (): void {
        expect(Option::get('facade_missing', 'fallback'))->toBe('fallback');
    });

    it('stores a new option with set and reads it back', function (): void {
        expect(Option::set('facade_new', 'value'))->toBeTrue()
            ->and($this->store['facade_new'])->toBe('value')
            ->and(Option::get('facade_new'))->toBe('value')
            ->and(Option::exists('facade_new'))->toBeTrue();
    });

    it('overwrites an existing option with set', function (): void {
        Option::set('facade_upsert', 'one');
        Option::set('facade_upsert', 'two');

        expect(Option::get('facade_upsert'))->toBe('two');
    });

    it('updates an option', function (): void {
        expect(Option::update('facade_update', ['a' => 1]))->toBeTrue()
            ->and($this->store['facade_update'])->toBe(['a' => 1]);
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
});
