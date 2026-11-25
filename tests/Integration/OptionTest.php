<?php

declare(strict_types=1);

use Pollora\Option\Adapter\Out\WordPress\WordPressOptionRepository;
use Pollora\Option\Application\Service\OptionService;
use Pollora\Option\Domain\Exception\InvalidOptionException;
use Pollora\Option\Domain\Model\Option as OptionModel;
use Pollora\Option\Domain\Service\OptionValidationService;
use Pollora\Option\Option;

/*
 * Options against a real WordPress and its wp_options table: what the object cache, the "not found" cache and the
 * database do with the values, which get_option() and add_option() doubles cannot show.
 */

afterEach(function (): void {
    foreach (['pollora_wpint_a', 'pollora_wpint_b', 'pollora_wpint_c', 'pollora_wpint_flag', 'pollora_wpint_zero', str_repeat('k', 191)] as $key) {
        delete_option($key);
    }
});

/**
 * The autoload column of an option, read from the database, not from a cache.
 */
function storedAutoload(string $key): ?string
{
    global $wpdb;

    return $wpdb->get_var($wpdb->prepare("SELECT autoload FROM {$wpdb->options} WHERE option_name = %s", $key));
}

it('stores, reads, updates and deletes an option in the database', function (): void {
    expect(Option::exists('pollora_wpint_a'))->toBeFalse()
        ->and(Option::get('pollora_wpint_a', 'fallback'))->toBe('fallback')
        ->and(Option::set('pollora_wpint_a', ['name' => 'Ada', 'tags' => [1, 2]]))->toBeTrue()
        ->and(Option::exists('pollora_wpint_a'))->toBeTrue()
        ->and(Option::get('pollora_wpint_a'))->toBe(['name' => 'Ada', 'tags' => [1, 2]]);

    // Read it again without the object cache: it is what the database holds.
    wp_cache_flush();

    expect(Option::get('pollora_wpint_a'))->toBe(['name' => 'Ada', 'tags' => [1, 2]])
        ->and(Option::set('pollora_wpint_a', 'replaced'))->toBeTrue()
        ->and(Option::get('pollora_wpint_a'))->toBe('replaced')
        ->and(Option::delete('pollora_wpint_a'))->toBeTrue()
        ->and(Option::exists('pollora_wpint_a'))->toBeFalse()
        ->and(Option::get('pollora_wpint_a', 'gone'))->toBe('gone');
});

it('sees an option created after a read that found nothing', function (): void {
    // WordPress remembers that an option is missing ("notoptions"); creating it must forget that.
    expect(Option::exists('pollora_wpint_b'))->toBeFalse();

    add_option('pollora_wpint_b', 'created elsewhere');

    expect(Option::exists('pollora_wpint_b'))->toBeTrue()
        ->and(Option::get('pollora_wpint_b'))->toBe('created elsewhere');
});

it('autoloads a new option', function (): void {
    Option::set('pollora_wpint_c', 'v1');

    expect(storedAutoload('pollora_wpint_c'))->toBeIn(['yes', 'on', 'auto', 'auto-on']);
});

it('keeps the autoload of an option that was created without autoload, on set() and on update()', function (): void {
    add_option('pollora_wpint_c', 'v1', '', false);

    expect(storedAutoload('pollora_wpint_c'))->toBeIn(['no', 'off']);

    Option::set('pollora_wpint_c', 'v2');
    wp_cache_flush();

    expect(storedAutoload('pollora_wpint_c'))->toBeIn(['no', 'off'])
        ->and(Option::get('pollora_wpint_c'))->toBe('v2');

    Option::update('pollora_wpint_c', 'v3');
    wp_cache_flush();

    expect(storedAutoload('pollora_wpint_c'))->toBeIn(['no', 'off'])
        ->and(Option::get('pollora_wpint_c'))->toBe('v3');
});

it('stores an option that update() creates, as WordPress does', function (): void {
    expect(Option::update('pollora_wpint_a', 'by update'))->toBeTrue()
        ->and(Option::exists('pollora_wpint_a'))->toBeTrue()
        ->and(Option::get('pollora_wpint_a'))->toBe('by update');
});

it('reads back the falsy values it stored, with the type WordPress keeps', function (): void {
    // WordPress stores false as an empty string and reads it back as ''; 0 and '0' come back as strings.
    // What matters for the package: the option exists afterwards, and the read never returns the default for it.
    Option::set('pollora_wpint_flag', false);
    Option::set('pollora_wpint_zero', 0);

    $zero = Option::get('pollora_wpint_zero', 'default');

    expect(Option::exists('pollora_wpint_flag'))->toBeTrue()
        ->and(Option::get('pollora_wpint_flag', 'default'))->not->toBe('default')
        ->and(Option::exists('pollora_wpint_zero'))->toBeTrue()
        ->and(is_numeric($zero) ? (int) $zero : null)->toBe(0);
});

it('refuses a key the options table cannot hold before it reaches the database', function (): void {
    expect(fn () => Option::set(str_repeat('k', 192), 'x'))->toThrow(InvalidOptionException::class);

    // The longest key the column holds round-trips.
    $longest = str_repeat('k', 191);

    expect(Option::set($longest, 'long'))->toBeTrue();

    wp_cache_flush();

    expect(Option::get($longest))->toBe('long');
});

it('writes through the filters WordPress offers on an option', function (): void {
    $seen = [];
    add_filter('pre_update_option_pollora_wpint_a', function (mixed $value) use (&$seen): mixed {
        $seen[] = $value;

        return is_string($value) ? strtoupper($value) : $value;
    });

    $service = new OptionService(new WordPressOptionRepository, new OptionValidationService);
    $service->set('pollora_wpint_a', 'first');
    $service->set('pollora_wpint_a', 'second');

    remove_all_filters('pre_update_option_pollora_wpint_a');

    // The first write is an add_option() (no pre_update filter), the second an update_option().
    expect($seen)->toBe(['second'])
        ->and(get_option('pollora_wpint_a'))->toBe('SECOND');
});

it('stores an option with autoload off when the model says so', function (): void {
    $repository = new WordPressOptionRepository;

    expect($repository->store(new OptionModel('pollora_wpint_b', 'lazy', false)))->toBeTrue()
        ->and(storedAutoload('pollora_wpint_b'))->toBeIn(['no', 'off', 'auto-off']);
});
