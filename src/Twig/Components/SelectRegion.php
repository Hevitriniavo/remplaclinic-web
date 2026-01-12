<?php

namespace App\Twig\Components;

use App\Repository\RegionRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SelectRegion
{
    public function __construct(
        private readonly RegionRepository $regionRepository,
        private readonly RequestStack $request,
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
}
