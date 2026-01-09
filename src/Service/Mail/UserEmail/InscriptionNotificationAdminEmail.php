<?php
namespace App\Service\Mail\UserEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\User;
use Twig\Environment;

class InscriptionNotificationAdminEmail
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
        return 'Remplaclinic / Instalclinic : Nouveau compte';
    }

    private function getBody(): string
    {
        $viewData = [
            'type_compte' => $this->user->getRole()->getRole(),
            'nom_etablissement' => $this->user->getEstablishmentName(),
            'prenom' => $this->user->getSurname(),
            'nom' => $this->user->getName(),
            'mail' => $this->user->getEmail(),
            'cp' => $this->templateValue($this->user->getAddress()?->getPostalCode()),
            'tel_fixe' => $this->templateValue($this->user->getTelephone()),
            'tel_port' => $this->templateValue($this->user->getTelephone2()),
            'detail_url' => empty($this->options['detail_url']) ? '#' : $this->options['detail_url'],
        ];

        if ($this->user->getRole()->getId() === User::ROLE_REPLACEMENT_ID) {
            $viewData['specialites'] = $this->user->getSpeciality()->getName();
        }
        
        return $this->twig->render(
            'mails/users/notification_admin_nouveau_compte.html.twig',
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