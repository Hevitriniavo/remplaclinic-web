<?php
namespace App\Message\Request;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class RequestMessage
{
    public function __construct(
        private readonly string $eventName,
        private readonly int $requetId,
        private readonly array $users,
    ) {}

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getRequestId(): int
    {
        return $this->requetId;
    }

    public function getUsers(): array
    {
        return $this->users;
    }
}