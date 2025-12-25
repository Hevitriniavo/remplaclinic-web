<?php
namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\Request\AddUsersToDto;
use App\Entity\User;
use App\Repository\RequestRepository;
use App\Repository\RequestResponseRepository;
use App\Repository\UserRepository;
use App\Service\Request\RequestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class UserRequestController extends AbstractController
{
    const JSON_LIST_GROUPS = ['request:datatable'];

    public function __construct(
        private RequestRepository $requestRepository,
        private RequestResponseRepository $requestResponseRepository,
        private UserRepository $userRepository,
    ) {}

    #[Route('/api/requests/{requestId}/personne-contacte', name: 'api_request_personne_contacte_list', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getPersonneContacteForRequest(Request $request, int $requestId): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->requestResponseRepository->findAllDataTables($requestId, $params), 200, [], ['groups' => self::JSON_LIST_GROUPS]);
    }

    #[Route('/api/requests/personne-contacte', name: 'api_request_personne_contacte_all', methods: ['GET'])]
    public function getAllUsersForRequest(Request $request): Response
    {
        $queryParams = $request->query->all();
        $params = DataTableParams::fromRequest($queryParams);

        return $this->json($this->userRepository->findAllDataTablesForRequest(User::ROLE_REPLACEMENT_ID, $params, $queryParams), 200, [], ['groups' => self::JSON_LIST_GROUPS]);
    }

    #[Route('/api/requests/personne-contacte/ids', name: 'api_request_personne_contacte_ids', methods: ['GET'])]
    public function getAllUsersIdForRequest(Request $request): Response
    {
        $params = $request->query->all();

        return $this->json($this->userRepository->findAllIdsForRequest(User::ROLE_REPLACEMENT_ID, $params));
    }

    #[Route('/api/requests/{requestId}/personne-contacte', name: 'api_request_personne_contacte_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addUsersIdForRequest(
        RequestService $requestService,
        int $requestId,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] AddUsersToDto $addUsersDto
    ): Response
    {
        $request = $this->requestRepository->find($requestId);

        if (is_null($request)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $requestService->initRequestResponse($request, $addUsersDto->users);

        return $this->json('ok');
    }
}