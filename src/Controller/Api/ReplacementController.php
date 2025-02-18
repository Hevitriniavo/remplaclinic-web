<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\User\ReplacementDto;
use App\Dto\User\UserFilesDto;
use App\Repository\UserRepository;
use App\Service\User\Registration;
use App\Service\User\UserUpdate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;

class ReplacementController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
    ) {}

    #[Route('/api/replacements', name: 'api_replacement_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->userRepository->findAllReplacementDataTables($params), 200, [], ['groups' => 'datatable']);
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
        return $this->json($registration->register($replacementDto, $this->toUserFilesDto($cv, $diplom, $licence)), Response::HTTP_CREATED, [], ['groups' => 'datatable']);
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

        $userUpdate->update($user, $replacementDto, $this->toUserFilesDto($cv, $diplom, $licence));

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
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
