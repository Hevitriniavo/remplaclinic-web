<?php
namespace App\Message\Request;

use App\Entity\Request;
use App\Entity\RequestType;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class RequestMessageDispatcherMessage
{
    const SEND_EMAIL_BLOC_COUNT = 10;

    public function __construct(
        private readonly string $eventName,
        private readonly int $requetId,
        private readonly RequestType $requestType,
        private readonly array $usersId,
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
        return $this->usersId;
    }

    public function getBlocCount(): int
    {
        return self::SEND_EMAIL_BLOC_COUNT;
    }
}