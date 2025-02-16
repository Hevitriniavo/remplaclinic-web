<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\PartenaireLogoDto;
use App\Entity\PartenaireLogo;
use App\Repository\PartenaireLogoRepository;
use App\Service\FileCleaner;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;

class PartenaireController extends AbstractController
{
    public function __construct(
        private PartenaireLogoRepository $partenaireRepository,
        private FileUploader $uploader,
    ) {}
    
    #[Route('/api/partenaires', name: 'api_partenaire_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->partenaireRepository->findAllDataTables($params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/partenaires', name: 'api_partenaire_new', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] PartenaireLogoDto $partenaireDto,
        #[MapUploadedFile]
        ?UploadedFile $logo = null
    ): Response {
        $partenaire = new PartenaireLogo();

        $this->uploadAndUpdatePartenaireLogo($partenaire, $partenaireDto, $logo);

        return $this->json($this->partenaireRepository->save($partenaire), Response::HTTP_CREATED, [], ['groups' => 'datatable']);
    }

    #[Route('/api/partenaires/{id}', name: 'api_partenaire_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $partenaire = $this->partenaireRepository->find($id);

        return $this->json(
            $partenaire,
            is_null($partenaire) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/partenaires/{id}', name: 'api_partenaire_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] PartenaireLogoDto $partenaireDto,
        #[MapUploadedFile]
        ?UploadedFile $logo = null,
    ): Response {
        $partenaire = $this->partenaireRepository->find($id);

        if (is_null($partenaire)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $this->uploadAndUpdatePartenaireLogo($partenaire, $partenaireDto, $logo);

        $this->partenaireRepository->update($partenaire);

        return $this->json(
            $partenaire,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/partenaires/{id}', name: 'api_partenaire_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id, FileCleaner $fileCleaner): Response
    {
        $deleted = $this->partenaireRepository->remove($id);

        if (!is_null($deleted)) {
            $fileCleaner->remove($deleted->getLogo());
        }

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    private function uploadAndUpdatePartenaireLogo(PartenaireLogo $partenaire, PartenaireLogoDto $partenaireDto, ?UploadedFile $uploadedFile)
    {
        $partenaire->setName($partenaireDto->name);
        if (!empty($uploadedFile)) {
            $partenaire->setLogo($this->uploader->upload($uploadedFile));
        }
    }
}
