<?php
namespace App\Message\Ping;

use App\Service\Taches\AppConfigurationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class PingMessageHandler
{
    public function __construct(
        private readonly AppConfigurationService $appConfigurationService,
    )
    {
    }
    
    public function __invoke(PingMessage $message)
    {
        $token = $message->getTarget();

        $this->appConfigurationService->setValue('APP_MESSANGER_WORKER_PONG', $token, true);
    }
}