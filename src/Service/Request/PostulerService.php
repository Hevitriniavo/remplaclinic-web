<?php
namespace App\Service\Request;

use App\Entity\EmailEvents;
use App\Entity\Request;
use App\Entity\RequestResponse;
use App\Entity\RequestType;
use App\Service\Mail\MailService;
use App\Service\Mail\RequestMailBuilder;
use App\Service\Taches\AppConfigurationService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostulerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MailService $mailService,
        private readonly RequestMailBuilder $mailBuilder,
        private readonly AppConfigurationService $config
    )
    {}

    public function validate(int $user, int $request, int $responseStatus, array &$messages): ?RequestResponse
    {
        /**
         * @var RequestResponseRepository
         */
        $requestResponseRepository = $this->em->getRepository(RequestResponse::class);
        /**
         * @var RequestResponse
         */
        $requestResponse = $requestResponseRepository->findOneByUserIdAndRequestId($user, $request);
        if (is_null($requestResponse)) {
            $messages = [
                'dashboard_url' => $this->urlGenerator->generate('app_user_espace_perso'),
                'message1' => 'Désolé, ce demande ne vous concerne pas!',
                'message2' => 'Vous pouvez retrouver les différentes demandes qui vous correspondent depuis votre tableau de bord.',
                'message3' => '',
            ];

            return null;
        }
        
        $dashboardUrl = $this->urlGenerator->generate($requestResponse->getRequest()->getRequestType() === RequestType::INSTALLATION ? 'app_user_requets_installation' : 'app_user_requets_replacement');

        if ($requestResponse->getRequest()->getStatus() === Request::ARCHIVED) {
            $messages = [
                'dashboard_url' => $dashboardUrl,
                'message1' => 'Désolé, ce demande est déjà archivé !',
                'message2' => 'Vous pouvez retrouver les différentes demandes qui sont en cours depuis votre tableau de bord.',
                'message3' => '',
            ];
        } else if ($requestResponse->getStatus() === RequestResponse::ACCEPTE) {
            $messages = [
                'dashboard_url' => $dashboardUrl,
                'message1' => 'Votre réponse a déjà été traitée !',
                'message2' => "Veuillez contacter un administrateur si vous n'avez pas eu de suite à votre réponse.",
                'message3' => '',
            ];
        } else {
            // step 1: update candidature
            $this->updateRequestResponse($requestResponse, $responseStatus);
            $this->updateRequestResponseCount($requestResponse->getRequest());
            
            // step 2 - 3 - 4: envoi des emails vers demandeur, candidat et admin 
            if ($requestResponse->getRequest()->getRequestType() === RequestType::INSTALLATION) {
                $this->sendApplicationEmailInstallation($requestResponse);
            } else {
                $this->sendApplicationEmailReplacement($requestResponse);
            }
            
            if ($requestResponse->getRequest()->getRequestType() === RequestType::INSTALLATION) {
                $messages = [
                    'dashboard_url' => $dashboardUrl,
                    'message1' => 'Votre réponse a été traitée avec succès !',
                    'message2' => "Merci d'avoir postulé à la demande. <br/> L'équipe d'Instalclinic reviendra vers vous dans les plus brefs délais.",
                    'message3' => "Envoyez votre CV, en indiquant la référence de l'offre (<b>" . $requestResponse->getRequest()->getId() . "</b>), par mail à l'adresse suivante : <b>contact2@remplaclinic.fr</b>.",
                ];
            } else {
                $messages = [
                    'dashboard_url' => $dashboardUrl,
                    'message1' => 'Votre réponse a été traitée avec succès !',
                    'message2' => "Un message vous a été envoyé à l'adresse suivante : <b>" . $requestResponse->getUser()->getEmail() . "</b>.",
                    'message3' => "Envoyez votre CV, en indiquant la référence de l'offre (<b>" . $requestResponse->getRequest()->getId() . "</b>), par mail à l'adresse suivante : <b>conseil@remplaclinic.fr</b>.",
                ];
            }
        }

        return $requestResponse;
    }

    private function updateRequestResponse(RequestResponse $requestResponse, int $responseStatus)
    {
        $requestResponse
            ->setStatus($responseStatus)
            ->setUpdatedAt(new DateTimeImmutable())
        ;
        $this->em->flush();
    }

    private function updateRequestResponseCount(Request $request)
    {
        $request->incrementResponseCount();
        
        $this->em->flush();
    }

    private function sendApplicationEmailReplacement(RequestResponse $requestResponse)
    {
        // notification au demandeur
        if ($requestResponse->getStatus() === RequestResponse::ACCEPTE) {
            $mailLog = $this->mailBuilder
                ->build(EmailEvents::REQUEST_REPONSE_DEMANDEUR, $requestResponse->getRequest(), $requestResponse->getUser(), [
                    'request_response' => $requestResponse,
                ]);
            $this->mailService->send($mailLog);
        }

        // envoi des coordonnees au candidat
        $mailLog = $this->mailBuilder
            ->build(EmailEvents::REQUEST_REPONSE_COORDONNEE, $requestResponse->getRequest(), $requestResponse->getUser(), [
                'request_response' => $requestResponse,
            ]);
        $this->mailService->send($mailLog);

        // notification de l'admin
        $mailLog = $this->mailBuilder
            ->build(EmailEvents::REQUEST_REPONSE_ADMIN, $requestResponse->getRequest(), $requestResponse->getUser(), [
                'request_response' => $requestResponse,
                'target_email' => $this->config->getValue('REQUEST_NOTIFICATION_TARGET_EMAIL'),
            ]);
        $this->mailService->send($mailLog);
    }

    private function sendApplicationEmailInstallation(RequestResponse $requestResponse)
    {
        // pas de notification au demandeur
        // pas d'envoi des coordonnees au candidat
        // notification de l'admin
        $mailLog = $this->mailBuilder
            ->build(EmailEvents::REQUEST_REPONSE_ADMIN, $requestResponse->getRequest(), $requestResponse->getUser(), [
                'request_response' => $requestResponse,
                'target_email' => $this->config->getValue('REQUEST_NOTIFICATION_TARGET_EMAIL'),
            ]);
        $this->mailService->send($mailLog);
    }
}