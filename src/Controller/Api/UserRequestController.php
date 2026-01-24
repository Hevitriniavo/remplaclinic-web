<?php
namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\IdListDto;
use App\Dto\Request\AddUsersToDto;
use App\Entity\Request as EntityRequest;
use App\Entity\User;
use App\Repository\RequestRepository;
use App\Repository\RequestResponseRepository;
use App\Repository\UserRepository;
use App\Service\Request\DeleteRequestResponseService;
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
        private readonly RequestRepository $requestRepository,
        private readonly RequestResponseRepository $requestResponseRepository,
        private readonly UserRepository $userRepository,
    ) {}

    #[Route('/api/requests/{requestId}/personne-contacte', name: 'api_request_personne_contacte_list', methods: ['GET'], requirements: ['requestId' => '\d+'])]
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

    #[Route('/api/requests/{requestId}/personne-contacte', name: 'api_request_personne_contacte_add', methods: ['POST'], requirements: ['requestId' => '\d+'])]
    public function addUsersIdForRequest(
        RequestService $requestService,
        int $requestId,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] AddUsersToDto $addUsersDto
    ): Response
    {
        /**
         * @var EntityRequest
         */
        $request = $this->requestRepository->find($requestId);

        if (is_null($request)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $users = $addUsersDto->users;

        if ($addUsersDto->all) {
            $users = $this->userRepository->findAllIdsForRequest(
                User::ROLE_REPLACEMENT_ID,
                [
                    'speciality' => $request->getSpeciality()->getId(),
                    'mobility' => $request->getRegion()->getId(),
                    'condition_type' => 'and'
                ],
                $addUsersDto->users
            );
        }

        $requestService->initRequestResponse($request, $users);

        return $this->json('ok');
    }

    #[Route('/api/requests/{requestId}/personne-contacte/missing', name: 'api_request_personne_contacte_missing', methods: ['GET'], requirements: ['requestId' => '\d+'])]
    public function getUsersNotAttachedToRequest(int $requestId, Request $request): Response
    {
        $searchTerm = $request->query->get('search', '');

        $users = $this->requestResponseRepository->findAllUserNotAddedTo($requestId, $searchTerm);

        return $this->json($users);
    }

    #[Route('/api/requests/{requestId}/personne-contacte/{id}', name: 'api_request_personne_contacte_delete', methods: ['DELETE'], requirements: ['id' => '\d+', 'requestId' => '\d+'])]
    public function remove(int $id, DeleteRequestResponseService $deleteRequestResponse): Response
    {
        $deleted = $deleteRequestResponse->delete($id);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/api/requests/{requestId}/personne-contacte/delete-multiple', name: 'api_request_personne_contacte_delete_multiple', methods: ['DELETE'], requirements: ['requestId' => '\d+'])]
    public function removeMultiple(
        DeleteRequestResponseService $deleteRequestResponse,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] IdListDto $idList
    ): Response
    {

        $deleted = $deleteRequestResponse->deleteMultiple($idList->ids);

        return $this->json(
            '',
            !empty($deleted) ? Response::HTTP_OK : Response::HTTP_CONFLICT
        );
    }
}