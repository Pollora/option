<?php

declare(strict_types=1);

/*
 * ARCH-07: the harness preset (WordPress classes and functions, the tests), plus what is specific to this package.
 */
$configuration = require __DIR__.'/vendor/pollora/harness/presets/composer-dependency-analyser.php';

return $configuration
    // The framework's facade: used only when Pollora is present, absent when the package runs alone.
    ->ignoreUnknownClasses(['Pollora\Support\Facades\Option']);
