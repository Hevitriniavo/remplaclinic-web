<?php
namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;

class UserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function getUsers(?array $userIds): array
    {
        $users = [];
        if (is_array($userIds)) {
            foreach ($userIds as $userId) {
                $user = $this->userRepository->find($userId);
                if (!$user) {
                    throw new EntityNotFoundException("No user found for ID: $userId");
                }
                $users[] = $user;
            }
        }
        return $users;
    }

    public function getUser(?int $userId): ?User
    {
        if (is_null($userId)) {
            return null;
        }

        return $this->getUsers([$userId])[0];
    }
}
