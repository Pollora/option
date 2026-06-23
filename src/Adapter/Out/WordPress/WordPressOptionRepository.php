<?php

declare(strict_types=1);

namespace Pollora\Option\Adapter\Out\WordPress;

use Pollora\Option\Domain\Contract\OptionRepositoryInterface;
use Pollora\Option\Domain\Model\Option;

/**
 * WordPress adapter for option storage via `get_option()` / `update_option()`.
 *
 * Translates the domain {@see OptionRepositoryInterface} contract into
 * WordPress native option functions.
 */
final class WordPressOptionRepository implements OptionRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?Option
    {
        $value = get_option($key, null);

        if ($value === null) {
            return null;
        }

        return new Option($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function store(Option $option): bool
    {
        return add_option($option->key, $option->value, '', $option->autoload);
    }

    /**
     * {@inheritDoc}
     */
    public function update(Option $option): bool
    {
        return update_option($option->key, $option->value, $option->autoload);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): bool
    {
        return delete_option($key);
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $key): bool
    {
        return get_option($key, null) !== null;
    }
}
