<?php
namespace App\Message\Request;

use App\Entity\RequestType;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class RequestMessage
{
    public function __construct(
        private readonly string $eventName,
        private readonly int $requetId,
        private readonly ?RequestType $requestType,
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
    
    public function getRequestType(): ?RequestType
    {
        return $this->requestType;
    }

    public function getUsers(): array
    {
        return $this->users;
    }
}