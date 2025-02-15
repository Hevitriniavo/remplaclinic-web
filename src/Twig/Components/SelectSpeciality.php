<?php
namespace App\Twig\Components;

use App\Repository\SpecialityRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SelectSpeciality
{
    public function __construct(private SpecialityRepository $specialityRepository)
    {
    }

    public function getSpecialities(): array
    {
        return $this->specialityRepository->findAll();
    }
}