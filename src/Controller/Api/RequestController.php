<?php

namespace App\Controller\Api;

use App\Controller\Trait\RequestCheckAccessTrait;
use App\Dto\DataTable\DataTableParams;
use App\Dto\Request\EditRequestDto;
use App\Dto\Request\NewInstallationDto;
use App\Dto\Request\NewReplacementDto;
use App\Dto\IdListDto;
use App\Entity\EmailEvents;
use App\Entity\Request as EntityRequest;
use App\Entity\RequestReason;
use App\Entity\RequestType;
use App\Repository\RequestRepository;
use App\Service\Request\OperationExecutor;
use App\Service\Request\RequestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class RequestController extends AbstractController
{
    const JSON_LIST_GROUPS = ['request:datatable', 'user:simple', 'speciality:simple'];

    use RequestCheckAccessTrait;

    public function __construct(
        private readonly RequestRepository $requestRepository,
        private readonly Security $security,
        private readonly OperationExecutor $operationExecutor,
    ) {}

    #[Route('/api/request-replacements', name: 'api_request_replacement_get', methods: ['GET'])]
    public function replacement(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());

        $groups = self::JSON_LIST_GROUPS;
        array_push($groups, 'user:establishment');

        return $this->json($this->requestRepository->findAllDataTables(RequestType::REPLACEMENT, $params), 200, [], ['groups' => $groups]);
    }

    #[Route('/api/request-installations', name: 'api_request_installation_get', methods: ['GET'])]
    public function installation(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());

        $groups = self::JSON_LIST_GROUPS;
        array_push($groups, 'user:establishment');
        
        return $this->json($this->requestRepository->findAllDataTables(RequestType::INSTALLATION, $params), 200, [], ['groups' =>  $groups]);
    }

    #[Route('/api/request-replacements', name: 'api_request_replacement_new', methods: ['POST'])]
    public function createReplacement(
        RequestService $requestService,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] NewReplacementDto $replacementDto
    ): Response {
        if (!$this->canCreateOrUpdateUser($this->security, $replacementDto->applicant)) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $request = $requestService->createReplacement($replacementDto);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->json($request, Response::HTTP_CREATED, [], ['groups' =>  self::JSON_LIST_GROUPS]);
        }

        // redirect to my request
        return $this->json([
            'id' => $request->getId(),
            '_redirect' => $this->generateUrl('app_user_requets_replacement'),
            '_edit' => $this->generateUrl('api_request_replacement_update', [ 'id' => $request->getId() ])
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/request-installations', name: 'api_request_installation_new', methods: ['POST'])]
    public function createInstallation(
        RequestService $requestService,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] NewInstallationDto $installationDto
    ): Response {
        if (!$this->canCreateOrUpdateUser($this->security, $installationDto->applicant)) {
            
            // @TODO: check user abonnement here

            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $request = $requestService->createInstallation($installationDto);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->json($request, Response::HTTP_CREATED, [], ['groups' =>  self::JSON_LIST_GROUPS]);
        }

        // redirect to my request
        return $this->json([
            'id' => $request->getId(),
            '_redirect' => $this->generateUrl('app_user_requets_installation'),
            '_edit' => $this->generateUrl('api_request_installation_update', [ 'id' => $request->getId() ])
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/requests/{id}', name: 'api_request_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $request = $this->requestRepository->find($id);

        $groups = self::JSON_LIST_GROUPS;
        $groups[] = 'full';
        $groups[] = 'user:establishment';

        return $this->json(
            $request,
            is_null($request) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => $groups]
        );
    }

    #[Route('/api/requests/{id}/more-detail', name: 'api_request_detail_more', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getMoreDetail(int $id): Response
    {
        /**
         * @var EntityRequest
         */
        $request = $this->requestRepository->find($id);

        return $this->json([
            'id' => $request->getId(),
            'title' => $request->getTitle(),
            'specialite' => $request->getSpeciality()->getName(),
            'sous_specialite' => $request->getSubSpecialitiesAsText(),
            'region' => $request->getRegion()->getName(),
            'ville' => $request->getApplicant()->getApplicantLocality(),
            'remuneration' => $request->getRemunerationOrRetrocession(),
            'logement' => $request->getAccomodationIncludedAsText(),
            'transport' => $request->getTransportCostRefundedAsText(),
            'commentaire' => $request->getComment(),
            'raison' => implode('<br>', $request->getReasons()->map(fn(RequestReason $r) => $r->getReason() === 'Autre' ? $r->getReasonValue() : $r->getReason())->toArray())
        ]);
    }

    #[Route('/api/request-replacements/{id}', name: 'api_request_replacement_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        RequestService $requestService,
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] EditRequestDto $requestDto
    ): Response {
        $request = $this->requestRepository->find($id);

        if (is_null($request)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        if (!$this->canCreateOrUpdateUser($this->security, $request->getApplicant()->getId())) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $requestService->updateReplacement($request, $requestDto);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->json($request, Response::HTTP_OK, [], ['groups' => 'datatable']);
        }

        // redirect to my request
        return $this->json([
            'id' => $request->getId(),
            '_redirect' => $this->generateUrl('app_user_requets_replacement'),
            '_edit' => $this->generateUrl('api_request_replacement_update', [ 'id' => $request->getId() ])
        ], Response::HTTP_OK);
    }

    #[Route('/api/request-installations/{id}', name: 'api_request_installation_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function updateRequestInstallation(
        RequestService $requestService,
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] EditRequestDto $requestDto
    ): Response {
        $request = $this->requestRepository->find($id);

        if (is_null($request)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        if (!$this->canCreateOrUpdateUser($this->security, $request->getApplicant()->getId())) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $requestService->updateInstallation($request, $requestDto);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->json($request, Response::HTTP_OK, [], ['groups' => 'datatable']);
        }

        // redirect to my request
        return $this->json([
            'id' => $request->getId(),
            '_redirect' => $this->generateUrl('app_user_requets_installation'),
            '_edit' => $this->generateUrl('api_request_installation_update', [ 'id' => $request->getId() ])
        ], Response::HTTP_OK);
    }

    #[Route('/api/requests/{id}', name: 'api_request_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id, RequestService $requestService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $deleted = $requestService->deleteRequest($id);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/api/requests/delete-multiple', name: 'api_request_delete_multiple', methods: ['DELETE'])]
    public function removeMultiple(
        RequestService $requestService,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] IdListDto $requests
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $deleted = $requestService->deleteMultipleRequest($requests->ids);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    #[Route(
        '/api/requests-{requestType}/{eventName}/multiple',
        name: 'api_request_taf_execute',
        requirements: [
            'requestType' => 'replacement|installation',
            'eventName' => EmailEvents::REQUEST_VALIDATION . '|' . EmailEvents::REQUEST_RENVOIE . '|' . EmailEvents::REQUEST_RELANCE . '|' . EmailEvents::REQUEST_CLOTURATION
        ],
        methods: ['PUT']
    )]
    public function tafExecutionMultiple(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] IdListDto $requests,
        string $requestType,
        string $eventName
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $requestType = RequestType::from($requestType);

        foreach($requests->ids as $requestId) {
            $this->operationExecutor->handle($requestId, $eventName);
        }

        return $this->json([
            'ok' => true,
            'message' => sprintf("L'operation %s sur les %s est en cours d'execution.", $eventName, $requestType === RequestType::REPLACEMENT ? 'demandes de remplacement' : "propositions d'installation")
        ]);
    }

    #[Route(
        '/api/requests-{requestType}/{requestId}/{eventName}',
        name: 'api_request_operation_execute',
        requirements: [
            'requestType' => 'replacement|installation',
            'requestId' => '\d+',
            'eventName' => EmailEvents::REQUEST_CLOTURATION
        ],
        methods: ['PUT']
    )]
    public function operationExecution(
        string $requestType,
        int $requestId,
        string $eventName
    ): Response
    {
        /**
         * @var EntityRequest
         */
        $request = $this->requestRepository->find($requestId);
        if (is_null($request)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        if (!$this->canCreateOrUpdateUser($this->security, $request->getApplicant()->getId())) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $requestType = RequestType::from($requestType);

        $this->operationExecutor->handle($requestId, $eventName);

        return $this->json([
            'ok' => true,
            'message' => sprintf("L'operation %s sur la %s est en cours d'execution.", $eventName, $requestType === RequestType::REPLACEMENT ? 'demande de remplacement' : "proposition d'installation")
        ]);
    }
}
