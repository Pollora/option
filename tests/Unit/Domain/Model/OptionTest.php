<?php

declare(strict_types=1);

use Pollora\Option\Domain\Model\Option;

describe('Option', function (): void {
    it('can create option with default autoload', function (): void {
        $option = new Option('test_key', 'test_value');

        expect($option->key)->toBe('test_key')
            ->and($option->value)->toBe('test_value')
            ->and($option->autoload)->toBeTrue();
    });

    it('can create option with custom autoload', function (): void {
        $option = new Option('test_key', 'test_value', false);

        expect($option->autoload)->toBeFalse();
    });

    it('can create option with different value types', function (): void {
        expect((new Option('k', 'test'))->value)->toBe('test')
            ->and((new Option('k', 42))->value)->toBe(42)
            ->and((new Option('k', ['foo' => 'bar']))->value)->toEqual(['foo' => 'bar'])
            ->and((new Option('k', true))->value)->toBeTrue()
            ->and((new Option('k', null))->value)->toBeNull();
    });

    it('withValue returns new instance', function (): void {
        $original = new Option('test_key', 'original_value');
        $updated = $original->withValue('new_value');

        expect($updated)->not->toBe($original)
            ->and($original->value)->toBe('original_value')
            ->and($updated->value)->toBe('new_value')
            ->and($updated->key)->toBe('test_key');
    });

    it('withAutoload returns new instance', function (): void {
        $original = new Option('test_key', 'test_value', true);
        $updated = $original->withAutoload(false);

        expect($updated)->not->toBe($original)
            ->and($original->autoload)->toBeTrue()
            ->and($updated->autoload)->toBeFalse();
    });

    it('supports chaining with methods', function (): void {
        $original = new Option('test_key', 'original_value', true);
        $updated = $original->withValue('new_value')->withAutoload(false);

        expect($updated->value)->toBe('new_value')
            ->and($updated->autoload)->toBeFalse()
            ->and($original->value)->toBe('original_value')
            ->and($original->autoload)->toBeTrue();
    });
});
