<?php

declare(strict_types=1);

namespace Pollora\Option\Application\Service;

use Pollora\Option\Domain\Contract\OptionRepositoryInterface;
use Pollora\Option\Domain\Model\Option;
use Pollora\Option\Domain\Service\OptionValidationService;

/**
 * Application service for managing WordPress options.
 *
 * Orchestrates option CRUD operations by combining the domain repository
 * with validation logic. This is the primary entry point for option management.
 *
 * @see OptionRepositoryInterface  The storage backend.
 * @see OptionValidationService     The key/value validator.
 */
final readonly class OptionService
{
    /**
     * @param  OptionRepositoryInterface  $repository  The storage backend for options.
     * @param  OptionValidationService  $validator  The key/value validation service.
     */
    public function __construct(
        private OptionRepositoryInterface $repository,
        private OptionValidationService $validator
    ) {}

    /**
     * Retrieve an option value with type safety.
     *
     * @param  string  $key  Option key.
     * @param  mixed  $default  Default value if option doesn't exist.
     * @return mixed Option value or default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->validator->validateKey($key);

        $option = $this->repository->get($key);

        return $option instanceof Option ? $option->value : $default;
    }

    /**
     * Create or update an option (smart upsert).
     *
     * Stores a new option if it doesn't exist, or updates it if it does.
     *
     * @param  string  $key  Option key.
     * @param  mixed  $value  Option value.
     * @return bool True on success, false on failure.
     */
    public function set(string $key, mixed $value): bool
    {
        $this->validator->validateKey($key);
        $this->validator->validateValue($value);

        $option = new Option($key, $value);

        if ($this->repository->exists($key)) {
            return $this->repository->update($option);
        }

        return $this->repository->store($option);
    }

    /**
     * Update an existing option.
     *
     * @param  string  $key  Option key.
     * @param  mixed  $value  Option value.
     * @return bool True on success, false on failure.
     */
    public function update(string $key, mixed $value): bool
    {
        $this->validator->validateKey($key);
        $this->validator->validateValue($value);

        return $this->repository->update(new Option($key, $value));
    }

    /**
     * Delete an option.
     *
     * @param  string  $key  Option key.
     * @return bool True on success, false on failure.
     */
    public function delete(string $key): bool
    {
        $this->validator->validateKey($key);

        return $this->repository->delete($key);
    }

    /**
     * Check if an option exists.
     *
     * @param  string  $key  Option key.
     * @return bool True if the option exists.
     */
    public function exists(string $key): bool
    {
        $this->validator->validateKey($key);

        return $this->repository->exists($key);
    }
}
