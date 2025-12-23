<?php

namespace App\Twig\Components;

use App\Repository\UserRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SelectClinic
{
    public function __construct(private UserRepository $userRepository) {}

    public function getUsers(): array
    {
        return $this->userRepository->findAll();
    }
}
