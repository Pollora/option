<?php

declare(strict_types=1);

namespace Pollora\Option\Tests\Fixtures;

/**
 * The options a test keeps in memory, in place of the WordPress options table.
 */
final class StoredOptions
{
    /** @var array<string, mixed> */
    public array $options = [];

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->options);
    }

    public function put(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    public function forget(string $key): void
    {
        unset($this->options[$key]);
    }

    public function clear(): void
    {
        $this->options = [];
    }
}
