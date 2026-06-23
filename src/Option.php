<?php

declare(strict_types=1);

namespace Pollora\Option;

use Pollora\Option\Adapter\Out\WordPress\WordPressOptionRepository;
use Pollora\Option\Application\Service\OptionService;
use Pollora\Option\Domain\Service\OptionValidationService;

/**
 * Convenience static facade for WordPress option management (standalone usage).
 *
 * Provides a clean entry point without requiring a service container.
 * When the Pollora framework is available, prefer the Laravel facade
 * `Pollora\Support\Facades\Option` which resolves from the DI container.
 *
 * Usage:
 *     Option::get('site_title', 'Default');
 *     Option::set('site_title', 'New Title');
 *     Option::exists('api_key');
 *     Option::delete('old_option');
 *
 * @see OptionService The underlying application service.
 */
class Option
{
    private static ?OptionService $service = null;

    /**
     * Retrieve an option value.
     *
     * @param  string  $key  Option key.
     * @param  mixed  $default  Default value if not found.
     * @return mixed Option value or default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getService()->get($key, $default);
    }

    /**
     * Create or update an option (smart upsert).
     *
     * @param  string  $key  Option key.
     * @param  mixed  $value  Option value.
     * @return bool True on success.
     */
    public static function set(string $key, mixed $value): bool
    {
        return self::getService()->set($key, $value);
    }

    /**
     * Update an existing option.
     *
     * @param  string  $key  Option key.
     * @param  mixed  $value  Option value.
     * @return bool True on success.
     */
    public static function update(string $key, mixed $value): bool
    {
        return self::getService()->update($key, $value);
    }

    /**
     * Delete an option.
     *
     * @param  string  $key  Option key.
     * @return bool True on success.
     */
    public static function delete(string $key): bool
    {
        return self::getService()->delete($key);
    }

    /**
     * Check if an option exists.
     *
     * @param  string  $key  Option key.
     * @return bool True if the option exists.
     */
    public static function exists(string $key): bool
    {
        return self::getService()->exists($key);
    }

    private static function getService(): OptionService
    {
        if (! self::$service instanceof OptionService) {
            if (class_exists(\Pollora\Support\Facades\Option::class)) {
                trigger_error(
                    'Using Pollora\Option\Option directly is not recommended when the Pollora framework is available. '
                    .'Use the Pollora\Support\Facades\Option facade instead for DI container support.',
                    E_USER_NOTICE
                );
            }

            self::$service = new OptionService(
                new WordPressOptionRepository,
                new OptionValidationService
            );
        }

        return self::$service;
    }
}
