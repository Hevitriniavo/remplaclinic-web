<?php
namespace App\Controller\Api;

use App\Message\Ping\PingMessage;
use App\Service\Taches\AppConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class PingContoller extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly AppConfigurationService $appConfigurationService,
    )
    {
    }

    #[Route('/api/messengers/ping', name: 'api_admin_messenger_ping')]
    public function ping(): JsonResponse
    {
        $token = bin2hex(random_bytes(8));

        $this->messageBus->dispatch(new PingMessage($token));

        // Wait max 5 seconds
        $start = microtime(true);
        while (microtime(true) - $start < 5) {
            $pongValue = $this->appConfigurationService->getValue('APP_MESSANGER_WORKER_PONG', true, false, true);
            if ($pongValue === $token) {
                return $this->json([
                    'status' => 'UP',
                    'worker' => 'RESPONSIVE',
                ]);
            }

            usleep(500_000); // 500ms
        }

        return $this->json([
            'status' => 'DOWN',
            'worker' => 'NOT_RESPONDING',
        ], 503);
    }
}
