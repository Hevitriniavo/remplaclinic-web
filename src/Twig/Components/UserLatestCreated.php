<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class UserLatestCreated
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->userRepository->findLatestOrderByCreatedAt(7);
    }
}
