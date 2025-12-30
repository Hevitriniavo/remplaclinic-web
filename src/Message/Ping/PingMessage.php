<?php
namespace App\Message\Ping;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class PingMessage
{
    public function __construct(
        private readonly string $target,
    ) {}

    public function getTarget(): string
    {
        return $this->target;
    }
}