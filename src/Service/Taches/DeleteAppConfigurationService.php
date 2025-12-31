<?php
namespace App\Service\Taches;

use App\Entity\AppConfiguration;
use App\Service\DeleteEntityService;
use Doctrine\ORM\EntityManagerInterface;

class DeleteAppConfigurationService
{
    private DeleteEntityService $deleteEntityService;

    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
        $this->deleteEntityService = new DeleteEntityService($em, AppConfiguration::class);
    }

    public function delete(int $id): ?AppConfiguration
    {   
        return $this->deleteEntityService->delete($id);
    }

    public function deleteMultiple(array $ids): array
    {
        return $this->deleteEntityService->deleteMultiple($ids);
    }
}