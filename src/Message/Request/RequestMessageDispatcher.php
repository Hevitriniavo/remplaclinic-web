<?php
namespace App\Message\Request;

use App\Entity\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class RequestMessageDispatcher
{
    const SEND_EMAIL_BLOC_COUNT = 10;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    )
    {}

    public function dispatchSendEmailMessage(string $eventName, Request $request, array $usersId)
    {
        $usersCount = count($usersId);

        if ($usersCount < 1) {
            return;
        }

        $bloc = [];
        $blocCount = 0;

        for($i = 0; $i < $usersCount; $i++) {
            $bloc[] = $usersId[$i];
            $blocCount++;

            if ($blocCount === self::SEND_EMAIL_BLOC_COUNT) {
                
                $this->messageBus->dispatch(new RequestMessage(
                    $eventName,
                    $request->getId(),
                    $request->getRequestType(),
                    $bloc
                ));
                
                $bloc = [];
                $blocCount = 0;
            }
        }

        if ($blocCount > 0) {
            $this->messageBus->dispatch(new RequestMessage(
                $eventName,
                $request->getId(),
                $request->getRequestType(),
                $bloc
            ));
        }
    }
}