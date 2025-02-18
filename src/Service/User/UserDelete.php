<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FileCleaner;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class UserDelete
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private FileCleaner $fileCleaner,
    ) {}
    
    public function remove(int $id)
    {
        /**
         * @var User
         */
        $user = $this->userRepository->find($id);
        if (is_null($user)) {
            throw new EntityNotFoundException('No user found for ' . $id);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->fileCleaner->remove($user->getCv());
        $this->fileCleaner->remove($user->getDiplom());
        $this->fileCleaner->remove($user->getLicence());

        return true;
    }
}