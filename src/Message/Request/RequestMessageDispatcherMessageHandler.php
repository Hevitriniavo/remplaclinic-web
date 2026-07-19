<?php
namespace App\Message\Request;

use App\Entity\Request;
use App\Repository\RequestRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler()]
class RequestMessageDispatcherMessageHandler
{
    public function __construct(
        private readonly RequestRepository $requestRepository,
        private readonly MessageBusInterface $messageBus,
    ) {}

    public function __invoke(RequestMessageDispatcherMessage $message)
    {
        // verifier les remplacants
        $usersCount = count($message->getUsers());

        if ($usersCount < 1) {
            return;
        }

        // verifier la demande
        $request = $this->requestRepository->find($message->getRequestId());
        if (is_null($request)) {
            return;
        }

        // separer l'envoi d'email par bloc de 10
        $bloc = [];
        $blocCount = 0;

        for($i = 0; $i < $usersCount; $i++) {
            $bloc[] = $message->getUsers()[$i];
            $blocCount++;

            if ($blocCount === $message->getBlocCount()) {
                
                $this->messageBus->dispatch(new RequestMessage(
                    $message->getEventName(),
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
                $message->getEventName(),
                $request->getId(),
                $request->getRequestType(),
                $bloc
            ));
        }
    }
}