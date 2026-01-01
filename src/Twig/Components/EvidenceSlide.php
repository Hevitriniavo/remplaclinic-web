<?php

namespace App\Twig\Components;

use App\Entity\Evidence;
use App\Repository\EvidenceRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class EvidenceSlide
{
    public function __construct(
        private readonly EvidenceRepository $evidenceRepository,
    ) {}

    /**
     * @return Evidence[]
     */
    public function getEvidences(): array
    {
        return $this->evidenceRepository->findAllOrderByIdDesc();
    }
}
