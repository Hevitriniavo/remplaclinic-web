<?php

namespace App\Twig\Components;

use App\Entity\Region;
use App\Repository\RegionRepository;
use App\Security\SecurityUser;
use App\Service\User\RegionService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SelectRegion
{
    public function __construct(
        private readonly RegionRepository $regionRepository,
        private readonly RequestStack $request,
        private readonly Security $security,
        private readonly RegionService $regionService,
    ) {}

    public function getRegions(): array
    {
        return $this->regionRepository->findAll();
    }

    public function getActiveRegion(): ?int
    {
        $activeRegion = $this->request->getCurrentRequest()->query->get('region', 'all');
        
        if (strtolower($activeRegion) != 'all') {
            return (int) $activeRegion;
        }

        return null;
    }

    public function getUserRegion(): ?int
    {
        if ($this->security->isGranted('ROLE_DOCTOR') || $this->security->isGranted('ROLE_CLINIC')) {
            /**
             * @var SecurityUser
             */
            $user = $this->security->getUser();
            $codePostale = $user->getUser()->getAddress()?->getPostalCode();

            if ($codePostale) {
                $region = $this->regionService->getRegionByCodePostal($codePostale);

                if (!is_null($region)) {
                    return $region->getId();
                }
            }
        }

        return null;
    }
}
