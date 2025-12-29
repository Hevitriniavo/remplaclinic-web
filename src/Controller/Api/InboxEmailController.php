<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\IdListDto;
use App\Dto\Mailbox\ComposeEmailDto;
use App\Repository\MailLogRepository;
use App\Service\Mail\DeleteMailLogService;
use App\Service\Mail\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class InboxEmailController extends AbstractController
{
    public function __construct(
        private MailLogRepository $mailLogRepository,
    ) {}

    #[Route('/api/inbox-emails', name: 'api_inbox_email_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->mailLogRepository->findAllDataTables($params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/inbox-emails', name: 'api_inbox_emails_compose', methods: ['POST'])]
    public function composeEmailTest(
        MailService $mailService,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] ComposeEmailDto $composeEmailDto
    ): Response {

        $sent = $mailService->send($composeEmailDto->toMail());

        return $this->json($sent, $sent ? Response::HTTP_CREATED : Response::HTTP_CONFLICT, [], ['groups' => 'datatable']);
    }

    #[Route('/api/inbox-emails/{id}', name: 'api_inbox_emails_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id, DeleteMailLogService $deleteMailLog): Response
    {
        $deleted = $deleteMailLog->delete($id);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/api/inbox-emails/delete-multiple', name: 'api_inbox_emails_delete_multiple', methods: ['DELETE'])]
    public function removeMultiple(
        DeleteMailLogService $deleteMailLog,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] IdListDto $idList
    ): Response
    {

        $deleted = $deleteMailLog->deleteMultiple($idList->ids);

        return $this->json(
            '',
            !empty($deleted) ? Response::HTTP_OK : Response::HTTP_CONFLICT
        );
    }
}
