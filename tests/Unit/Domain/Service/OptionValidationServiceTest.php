<?php

declare(strict_types=1);

use Pollora\Option\Domain\Exception\InvalidOptionException;
use Pollora\Option\Domain\Service\OptionValidationService;

// What the tests share, typed: the closures below receive it instead of reading `$this`.
$context = new class
{
    public OptionValidationService $service;
};

describe('OptionValidationService', function () use ($context): void {
    beforeEach(function () use ($context): void {
        $context->service = new OptionValidationService;
    });

    it('validates valid key', function () use ($context): void {
        $context->service->validateKey('valid_key');
    })->throwsNoExceptions();

    it('throws exception for empty key', function () use ($context): void {
        $context->service->validateKey('');
    })->throws(InvalidOptionException::class, 'Option key cannot be empty');

    it('throws exception for too long key', function () use ($context): void {
        $context->service->validateKey(str_repeat('a', 192));
    })->throws(InvalidOptionException::class, 'Option key cannot exceed 191 characters');

    it('accepts a single character key', function () use ($context): void {
        $context->service->validateKey('a');
    })->throwsNoExceptions();

    it('accepts maximum length key', function () use ($context): void {
        $context->service->validateKey(str_repeat('a', 191));
    })->throwsNoExceptions();

    it('throws exception for key with null bytes', function () use ($context): void {
        $context->service->validateKey("test\0key");
    })->throws(InvalidOptionException::class, 'Option key cannot contain null bytes');

    it('validates valid scalar values', function () use ($context): void {
        $context->service->validateValue('string');
        $context->service->validateValue(42);
        $context->service->validateValue(3.14);
        $context->service->validateValue(true);
        $context->service->validateValue(null);
    })->throwsNoExceptions();

    it('validates valid array value', function () use ($context): void {
        $context->service->validateValue(['key' => 'value']);
    })->throwsNoExceptions();

    it('validates serializable object', function () use ($context): void {
        $object = new stdClass;
        $object->property = 'value';
        $context->service->validateValue($object);
    })->throwsNoExceptions();

    it('throws exception for resource value', function () use ($context): void {
        $resource = fopen('php://memory', 'r') ?: throw new RuntimeException('php://memory cannot be opened');
        try {
            $context->service->validateValue($resource);
        } finally {
            fclose($resource);
        }
    })->throws(InvalidOptionException::class, 'Option value cannot be a resource');

    it('throws exception for non-serializable object', function () use ($context): void {
        $context->service->validateValue(fn (): string => 'test');
    })->throws(InvalidOptionException::class, 'Option value must be serializable');
});
