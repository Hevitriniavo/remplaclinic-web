<?php
namespace App\Controller\Api;

use App\Message\MessengerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class MessengerContoller extends AbstractController
{
    public function __construct(
        private readonly MessengerService $messengerService,
    )
    {
    }

    #[Route('/api/messengers', name: 'api_admin_messengers_list')]
    public function list(): JsonResponse
    {
        $messages = $this->messengerService->getAllMessages();

        return $this->json($messages);
    }
}
