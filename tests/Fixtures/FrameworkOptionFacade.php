<?php

declare(strict_types=1);

namespace Pollora\Support\Facades;

/**
 * Stand-in for the framework facade, loaded on demand by the tests that
 * need `class_exists()` to report the framework as present.
 */
final class Option {}
