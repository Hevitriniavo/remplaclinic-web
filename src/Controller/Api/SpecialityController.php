<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\SpecialityDto;
use App\Repository\SpecialityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class SpecialityController extends AbstractController
{
    public function __construct(private SpecialityRepository $specialityRepository) {}
    
    #[Route('/api/specialities', name: 'api_speciality_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->specialityRepository->findAllDataTables($params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/specialities', name: 'api_speciality_new', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] SpecialityDto $specialityDto,
    ): Response {
        return $this->json($this->specialityRepository->save($specialityDto), Response::HTTP_CREATED, [], ['groups' => 'datatable']);
    }

    #[Route('/api/specialities/{id}', name: 'api_speciality_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $speciality = $this->specialityRepository->find($id);

        return $this->json(
            $speciality,
            is_null($speciality) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/specialities/{id}/sub-specialites', name: 'api_speciality_sub_specialites', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getSousSpecialites(int $id): Response
    {
        $specialityListe = $this->specialityRepository->findAllSousSpecialite($id);

        return $this->json(
            $specialityListe,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/specialities/{id}', name: 'api_speciality_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] SpecialityDto $specialityDto,
    ): Response {
        $speciality = $this->specialityRepository->find($id);

        if (is_null($speciality)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $this->specialityRepository->update($speciality, $specialityDto);

        return $this->json(
            $speciality,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/specialities/{id}', name: 'api_speciality_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id): Response
    {
        $deleted = $this->specialityRepository->remove($id);

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }
}
