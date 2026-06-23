<?php

declare(strict_types=1);

use Pollora\Option\Domain\Exception\InvalidOptionException;

describe('InvalidOptionException', function (): void {
    it('creates exception with custom message', function (): void {
        $exception = new InvalidOptionException('Custom error message');

        expect($exception->getMessage())->toBe('Custom error message');
    });

    it('handles empty message', function (): void {
        $exception = new InvalidOptionException('');

        expect($exception->getMessage())->toBe('');
    });
});
