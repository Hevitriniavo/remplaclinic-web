<?php
namespace App\Controller\Trait;

use App\Security\SecurityUser;
use Symfony\Bundle\SecurityBundle\Security;

trait RequestCheckAccessTrait
{
    private function canCreateOrUpdateUser(Security $security, int $applicantId): bool
    {
        /**
         * @var SecurityUser
         */
        $connectedUser = $security->getUser();

        return $connectedUser->getUser()->getId() === $applicantId;
    }
}