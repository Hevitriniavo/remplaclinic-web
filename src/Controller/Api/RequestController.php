<?php

namespace App\Controller\Api;

use App\Controller\Trait\RequestCheckAccessTrait;
use App\Dto\DataTable\DataTableParams;
use App\Dto\Request\EditRequestDto;
use App\Dto\Request\NewInstallationDto;
use App\Dto\Request\NewReplacementDto;
use App\Dto\IdListDto;
use App\Entity\RequestType;
use App\Repository\RequestRepository;
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
    ) {}

    #[Route('/api/request-replacements', name: 'api_request_replacement_get', methods: ['GET'])]
    public function replacement(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->requestRepository->findAllDataTables(RequestType::REPLACEMENT, $params), 200, [], ['groups' => self::JSON_LIST_GROUPS]);
    }

    #[Route('/api/request-installations', name: 'api_request_installation_get', methods: ['GET'])]
    public function installation(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->requestRepository->findAllDataTables(RequestType::INSTALLATION, $params), 200, [], ['groups' =>  self::JSON_LIST_GROUPS]);
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
            '_redirect' => $this->generateUrl('app_user_requets_installation')
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/requests/{id}', name: 'api_request_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $request = $this->requestRepository->find($id);

        $groups = self::JSON_LIST_GROUPS;
        $groups[] = 'full';

        return $this->json(
            $request,
            is_null($request) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => $groups]
        );
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

        $requestService->updateReplacement($request, $requestDto);

        return $this->json(
            $request,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
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

        $requestService->updateInstallation($request, $requestDto);

        return $this->json(
            $request,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/requests/{id}', name: 'api_request_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id, RequestService $requestService): Response
    {
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
        $deleted = $requestService->deleteMultipleRequest($requests->ids);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }
}
