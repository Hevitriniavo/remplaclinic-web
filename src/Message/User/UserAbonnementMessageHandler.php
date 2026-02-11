<?php
namespace App\Message\User;

use App\Service\User\UserAbonnementService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class UserAbonnementMessageHandler
{
    public function __construct(
        private readonly UserAbonnementService $abonnementService,
    ) {}

    public function __invoke(UserAbonnementMessage $message)
    {
        $this->abonnementService->checkAbonnementEndDate();
    }
}