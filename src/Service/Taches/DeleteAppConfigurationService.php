<?php
namespace App\Service\Taches;

use App\Entity\AppConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class DeleteAppConfigurationService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {}

    public function delete(int $id): ?AppConfiguration
    {   
        $configurations = $this->getAppConfigurations([$id]);

        if (count($configurations) > 0) {
            $this->em->remove($configurations[0]);
            $this->em->flush();

            return $configurations[0];
        }

        return null;
    }

    public function deleteMultiple(array $ids): array
    {
        $result = $this->getAppConfigurations($ids);

        foreach($result as $configurations) {
            $this->em->remove($configurations);
        }

        $this->em->flush();

        return $result;
    }

    private function getAppConfigurations(array $ids): array
    {
        $res = [];

        foreach($ids as $id) {
            $configurations = $this->em->find(AppConfiguration::class, $id);

            if (!$configurations) {
                throw new Exception('No app configuration found for #' . $id);
            }

            $res[] = $configurations;
        }

        return $res;
    }
}