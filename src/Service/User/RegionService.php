<?php
namespace App\Service\User;

use App\Entity\Region;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityNotFoundException;

class RegionService
{
    public function __construct(private RegionRepository $regionRepository) {}

    public function getRegions(?array $regionIds): array
    {
        $regions = [];
        if (is_array($regionIds)) {
            foreach ($regionIds as $regionId) {
                $region = $this->regionRepository->find($regionId);
                if (!$region) {
                    throw new EntityNotFoundException("No region found for ID: $regionId");
                }
                $regions[] = $region;
            }
        }
        return $regions;
    }

    public function getRegion(?int $regionId): ?Region
    {
        if (is_null($regionId)) {
            return null;
        }

        return $this->getRegions([$regionId])[0];
    }
}
