<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class FormRechercheRemplacant
{
    public function __construct(
        private readonly UserRepository $userRepository,
        // private readonly SpecialityRepository $specialityRepository,
        // private readonly RegionRepository $regionRepository
    ) {}

    public function getRemplacants(): array
    {
        return $this->userRepository->findAllByRole(User::ROLE_REPLACEMENT_ID);
    }

    public function getCountRemplacant(): int
    {
        return $this->userRepository->countAllByRole(User::ROLE_REPLACEMENT_ID);
    }
}
