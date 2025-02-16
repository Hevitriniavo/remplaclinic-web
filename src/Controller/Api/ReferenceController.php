<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\EvidenceDto;
use App\Repository\EvidenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class ReferenceController extends AbstractController
{
    public function __construct(
        private EvidenceRepository $evidenceRepository,
    ) {}

    #[Route('/api/references', name: 'api_reference_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->evidenceRepository->findAllDataTables($params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/references', name: 'api_reference_new', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] EvidenceDto $evidenceDto
    ): Response {
        return $this->json($this->evidenceRepository->save($evidenceDto), Response::HTTP_CREATED, [], ['groups' => 'datatable']);
    }

    #[Route('/api/references/{id}', name: 'api_reference_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $evidence = $this->evidenceRepository->find($id);

        return $this->json(
            $evidence,
            is_null($evidence) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/references/{id}', name: 'api_reference_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] EvidenceDto $evidenceDto,
    ): Response {
        $evidence = $this->evidenceRepository->find($id);

        if (is_null($evidence)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $this->evidenceRepository->update($evidence, $evidenceDto);

        return $this->json(
            $evidence,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/references/{id}', name: 'api_reference_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id): Response
    {
        $deleted = $this->evidenceRepository->remove($id);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }
}
