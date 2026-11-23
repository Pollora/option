<?php

declare(strict_types=1);

use Pollora\Option\Adapter\Out\WordPress\WordPressOptionRepository;
use Pollora\Option\Domain\Model\Option;

describe('WordPressOptionRepository', function (): void {
    beforeEach(function (): void {
        $this->repository = new WordPressOptionRepository;
        Brain\Monkey\setUp();
    });

    afterEach(function (): void {
        Brain\Monkey\tearDown();
    });

    it('returns option when exists', function (): void {
        Brain\Monkey\Functions\when('get_option')->alias(fn ($key, $default) => $key === 'test_key' ? 'test_value' : $default);

        $result = $this->repository->get('test_key');

        expect($result)->toBeInstanceOf(Option::class)
            ->and($result->key)->toBe('test_key')
            ->and($result->value)->toBe('test_value');
    });

    it('returns null when not exists', function (): void {
        Brain\Monkey\Functions\when('get_option')->alias(fn ($key, $default) => null);

        expect($this->repository->get('non_existent_key'))->toBeNull();
    });

    it('store returns true', function (): void {
        Brain\Monkey\Functions\when('add_option')->justReturn(true);

        expect($this->repository->store(new Option('test_key', 'test_value', true)))->toBeTrue();
    });

    it('store passes key, value, an empty deprecated argument and the autoload flag to add_option', function (): void {
        $calls = [];
        Brain\Monkey\Functions\when('add_option')->alias(function (...$args) use (&$calls): bool {
            $calls[] = $args;

            return true;
        });

        $this->repository->store(new Option('test_key', ['v'], false));

        expect($calls)->toBe([['test_key', ['v'], '', false]]);
    });

    it('store autoloads a new option when no autoload is given', function (): void {
        $calls = [];
        Brain\Monkey\Functions\when('add_option')->alias(function (...$args) use (&$calls): bool {
            $calls[] = $args;

            return true;
        });

        $this->repository->store(new Option('test_key', 'v'));

        expect($calls)->toBe([['test_key', 'v', '', true]]);
    });

    it('update passes a null autoload so that WordPress keeps the stored one', function (): void {
        $calls = [];
        Brain\Monkey\Functions\when('update_option')->alias(function (...$args) use (&$calls): bool {
            $calls[] = $args;

            return true;
        });

        $this->repository->update(new Option('test_key', 'v'));

        expect($calls)->toBe([['test_key', 'v', null]]);
    });

    it('update passes an explicit autoload through, true or false', function (): void {
        $calls = [];
        Brain\Monkey\Functions\when('update_option')->alias(function (...$args) use (&$calls): bool {
            $calls[] = $args;

            return true;
        });

        $this->repository->update(new Option('a', 'v', false));
        $this->repository->update(new Option('b', 'v', true));

        expect($calls)->toBe([['a', 'v', false], ['b', 'v', true]]);
    });

    it('update returns true', function (): void {
        Brain\Monkey\Functions\when('update_option')->justReturn(true);

        expect($this->repository->update(new Option('test_key', 'updated_value', false)))->toBeTrue();
    });

    it('delete returns true', function (): void {
        Brain\Monkey\Functions\when('delete_option')->justReturn(true);

        expect($this->repository->delete('test_key'))->toBeTrue();
    });

    it('exists returns true when option has value', function (): void {
        Brain\Monkey\Functions\when('get_option')->alias(fn ($key, $default) => $key === 'existing_key' ? 'some_value' : $default);

        expect($this->repository->exists('existing_key'))->toBeTrue();
    });

    it('exists returns false when option is null', function (): void {
        Brain\Monkey\Functions\when('get_option')->alias(fn ($key, $default) => null);

        expect($this->repository->exists('non_existent_key'))->toBeFalse();
    });
});
