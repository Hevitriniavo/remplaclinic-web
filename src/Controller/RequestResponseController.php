<?php
namespace App\Controller;

use App\Entity\RequestResponse;
use App\Security\SecurityUser;
use App\Service\Request\PostulerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RequestResponseController extends AbstractController
{

    public function __construct(
        private readonly PostulerService $postulerService,
    )
    {}

    #[Route(
        '/mon-compte/{userId}/{requestId}/postuler/{responseStatus}',
        name: 'app_user_request_response_answer',
        requirements: [
            'userId' => '\d+',
            'requestId' => '\d+',
            'responseStatus' => RequestResponse::ACCEPTE . '|' . RequestResponse::PLUS_D_INFOS,
        ]
    )]
    public function postuler(int $userId, int $requestId, int $responseStatus, FlashBagAwareSessionInterface $flashBag): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_REPLACEMENT');

        /**
         * @var SecurityUser
         */
        $user = $this->getUser();
        if ($user->getUser()->getId() !== $userId) {
            throw new AccessDeniedException('Access denied.');
        }

        $this->validateApplication($userId, $requestId, $responseStatus, $flashBag);

        return $this->redirectToRoute('app_user_request_response_confirmation');
    }

    #[Route(
        '/je-postule/{userId}/{requestId}/postuler/{responseStatus}',
        name: 'app_user_request_response_from_mail',
        requirements: [
            'userId' => '\d+',
            'requestId' => '\d+',
            'responseStatus' => RequestResponse::ACCEPTE . '|' . RequestResponse::PLUS_D_INFOS,
        ]
    )]
    public function postulerFromMail(int $userId, int $requestId, int $responseStatus, FlashBagAwareSessionInterface $flashBag): RedirectResponse
    {
        //@TODO: check user authenticity

        $this->validateApplication($userId, $requestId, $responseStatus, $flashBag);

        return $this->redirectToRoute('app_user_request_response_confirmation');
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

    #[Route(
        '/je-postule/confirmation',
        name: 'app_user_request_response_confirmation',
    )]
    public function postulerConfirmation(FlashBagAwareSessionInterface $flashBag)
    {
        $messages = $flashBag->getFlashBag()->all();
        foreach(['message1', 'message2', 'message3', 'dashboard_url'] as $message) {
            if (!array_key_exists($message, $messages)) {
                return $this->redirectToRoute('app_user_espace_perso');
            }
        }

        return $this->render('espace-perso/mes-demandes/request-response-confirmation.html.twig', $messages);
    }

    private function validateApplication(int $user, int $request, int $responseStatus, FlashBagAwareSessionInterface $flashBag)
    {
        $messages = [];

        $this->postulerService->validate($user, $request, $responseStatus, $messages);

        $flashBag->getFlashBag()->setAll($messages);
    }
}