<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class RequestResponseController extends AbstractController
{
    #[Route(
        '/mon-compte/{userId}/{requestId}/postuler/{responseStatus}',
        name: 'app_user_request_response_answer',
        requirements: [
            'userId' => '\d+',
            'requestId' => '\d+',
            'responseStatus' => '1|2',
        ]
    )]
    public function postuler(int $userId, int $requestId, int $responseStatus): RedirectResponse
    {
        // @TODO: handle user response here and fix redirect
        return $this->redirectToRoute('app_home');
    }
    
    #[Route(
        '/mon-compte/remplacants/mes-demandes-de-remplacements',
        name: 'app_user_request_response_list'
    )]
    public function getUserResponses(): RedirectResponse
    {
        // @TODO: handle user response here and fix redirect
        return $this->redirectToRoute('app_home');
    }

    #[Route(
        '/partager-une-demande-de-remplacement/{requestId}',
        name: 'app_user_request_share',
        requirements: [
            'requestId' => '\d+',
        ]
    )]
    public function share(int $requestId): RedirectResponse
    {
        // @TODO: handle user response here and fix redirect
        return $this->redirectToRoute('app_home');
    }

    #[Route(
        '/mon-compte/remplacants/mes-propositions-d-installation',
        name: 'app_user_request_response_installation_list'
    )]
    public function getUserResponsesInstallation(): RedirectResponse
    {
        // @TODO: handle user response here and fix redirect
        return $this->redirectToRoute('app_home');
    }

    #[Route(
        '/partager-une-proposition-d-installation/{requestId}',
        name: 'app_user_request_installation_share',
        requirements: [
            'requestId' => '\d+',
        ]
    )]
    public function shareInstallation(int $requestId): RedirectResponse
    {
        // @TODO: handle user response here and fix redirect
        return $this->redirectToRoute('app_home');
    }
}