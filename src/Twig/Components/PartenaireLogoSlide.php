<?php

namespace App\Twig\Components;

use App\Entity\PartenaireLogo;
use App\Repository\PartenaireLogoRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PartenaireLogoSlide
{
    public function __construct(
        private readonly PartenaireLogoRepository $partenaireLogoRepository,
    ) {}

    /**
     * @return PartenaireLogo[]
     */
    public function getPartenairesLogo(): array
    {
        return $this->partenaireLogoRepository->findAllOrderByIdDesc();
    }
}
