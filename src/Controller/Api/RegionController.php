<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\RegionDto;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class RegionController extends AbstractController
{
    public function __construct(private RegionRepository $regionRepository) {}
    
    #[Route('/api/regions', name: 'api_region_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->regionRepository->findAllDataTables($params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/regions', name: 'api_region_new', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] RegionDto $regionDto,
    ): Response {
        return $this->json($this->regionRepository->save($regionDto), Response::HTTP_CREATED, [], ['groups' => 'datatable']);
    }

    #[Route('/api/regions/{id}', name: 'api_region_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $region = $this->regionRepository->find($id);

        return $this->json(
            $region,
            is_null($region) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/regions/{id}', name: 'api_region_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] RegionDto $regionDto,
    ): Response {
        $region = $this->regionRepository->find($id);

        if (is_null($region)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $this->regionRepository->update($region, $regionDto);

        return $this->json(
            $region,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/regions/{id}', name: 'api_region_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id): Response
    {
        $deleted = $this->regionRepository->remove($id);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }
}
