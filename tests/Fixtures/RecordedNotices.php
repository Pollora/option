<?php

declare(strict_types=1);

namespace Pollora\Option\Tests\Fixtures;

/**
 * The user notices a test collects while it runs.
 */
final class RecordedNotices
{
    /** @var list<string> */
    public array $messages = [];

    public function record(string $message): void
    {
        $this->messages[] = $message;
    }

    public function clear(): void
    {
        $this->messages = [];
    }
}
