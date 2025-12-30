<?php

namespace App\Controller\Admin;

use App\Entity\EmailEvents;
use App\Entity\RequestType;
use App\Repository\RequestResponseRepository;
use App\Service\Mail\RequestMailBuilder;
use App\Service\Request\CloturerService;
use App\Service\Request\RelancerService;
use App\Service\Request\RenvoyerService;
use App\Service\Request\ValiderService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RequestTafController extends AbstractController
{
    public function __construct(
        private readonly ValiderService $validerService,
        private readonly RenvoyerService $renvoyerService,
        private readonly RelancerService $relancerService,
        private readonly CloturerService $cloturerService,
    )
    {}

    #[Route(
        '/admin/requests-{requestType}/{requestId}/{eventName}',
        name: 'app_admin_request_taf_execute',
        requirements: [
            'requestType' => 'replacement|installation',
            'requestId' => '\d+',
            'eventName' => EmailEvents::REQUEST_VALIDATION . '|' . EmailEvents::REQUEST_RENVOIE . '|' . EmailEvents::REQUEST_RELANCE . '|' . EmailEvents::REQUEST_CLOTURATION
        ]
    )]
    public function tafExecution(string $requestType, int $requestId, string $eventName): Response
    {
        $requestType = RequestType::from($requestType);
        return $this->handleExecuteAction($requestId, $eventName, $requestType === RequestType::REPLACEMENT ? 'app_admin_request_replacement' : 'app_admin_request_installation');
    }

    #[Route(
        '/admin/requests/{requestId}/{eventName}/preview-email',
        name: 'app_admin_request_taf_preview',
        requirements: [
            'requestId' => '\d+',
            'eventName' => EmailEvents::REQUEST_VALIDATION . '|' . EmailEvents::REQUEST_RENVOIE . '|' . EmailEvents::REQUEST_RELANCE . '|' . EmailEvents::REQUEST_ARCHIVAGE  . '|' . EmailEvents::REQUEST_CLOTURATION
        ]
    )]
    public function previewEmail(
        int $requestId,
        string $eventName,
        Request $request,
        RequestMailBuilder $mailBuilder,
        RequestResponseRepository $requestResponseRepository,
    ): Response
    {
        $asJson = $request->query->get('as_json', false);

        $userId = $request->query->get('user_id');
        $requiredUser = $eventName === EmailEvents::REQUEST_VALIDATION || $eventName === EmailEvents::REQUEST_RENVOIE;

        if ($requiredUser && empty($userId)){
            throw new Exception('User id must be not empty to preview the request email.');
        }

        $additionalEmailData = [];

        if ($eventName === EmailEvents::REQUEST_RELANCE) {
            $additionalEmailData['users'] = $requestResponseRepository->findAllUserWhoAccept($requestId);
        }

        $mailLog = $mailBuilder->buildFromUserAndRequet($eventName, $requestId, $userId, $additionalEmailData);

        if ($asJson) {
            return $this->json($mailLog, 200, ['groups' => ['datatable', 'full']]);
        }

        return new Response($mailLog->getBody());
    }


    private function handleExecuteAction(int $requestId, string $eventName, string $redirectToRoute): RedirectResponse
    {
        $services = [
            EmailEvents::REQUEST_VALIDATION => $this->validerService,
            EmailEvents::REQUEST_RENVOIE => $this->renvoyerService,
            EmailEvents::REQUEST_RELANCE => $this->relancerService,
            // EmailEvents::REQUEST_ARCHIVAGE => $this->validerService,
            EmailEvents::REQUEST_CLOTURATION => $this->cloturerService,
        ];
        $serviceVisitor = $services[$eventName];

        $request = $serviceVisitor->execute($requestId);

        $this->addFlash('request_operation', sprintf("L'operation %s sur la %s est en cours d'execution.", $eventName, $request->getRequestType() === RequestType::REPLACEMENT ? 'demande de remplacement' : "proposition d'installation"));

        return $this->redirectToRoute($redirectToRoute);
    }
}
