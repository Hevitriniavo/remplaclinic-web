<?php
namespace App\Service\Taches;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Dto\Taches\AppSchedulerDto;
use App\Entity\AppScheduler;
use App\Service\DeleteEntityService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class AppSchedulerService
{
    private DeleteEntityService $deleteEntityService;

    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
        $this->deleteEntityService = new DeleteEntityService($em, AppScheduler::class);
    }

    public function findAll(DataTableParams $params): DataTableResponse
    {
        return $this->em->getRepository(AppScheduler::class)->findAllDataTables($params);
    }

    public function store(AppSchedulerDto $scheduler): AppScheduler
    {
        $repository = $this->em->getRepository(AppScheduler::class);
        $appScheduler = $repository->findOneBy(['label' => $scheduler->label]);

        if (!is_null($appScheduler)) {
            throw new Exception('There is already a scheduler with label {' . $appScheduler->getLabel() . '}');
        }

        $appScheduler = new AppScheduler();
        $appScheduler
            ->setLabel($scheduler->label)
            ->setScript($scheduler->script)
            ->setOptions($scheduler->options)
            ->setTime($scheduler->time);
        
        $this->em->persist($appScheduler);
        $this->em->flush();

        return $appScheduler;
    }

    public function retrieve(int $id): AppScheduler
    {
        $appScheduler = $this->em->find(AppScheduler::class, $id);

        if (is_null($appScheduler)) {
            throw new Exception('No scheduler found for #' . $id);
        }

        return $appScheduler;
    }

    public function delete(int $id): ?AppScheduler
    {   
        return $this->deleteEntityService->delete($id);
    }

    public function deleteMultiple(array $ids): array
    {
        return $this->deleteEntityService->deleteMultiple($ids);
    }
}