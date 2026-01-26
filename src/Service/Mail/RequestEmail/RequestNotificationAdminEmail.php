<?php
namespace App\Service\Mail\RequestEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Entity\User;
use Twig\Environment;

class RequestNotificationAdminEmail
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ?Request $request,
        private readonly ?User $user,
        private readonly array $options = []
    )
    {}

    public function getEmail(): MailLog
    {
        return (new MailLog())
            ->setBody($this->getBody())
            ->setSubject($this->getSubject())
            ->setTarget($this->options['target_email'])
            ->setHtml(true)
        ;
    }

    private function getSubject(): string
    {
        if ($this->request->getRequestType() === RequestType::INSTALLATION) {
            return 'Instalclinic: Nouvelle proposition d\'installation';
        }

        return 'Remplaclinic: Nouvelle demande de remplacement';
    }

    private function getBody(): string
    {
        if ($this->request->getRequestType() === RequestType::INSTALLATION) {
            return $this->getBodyInstallation();
        }

        return $this->getBodyReplacement();
    }

    private function getBodyReplacement(): string
    {
        $viewData = [
            'specialite_demande' => $this->request->getSpeciality()->getName(),
            'nom_demandeur' => $this->request->getApplicant()->getApplicantName(),
            'ville_demandeur' => $this->request->getApplicant()->getApplicantLocality(),
            'date_debut_demande' => $this->request->getStartedAtFr(false),
            'date_fin_demande' => $this->request->getEndAtFr(false),
            'remuneration' => $this->request->getRemunerationOrRetrocession(),
            'commentaires_demande' => $this->request->getComment(),
            'specialites' => $this->request->hasSubSpecialities() ? $this->request->getSubSpecialities() : [['name' => 'Aucune sous-spécialité']],
            'request_id' => $this->request->getId(),
        ];
        
        return $this->twig->render(
            'mails/requests/notification_admin_nouvelle_demande.html.twig',
            $viewData
        );
    }

    private function getBodyInstallation(): string
    {
        $viewData = [
            'specialite_demande' => $this->request->getSpeciality()->getName(),
            'nom_demandeur' => $this->request->getApplicant()->getApplicantName(),
            'ville_demandeur' => $this->request->getApplicant()->getApplicantLocality(),
            'date_debut_demande' => $this->request->getStartedAtFr(false),
            'raisons' => $this->request->getReasons(),
            'remuneration' => $this->request->getRemunerationOrRetrocession(),
            'commentaires_demande' => $this->request->getComment(),
            'specialites' => $this->request->hasSubSpecialities() ? $this->request->getSubSpecialities() : [['name' => 'Aucune sous-spécialité']],
            'request_id' => $this->request->getId(),
        ];
        
        return $this->twig->render(
            'mails/requests/notification_admin_nouvelle_proposition.html.twig',
            $viewData
        );
    }
}