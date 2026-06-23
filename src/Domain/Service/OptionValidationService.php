<?php

declare(strict_types=1);

namespace Pollora\Option\Domain\Service;

use Pollora\Option\Domain\Exception\InvalidOptionException;

/**
 * Domain service for validating option keys and values.
 *
 * Enforces WordPress constraints on option keys (length, null bytes)
 * and values (serializability, no resources).
 */
final class OptionValidationService
{
    /**
     * Maximum allowed key length (WordPress wp_options.option_name column).
     */
    private const int MAX_KEY_LENGTH = 191;

    /**
     * Minimum allowed key length.
     */
    private const int MIN_KEY_LENGTH = 1;

    /**
     * Validate an option key.
     *
     * @param  string  $key  The option key to validate.
     *
     * @throws InvalidOptionException If the key is empty, too long, or contains null bytes.
     */
    public function validateKey(string $key): void
    {
        if (strlen($key) < self::MIN_KEY_LENGTH) {
            throw new InvalidOptionException('Option key cannot be empty');
        }

        if (strlen($key) > self::MAX_KEY_LENGTH) {
            throw new InvalidOptionException('Option key cannot exceed '.self::MAX_KEY_LENGTH.' characters');
        }

        if (str_contains($key, "\0")) {
            throw new InvalidOptionException('Option key cannot contain null bytes');
        }
    }

    /**
     * Validate an option value.
     *
     * @param  mixed  $value  The option value to validate.
     *
     * @throws InvalidOptionException If the value is a resource or non-serializable object.
     */
    public function validateValue(mixed $value): void
    {
        if (is_resource($value)) {
            throw new InvalidOptionException('Option value cannot be a resource');
        }

        if (is_object($value) && ! $this->isSerializableObject($value)) {
            throw new InvalidOptionException('Option value must be serializable');
        }
    }

    /**
     * Check if an object is serializable.
     */
    private function isSerializableObject(object $object): bool
    {
        try {
            serialize($object);

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
