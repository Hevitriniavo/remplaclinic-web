<?php

namespace App\Controller\Api;

use App\Controller\Trait\UpdateCheckAccessTrait;
use App\Dto\DataTable\DataTableParams;
use App\Dto\User\ClinicDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\SecurityUser;
use App\Service\User\Registration;
use App\Service\User\UserUpdate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class ClinicController extends AbstractController
{
    use UpdateCheckAccessTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly Security $security,
    ) {}

    #[Route('/api/clinics', name: 'api_clinic_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->userRepository->findAllDataTables(User::ROLE_CLINIC_ID, $params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/clinics', name: 'api_clinic_new', methods: ['POST'])]
    public function create(
        Registration $registration,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] ClinicDto $clinicDto
    ): Response {

        $user = $registration->registerClinic($clinicDto);
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->json($user, Response::HTTP_CREATED, [], ['groups' => 'datatable']);
        }

        // logged in the user
        $this->security->login(new SecurityUser($user));

        // redirect to signup validation
        return $this->json([ '_redirect' => $this->generateUrl('app_register_success') ], Response::HTTP_CREATED);
    }

    #[Route('/api/clinics/{id}', name: 'api_clinic_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $clinic = $this->userRepository->find($id);

        return $this->json(
            $clinic,
            is_null($clinic) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
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

        if (!$this->canUpdateUser($this->security, $user->getId())) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $userUpdate->updateClinic($user, $clinicDto);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->json($user, Response::HTTP_OK, [], ['groups' => 'datatable']);
        }

        // redirect to espace peprso
        return $this->json([ '_redirect' => $this->generateUrl('app_user_espace_perso') ], Response::HTTP_OK);
    }
}
