<?php

declare(strict_types=1);

use Pollora\Option\Adapter\Out\WordPress\WordPressOptionRepository;
use Pollora\Option\Domain\Model\Option;

// What the tests share, typed: the closures below receive it instead of reading `$this`.
$context = new class
{
    public WordPressOptionRepository $repository;
};

describe('WordPressOptionRepository', function () use ($context): void {
    beforeEach(function () use ($context): void {
        $context->repository = new WordPressOptionRepository;
        Brain\Monkey\setUp();
    });

    afterEach(function (): void {
        Brain\Monkey\tearDown();
    });

    it('returns option when exists', function () use ($context): void {
        Brain\Monkey\Functions\when('get_option')->alias(fn ($key, $default) => $key === 'test_key' ? 'test_value' : $default);

        $result = $context->repository->get('test_key');

        expect($result)->toBeInstanceOf(Option::class)
            ->and($result?->key)->toBe('test_key')
            ->and($result?->value)->toBe('test_value');
    });

    it('returns null when not exists', function () use ($context): void {
        Brain\Monkey\Functions\when('get_option')->alias(fn ($key, $default) => null);

        expect($context->repository->get('non_existent_key'))->toBeNull();
    });

    it('store returns true', function () use ($context): void {
        Brain\Monkey\Functions\when('add_option')->justReturn(true);

        expect($context->repository->store(new Option('test_key', 'test_value', true)))->toBeTrue();
    });

    it('store passes key, value, an empty deprecated argument and the autoload flag to add_option', function () use ($context): void {
        $calls = [];
        Brain\Monkey\Functions\when('add_option')->alias(function (...$args) use (&$calls): bool {
            $calls[] = $args;

            return true;
        });

        $context->repository->store(new Option('test_key', ['v'], false));

        expect($calls)->toBe([['test_key', ['v'], '', false]]);
    });

    it('store autoloads a new option when no autoload is given', function () use ($context): void {
        $calls = [];
        Brain\Monkey\Functions\when('add_option')->alias(function (...$args) use (&$calls): bool {
            $calls[] = $args;

            return true;
        });

        $context->repository->store(new Option('test_key', 'v'));

        expect($calls)->toBe([['test_key', 'v', '', true]]);
    });

    it('update passes a null autoload so that WordPress keeps the stored one', function () use ($context): void {
        $calls = [];
        Brain\Monkey\Functions\when('update_option')->alias(function (...$args) use (&$calls): bool {
            $calls[] = $args;

            return true;
        });

        $context->repository->update(new Option('test_key', 'v'));

        expect($calls)->toBe([['test_key', 'v', null]]);
    });

    it('update passes an explicit autoload through, true or false', function () use ($context): void {
        $calls = [];
        Brain\Monkey\Functions\when('update_option')->alias(function (...$args) use (&$calls): bool {
            $calls[] = $args;

            return true;
        });

        $context->repository->update(new Option('a', 'v', false));
        $context->repository->update(new Option('b', 'v', true));

        expect($calls)->toBe([['a', 'v', false], ['b', 'v', true]]);
    });

    it('update returns true', function () use ($context): void {
        Brain\Monkey\Functions\when('update_option')->justReturn(true);

        expect($context->repository->update(new Option('test_key', 'updated_value', false)))->toBeTrue();
    });

    it('delete returns true', function () use ($context): void {
        Brain\Monkey\Functions\when('delete_option')->justReturn(true);

        expect($context->repository->delete('test_key'))->toBeTrue();
    });

    it('exists returns true when option has value', function () use ($context): void {
        Brain\Monkey\Functions\when('get_option')->alias(fn ($key, $default) => $key === 'existing_key' ? 'some_value' : $default);

        expect($context->repository->exists('existing_key'))->toBeTrue();
    });

    it('exists returns false when option is null', function () use ($context): void {
        Brain\Monkey\Functions\when('get_option')->alias(fn ($key, $default) => null);

        expect($context->repository->exists('non_existent_key'))->toBeFalse();
    });
});
