<?php
namespace App\Message\Request;

use App\Service\Request\ArchiverService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class RequestArchivageMessageHandler
{
    public function __construct(
        private readonly ArchiverService $archiverService,
    ) {}

    public function __invoke(RequestArchivageMessage $message)
    {
        $this->archiverService->execute();
    }
}