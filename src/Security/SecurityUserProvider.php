<?php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

final class SecurityUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->em->getRepository(User::class)->findByEmail($identifier);

        if (!$user) {
            throw new UserNotFoundException();
        }

        return new SecurityUser($user);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new \InvalidArgumentException();
        }

        return new SecurityUser(
            $this->em->getRepository(User::class)->find($user->getUser()->getId())
        );
    }

    public function supportsClass(string $class): bool
    {
        return $class === SecurityUser::class;
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof SecurityUser) {
            throw new \InvalidArgumentException();
        }

        $userEntity = $user->getUser();
        $userEntity->setPassword($newHashedPassword);

        $this->em->flush();
    }
}
