<?php
namespace App\Service\User;

use App\Entity\Speciality;
use App\Repository\SpecialityRepository;
use Doctrine\ORM\EntityNotFoundException;

class SpecialityService
{
    public function __construct(private SpecialityRepository $specialityRepository) {}

    public function getSpecialities(?array $specialityIds): array
    {
        $specialities = [];
        if (is_array($specialityIds)) {
            foreach ($specialityIds as $specialityId) {
                $speciality = $this->specialityRepository->find($specialityId);
                if (!$speciality) {
                    throw new EntityNotFoundException("No speciality found for ID: $specialityId");
                }
                $specialities[] = $speciality;
            }
        }
        return $specialities;
    }

    public function getSpeciality(?int $specialityId): ?Speciality
    {
        if (is_null($specialityId)) {
            return null;
        }
        return $this->getSpecialities([$specialityId])[0];
    }
}
