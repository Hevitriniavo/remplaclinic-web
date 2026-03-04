<?php
namespace App\Controller\Trait;

use App\Exceptions\ApiException;
use App\Security\SecurityUser;
use Symfony\Bundle\SecurityBundle\Security;

trait RequestCheckAccessTrait
{
    private function canCreateOrUpdateUser(Security $security, int $applicantId): bool
    {
        if (!$security->isGranted('ROLE_USER')) {
            throw ApiException::make("Vous devez se connecter pour créer une nouvelle demande.", 401);
        }

        /**
         * @var SecurityUser
         */
        $connectedUser = $security->getUser();

        return $connectedUser->getUser()->getId() === $applicantId;
    }
    
    private function checkUserAbonnement(Security $security, bool $withInstallation = false)
    {
        $hasAccessReplacement = $security->isGranted('ROLE_USER_ABONNEMENT') && $security->isGranted('ROLE_USER_ABONNEMENT_ACTIF');
        $hasAccessInstallation = !$withInstallation || $security->isGranted('ROLE_USER_INSTALLATION');

        if (!($hasAccessReplacement && $hasAccessInstallation)) {
            throw ApiException::make("Vous devez disposer d'un abonnement actif pour créer une nouvelle demande.", 403);
        }
    }
}