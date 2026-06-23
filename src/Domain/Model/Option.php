<?php

declare(strict_types=1);

namespace Pollora\Option\Domain\Model;

/**
 * Immutable value object representing a WordPress option.
 *
 * Encapsulates the option key, value, and autoload flag.
 * Use `withValue()` and `withAutoload()` to derive new instances
 * with changed properties — the original is never mutated.
 *
 * @psalm-immutable
 */
final readonly class Option
{
    /**
     * @param  string  $key  The option key (unique identifier in wp_options).
     * @param  mixed  $value  The option value (any serializable type).
     * @param  bool  $autoload  Whether WordPress should autoload this option.
     */
    public function __construct(
        public string $key,
        public mixed $value,
        public bool $autoload = true
    ) {}

    /**
     * Create a new Option instance with a different value.
     *
     * @param  mixed  $value  The new value.
     * @return self A new instance with the updated value.
     */
    public function withValue(mixed $value): self
    {
        return new self($this->key, $value, $this->autoload);
    }

    /**
     * Create a new Option instance with a different autoload setting.
     *
     * @param  bool  $autoload  The new autoload flag.
     * @return self A new instance with the updated autoload.
     */
    public function withAutoload(bool $autoload): self
    {
        return new self($this->key, $this->value, $autoload);
    }
}
