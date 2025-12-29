<?php

namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\IdListDto;
use App\Dto\Taches\AppConfigurationDto;
use App\Entity\AppConfiguration;
use App\Repository\AppConfigurationRepository;
use App\Service\Taches\DeleteAppConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class AppConfigurationController extends AbstractController
{
    public function __construct(
        private AppConfigurationRepository $appConfigurationRepository,
    ) {}
    
    #[Route('/api/taches-configurations', name: 'api_taches_configurations_get', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = DataTableParams::fromRequest($request->query->all());
        return $this->json($this->appConfigurationRepository->findAllDataTables($params), 200, [], ['groups' => 'datatable']);
    }

    #[Route('/api/taches-configurations', name: 'api_taches_configurations_new', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] AppConfigurationDto $appConfigurationDto
    ): Response {
        $appConfiguration = new AppConfiguration();

        $this->updateAppConfiguration($appConfiguration, $appConfigurationDto);

        return $this->json($this->appConfigurationRepository->save($appConfiguration), Response::HTTP_CREATED, [], ['groups' => 'datatable']);
    }

    #[Route('/api/taches-configurations/{id}', name: 'api_taches_configurations_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDetail(int $id): Response
    {
        $appConfiguration = $this->appConfigurationRepository->find($id);

        return $this->json(
            $appConfiguration,
            is_null($appConfiguration) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/taches-configurations/{id}', name: 'api_taches_configurations_update', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] AppConfigurationDto $appConfigurationDto
    ): Response {
        $appConfiguration = $this->appConfigurationRepository->find($id);

        if (is_null($appConfiguration)) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        $this->updateAppConfiguration($appConfiguration, $appConfigurationDto);

        $this->appConfigurationRepository->update($appConfiguration);

        return $this->json(
            $appConfiguration,
            Response::HTTP_OK,
            [],
            ['groups' => 'datatable']
        );
    }

    #[Route('/api/taches-configurations/{id}', name: 'api_taches_configurations_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function remove(int $id, DeleteAppConfigurationService $deleteAppConfigurationService): Response
    {
        $deleted = $deleteAppConfigurationService->delete($id);

        return $this->json(
            '',
            !is_null($deleted) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/api/taches-configurations/delete-multiple', name: 'api_taches_configurations_delete_multiple', methods: ['DELETE'])]
    public function removeMultiple(
        DeleteAppConfigurationService $deleteAppConfigurationService,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] IdListDto $idList
    ): Response
    {

        $deleted = $deleteAppConfigurationService->deleteMultiple($idList->ids);

        return $this->json(
            '',
            !empty($deleted) ? Response::HTTP_OK : Response::HTTP_CONFLICT
        );
    }

    private function updateAppConfiguration(AppConfiguration $appConfiguration, AppConfigurationDto $appConfigurationDto)
    {
        $appConfiguration->setName($appConfigurationDto->name);
        $appConfiguration->setValue($appConfigurationDto->value);
        $appConfiguration->setActive($appConfigurationDto->active);
    }
}
