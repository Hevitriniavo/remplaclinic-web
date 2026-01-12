<?php
namespace App\Twig\Components;

use App\Repository\SpecialityRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SelectSpeciality
{
    public function __construct(
        private readonly SpecialityRepository $specialityRepository,
        private readonly RequestStack $request, 
    )
    {}

    public function getSpecialities(): array
    {
        return $this->specialityRepository->findAll();
    }

    public function getActiveSpeciality(): ?int
    {
        $activeSpeciality = $this->request->getCurrentRequest()->query->get('specialite', 'all');
        
        if (strtolower($activeSpeciality) != 'all') {
            return (int) $activeSpeciality;
        }

        return null;
    }
}