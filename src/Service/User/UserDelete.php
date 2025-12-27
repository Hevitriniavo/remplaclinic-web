<?php

namespace App\Service\User;

use App\Entity\User;
use App\Service\FileCleaner;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class UserDelete
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService,
        private FileCleaner $fileCleaner,
    ) {}
    
    public function remove(int $id)
    {
        /**
         * @var User
         */
        $user = $this->userService->getUser($id);

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->fileCleaner->remove($user->getCv());
        $this->fileCleaner->remove($user->getDiplom());
        $this->fileCleaner->remove($user->getLicence());

        return true;
    }

    public function removeMultiple(array $ids)
    {
        /**
         * @var User[]
         */
        $users = $this->userService->getUsers($ids);

        foreach($users as $user) {
            $this->entityManager->remove($user);
            
            $this->fileCleaner->remove($user->getCv());
            $this->fileCleaner->remove($user->getDiplom());
            $this->fileCleaner->remove($user->getLicence());
        }

        $this->entityManager->flush();

        return true;
    }
}