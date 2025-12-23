<?php
namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Entity\User;
use App\Repository\RequestResponseRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserRequestController extends AbstractController
{
    const JSON_LIST_GROUPS = ['request:datatable'];

    public function __construct(
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
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->userRepository->findAllDataTablesForRequest(User::ROLE_REPLACEMENT_ID, $params), 200, [], ['groups' => self::JSON_LIST_GROUPS]);
    }

    #[Route('/api/requests/personne-contacte/ids', name: 'api_request_personne_contacte_ids', methods: ['GET'])]
    public function getAllUsersIdForRequest(Request $request): Response
    {
        $searchValue = $request->query->get('search');
        return $this->json($this->userRepository->findAllIdsForRequest(User::ROLE_REPLACEMENT_ID, $searchValue));
    }
}