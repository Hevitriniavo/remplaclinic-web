<?php
namespace App\Service\Request;

use App\Entity\EmailEvents;
use App\Entity\Request;

final class OperationExecutor
{
    public function __construct(
        private readonly ValiderService $validerService,
        private readonly RenvoyerService $renvoyerService,
        private readonly RelancerService $relancerService,
        private readonly CloturerService $cloturerService,
    ) {}

    public function handle(int $requestId, string $eventName): Request
    {
        $services = [
            EmailEvents::REQUEST_VALIDATION => $this->validerService,
            EmailEvents::REQUEST_RENVOIE => $this->renvoyerService,
            EmailEvents::REQUEST_RELANCE => $this->relancerService,
            // EmailEvents::REQUEST_ARCHIVAGE => $this->validerService,
            EmailEvents::REQUEST_CLOTURATION => $this->cloturerService,
        ];
        $serviceVisitor = $services[$eventName];

        return $serviceVisitor->execute($requestId);
    }
}