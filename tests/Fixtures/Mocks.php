<?php

declare(strict_types=1);

namespace Pollora\Option\Tests\Fixtures;

use LogicException;
use Mockery;
use Mockery\MockInterface;

/**
 * Mockery mocks that PHPStan knows to be both the mock and the mocked type.
 */
final class Mocks
{
    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T&MockInterface
     */
    public static function of(string $class): MockInterface
    {
        $mock = Mockery::mock($class);

        if (! $mock instanceof $class) {
            throw new LogicException(sprintf('Mockery did not return an instance of %s.', $class));
        }

        return $mock;
    }
}
