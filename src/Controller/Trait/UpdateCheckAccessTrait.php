<?php
namespace App\Controller\Trait;

use App\Security\SecurityUser;
use Symfony\Bundle\SecurityBundle\Security;

trait UpdateCheckAccessTrait
{
    private function canUpdateUser(Security $security, int $id): bool
    {
        /**
         * @var SecurityUser
         */
        $connectedUser = $security->getUser();

        return $connectedUser->getUser()->getId() === $id;
    }
}