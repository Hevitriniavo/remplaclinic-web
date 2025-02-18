<?php

namespace App\Twig\Components;

use App\Repository\RegionRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SelectRegion
{
    public function __construct(private RegionRepository $regionRepository) {}

    public function getRegions(): array
    {
        return $this->regionRepository->findAll();
    }
}
