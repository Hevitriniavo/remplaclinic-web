<?php

namespace App\Controller\Api;

use App\Controller\Trait\UpdateCheckAccessTrait;
use App\Dto\DataTable\DataTableParams;
use App\Dto\User\ReplacementDto;
use App\Dto\User\UserFilesDto;
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
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;

class ReplacementController extends AbstractController
{
    use UpdateCheckAccessTrait;

    public function __construct(
        private UserRepository $userRepository,
        private readonly Security $security,
    ) {}

    #[Route('/api/replacements', name: 'api_replacement_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->userRepository->findAllDataTables(User::ROLE_REPLACEMENT_ID, $params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/replacements', name: 'api_replacement_new', methods: ['POST'])]
    public function create(
        Registration $registration,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] ReplacementDto $replacementDto,

        #[MapUploadedFile]
        mixed $cv,

        #[MapUploadedFile]
        mixed $diplom,

        #[MapUploadedFile]
        mixed $licence,
    ): Response {
        $user = $registration->register($replacementDto, $this->toUserFilesDto($cv, $diplom, $licence));

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->json($user, Response::HTTP_CREATED, [], ['groups' => 'datatable']);
        }

        // logged in the user
        $this->security->login(new SecurityUser($user));

        // redirect to signup validation
        return $this->json([ '_redirect' => $this->generateUrl('app_register_success') ], Response::HTTP_CREATED);
    }

    #[Route('/api/replacements/{id}', name: 'api_replacement_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
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

    #[Route('/api/replacements/{id}', name: 'api_replacement_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        UserUpdate $userUpdate,
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] ReplacementDto $replacementDto,

        #[MapUploadedFile]
        mixed $cv,

        #[MapUploadedFile]
        mixed $diplom,

        #[MapUploadedFile]
        mixed $licence,
    ): Response {
        $user = $this->userRepository->find($id);

        if (is_null($user)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        if (!$this->canUpdateUser($this->security, $user->getId())) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $userUpdate->update($user, $replacementDto, $this->toUserFilesDto($cv, $diplom, $licence));

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->json($user, Response::HTTP_OK, [], ['groups' => 'datatable']);
        }

        // redirect to espace-perso
        return $this->json([ '_redirect' => $this->generateUrl('app_user_espace_perso') ], Response::HTTP_OK);
    }

    private function toUserFilesDto($cv, $diplom, $licence): UserFilesDto
    {
        if (empty($cv)) {
            $cv = null;
        }
        if (empty($diplom)) {
            $diplom = null;
        }
        if (empty($licence)) {
            $licence = null;
        }
        return new UserFilesDto($cv, $diplom, $licence);
    }
}
