<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\User\ClinicDto;
use App\Repository\UserRepository;
use App\Service\User\Registration;
use App\Service\User\UserUpdate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class ClinicController extends AbstractController
{
    const ROLE_CLINIC_ID = 5;

    public function __construct(
        private UserRepository $userRepository,
    ) {}

    #[Route('/api/clinics', name: 'api_clinic_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->userRepository->findAllDataTables(self::ROLE_CLINIC_ID, $params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/clinics', name: 'api_clinic_new', methods: ['POST'])]
    public function create(
        Registration $registration,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] ClinicDto $clinicDto
    ): Response {
        return $this->json($registration->registerClinic($clinicDto), Response::HTTP_CREATED, [], ['groups' => 'datatable']);
    }

    #[Route('/api/clinics/{id}', name: 'api_clinic_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $evidence = $this->userRepository->find($id);

        return $this->json(
            $evidence,
            is_null($evidence) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => ['datatable', 'full']]
        );
    }

    #[Route('/api/clinics/{id}', name: 'api_clinic_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        UserUpdate $userUpdate,
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] ClinicDto $clinicDto
    ): Response {
        $user = $this->userRepository->find($id);

        if (is_null($user)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $userUpdate->updateClinic($user, $clinicDto);

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }
}
