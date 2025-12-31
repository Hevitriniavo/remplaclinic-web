<?php

namespace App\Message\Console;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class RunCommandMessage implements \Stringable
{
    public function __construct(
        public readonly string $script,
        public readonly array $options = [],
        public readonly bool $throwOnFailure = true,
        public readonly bool $catchExceptions = false,
    ) {
    }

    public function __toString(): string
    {
        $cmdOptions = [];
        foreach($this->options as $optionName => $optionValue) {
            $cmdOptions[] = sprintf('--%s=%s', $optionName, $optionValue);
        }

        return $this->script . ' ' . implode(' ', $cmdOptions);
    }
}
