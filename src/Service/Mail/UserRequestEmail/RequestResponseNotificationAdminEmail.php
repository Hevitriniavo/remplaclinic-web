<?php
namespace App\Service\Mail\UserRequestEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Entity\User;
use Twig\Environment;

class RequestResponseNotificationAdminEmail
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
            return 'Instalclinic : Nouvelle réponse';
        }

        return 'Remplaclinic: Nouvelle réponse';
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
            'commentaires_demande' => $this->request->getComment(),
            'specialites' => $this->request->hasSubSpecialities() ? $this->request->getSubSpecialities() : [['name' => 'Aucune sous-spécialité']],
            'prenom' => $this->user->getSurname(),
            'nom' => $this->user->getName(),
            'mail' => $this->user->getEmail(),
            'tel_fixe' => $this->templateValue($this->user->getTelephone()),
            'tel_port' => $this->templateValue($this->user->getTelephone2()),
            'request_id' => $this->request->getId(),
        ];
        
        return $this->twig->render(
            'mails/user-requests/notification_admin_nouvelle_reponse.html.twig',
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
            'commentaires_demande' => $this->request->getComment(),
            'prenom' => $this->user->getSurname(),
            'nom' => $this->user->getName(),
            'mail' => $this->user->getEmail(),
            'tel_fixe' => $this->templateValue($this->user->getTelephone()),
            'tel_port' => $this->templateValue($this->user->getTelephone2()),
            'request_id' => $this->request->getId(),
        ];
        
        return $this->twig->render(
            'mails/user-requests/notification_admin_nouvelle_reponse_installation.html.twig',
            $viewData
        );
    }

    private function templateValue(?string $value, string $defaultValue = 'Non renseigné'): string
    {
        if (empty($value)) {
            return $defaultValue;
        }

        return $value;
    }
}