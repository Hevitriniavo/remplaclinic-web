<?php
namespace App\Controller\Api;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Dto\IdListDto;
use App\Dto\Taches\AppImportationScriptDto;
use App\Dto\Taches\AppSchedulerDto;
use App\Message\MessengerService;
use App\Service\Taches\AppImportationScriptService;
use App\Service\Taches\AppSchedulerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TachesController extends AbstractController
{
    public function __construct(
        private readonly MessengerService $messengerService,
        private readonly AppImportationScriptService $importationService,
        private readonly AppSchedulerService $schedulerService,
    ){}

    #[Route(
        '/api/taches/{tachePage}s',
        name: 'api_admin_taches_list',
        requirements: [
            'tachePage' => 'evenement|importation|scheduler'
        ]
    )]
    public function listEvenements(Request $request, string $tachePage): JsonResponse
    {
        if ($tachePage === 'evenement') {
            $params = DataTableParams::fromRequest($request->query->all());
            return $this->json($this->messengerService->getAllMessages($params));
        }

        if ($tachePage === 'importation') {
            $params = DataTableParams::fromRequest($request->query->all());
            return $this->json($this->importationService->findAll($params), 200, [], ['groups' => 'datatable']);
        }

        if ($tachePage === 'scheduler') {
            $params = DataTableParams::fromRequest($request->query->all());
            return $this->json($this->schedulerService->findAll($params), 200, [], ['groups' => 'datatable']);
        }

        return $this->json(DataTableResponse::fromData([]), 200, [], ['groups' => 'datatable']);
    }

    #[Route(
        '/api/taches/{tachePage}s/{id}',
        name: 'api_admin_taches_delete',
        methods: ['DELETE'],
        requirements: [
            'id' => '\d+',
            'tachePage' => 'evenement|importation|scheduler'
        ]
    )]
    public function removeTachesEntity(int $id, string $tachePage): Response
    {
        $deleted = false;

        if ($tachePage === 'evenement') {
            $deleted = $this->messengerService->delete($id);
        }

        if ($tachePage === 'importation') {
            $deleted = $this->importationService->delete($id);
        }

        if ($tachePage === 'scheduler') {
            $deleted = $this->schedulerService->delete($id);
        }

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    #[Route(
        '/api/taches/{tachePage}s/delete-multiple',
        name: 'api_admin_taches_delete_multiple',
        methods: ['DELETE'],
        requirements: [
            'tachePage' => 'evenement|importation|scheduler'
        ]
    )]
    public function removeTachesEntityMultiple(
        string $tachePage,
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] IdListDto $idList
    ): Response
    {
        $deleted = false;

        if ($tachePage === 'evenement') {
            $deleted = $this->messengerService->deleteMultiple($idList->ids);
        }

        if ($tachePage === 'importation') {
            $deleted = $this->importationService->deleteMultiple($idList->ids);
        }

        if ($tachePage === 'scheduler') {
            $deleted = $this->schedulerService->deleteMultiple($idList->ids);
        }

        return $this->json(
            '',
            $deleted ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    #[Route(
        '/api/taches/{tachePage}s/generate-default',
        name: 'api_admin_taches_generate_default',
        methods: ['POST'],
        requirements: [
            'tachePage' => 'importation|scheduler'
        ]
    )]
    public function generateDefaultTachesEntity(string $tachePage): Response
    {
        if ($tachePage === 'importation') {
            $this->importationService->generateDefault();
        }

        return $this->json('');
    }

    #[Route(
        '/api/taches/importations/create',
        name: 'api_admin_taches_importations_create',
        methods: ['POST']
    )]
    public function createTachesImportations(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] AppImportationScriptDto $importationDto,
    ): Response
    {
        return $this->json($this->importationService->store($importationDto), 200, [], ['groups' => 'datatable']);
    }

    #[Route(
        '/api/taches/schedulers/create',
        name: 'api_admin_taches_schedulers_create',
        methods: ['POST']
    )]
    public function createTachesScheduler(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_BAD_REQUEST
        )] AppSchedulerDto $schedulerDto,
    ): Response
    {
        return $this->json($this->schedulerService->store($schedulerDto), 200, [], ['groups' => 'datatable']);
    }

    #[Route(
        '/api/taches/{tachePage}s/detail/{id}',
        name: 'api_admin_taches_detail',
        methods: ['GET'],
        requirements: [
            'id' => '\d+',
            'tachePage' => 'importation|scheduler'
        ]
    )]
    public function viewTachesElement(string $tachePage, int $id): Response
    {
        $tacheElement = null;

        if ($tachePage === 'importation') {
            $tacheElement = $this->importationService->retrieve($id);
        }

        if ($tachePage === 'scheduler') {
            $tacheElement = $this->schedulerService->retrieve($id);
        }
        return $this->json($tacheElement, 200, [], ['groups' => ['datatable', 'full']]);
    }

    #[Route(
        '/api/taches/{tachePage}s/execute/{id}',
        name: 'api_admin_taches_execute',
        methods: ['GET'],
        requirements: [
            'id' => '\d+',
            'tachePage' => 'importation|scheduler'
        ]
    )]
    public function executeTachesElement(string $tachePage, $id): Response
    {
        if ($tachePage === 'importation') {
            $this->importationService->execute($id);
        }
        
        return $this->json('');
    }
}
