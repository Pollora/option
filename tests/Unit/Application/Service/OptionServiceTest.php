<?php

declare(strict_types=1);

use Mockery\MockInterface;
use Pollora\Option\Application\Service\OptionService;
use Pollora\Option\Domain\Contract\OptionRepositoryInterface;
use Pollora\Option\Domain\Exception\InvalidOptionException;
use Pollora\Option\Domain\Model\Option;
use Pollora\Option\Domain\Service\OptionValidationService;
use Pollora\Option\Tests\Fixtures\Mocks;

// What the tests share, typed: the closures below receive it instead of reading `$this`.
$context = new class
{
    public OptionRepositoryInterface&MockInterface $repository;

    public OptionValidationService $validator;

    public OptionService $service;
};

describe('OptionService', function () use ($context): void {
    beforeEach(function () use ($context): void {
        $context->repository = Mocks::of(OptionRepositoryInterface::class);
        $context->validator = new OptionValidationService;
        $context->service = new OptionService($context->repository, $context->validator);
    });

    it('returns option value when exists', function () use ($context): void {
        $context->repository->shouldReceive('get')->with('test_key')->once()->andReturn(new Option('test_key', 'test_value'));

        expect($context->service->get('test_key'))->toBe('test_value');
    });

    it('returns default when option not exists', function () use ($context): void {
        $context->repository->shouldReceive('get')->with('test_key')->once()->andReturn(null);

        expect($context->service->get('test_key', 'default_value'))->toBe('default_value');
    });

    it('returns null as default when no default provided', function () use ($context): void {
        $context->repository->shouldReceive('get')->with('test_key')->once()->andReturn(null);

        expect($context->service->get('test_key'))->toBeNull();
    });

    it('stores new option when not exists', function () use ($context): void {
        $context->repository->shouldReceive('exists')->with('test_key')->once()->andReturn(false);
        $context->repository->shouldReceive('store')->once()->andReturn(true);

        expect($context->service->set('test_key', 'test_value'))->toBeTrue();
    });

    it('updates existing option when exists', function () use ($context): void {
        $context->repository->shouldReceive('exists')->with('test_key')->once()->andReturn(true);
        $context->repository->shouldReceive('update')->once()->andReturn(true);

        expect($context->service->set('test_key', 'test_value'))->toBeTrue();
    });

    it('set and update make no autoload choice, so the repository can keep the stored one', function (string $method) use ($context): void {
        $context->repository->shouldReceive('exists')->andReturn(true);
        $context->repository->shouldReceive('update')->once()
            ->withArgs(fn (Option $option): bool => $option->autoload === null)
            ->andReturn(true);

        expect($context->service->{$method}('test_key', 'v'))->toBeTrue();
    })->with(['set', 'update']);

    it('update calls repository update', function () use ($context): void {
        $context->repository->shouldReceive('update')->once()->andReturn(true);

        expect($context->service->update('test_key', 'test_value'))->toBeTrue();
    });

    it('delete calls repository delete', function () use ($context): void {
        $context->repository->shouldReceive('delete')->with('test_key')->once()->andReturn(true);

        expect($context->service->delete('test_key'))->toBeTrue();
    });

    it('exists calls repository exists', function () use ($context): void {
        $context->repository->shouldReceive('exists')->with('test_key')->once()->andReturn(true);

        expect($context->service->exists('test_key'))->toBeTrue();
    });

    it('rejects an invalid key before touching the repository', function (string $method, array $args) use ($context): void {
        $context->repository->shouldNotReceive('get', 'exists', 'update', 'store', 'delete');

        expect(fn () => $context->service->{$method}(...$args))
            ->toThrow(InvalidOptionException::class, 'Option key cannot be empty');
    })->with([
        'get' => ['get', ['']],
        'set' => ['set', ['', 'v']],
        'update' => ['update', ['', 'v']],
        'delete' => ['delete', ['']],
        'exists' => ['exists', ['']],
    ]);

    it('rejects an invalid value before touching the repository', function (string $method) use ($context): void {
        $context->repository->shouldNotReceive('get', 'exists', 'update', 'store', 'delete');

        expect(fn () => $context->service->{$method}('key', static fn (): int => 1))
            ->toThrow(InvalidOptionException::class, 'Option value must be serializable');
    })->with(['set', 'update']);

    it('accepts a single character key', function () use ($context): void {
        $context->repository->shouldReceive('exists')->with('a')->once()->andReturn(true);

        expect($context->service->exists('a'))->toBeTrue();
    });
});
