<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\User\DirectorDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\Registration;
use App\Service\User\UserUpdate;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class DirectorController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    #[Route('/api/directors', name: 'api_director_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->userRepository->findAllDataTables(User::ROLE_DIRECTOR_ID, $params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/directors', name: 'api_director_new', methods: ['POST'])]
    public function create(
        Registration $registration,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] DirectorDto $directorDto
    ): Response {
        return $this->json($registration->registerDirector($directorDto), Response::HTTP_CREATED, [], ['groups' => 'datatable']);
    }

    #[Route('/api/directors/{id}', name: 'api_director_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $evidence = $this->userRepository->find($id);

        return $this->json(
            $evidence,
            is_null($evidence) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => ['datatable', 'full', 'user:with-clinics']]
        );
    }

    #[Route('/api/directors/{id}', name: 'api_director_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        UserUpdate $userUpdate,
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] DirectorDto $directorDto
    ): Response {
        $user = $this->userRepository->find($id);

        if (is_null($user)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $userUpdate->updateDirector($user, $directorDto);

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }
}
