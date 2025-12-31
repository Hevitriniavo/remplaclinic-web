<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly User $user
    ) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserIdentifier(): string
    {
        return $this->user->getEmail();
    }

    public function getPassword(): string
    {
        return $this->user->getPassword();
    }

    public function getRoles(): array
    {
        $roles = $this->user->getRoles();
        $allRoles = User::allRoles();

        $result = [];
        foreach($roles as $role) {
            if (array_key_exists($role->getId(), $allRoles)) {
                $result[] = 'ROLE_' . $allRoles[$role->getId()];
            }
        }

        $result[] = 'ROLE_USER';

        return array_unique($result);
    }

    public function eraseCredentials(): void
    {
        // nothing to erase
    }
}
