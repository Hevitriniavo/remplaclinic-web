<?php
namespace App\Service\Mail\UserEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Entity\User;
use Twig\Environment;

class AbonnementExpirationAdminEmail
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
            ->setTarget($this->options['_target'])
            ->setHtml(true)
        ;
    }

    private function getSubject(): string
    {
        return 'Remplaclinic / Instalclinic : expiration abonnement';
    }

    private function getBody(): string
    {
        // @TODO: change all path into emails template into url, the same for asset
        $viewData = [
            'users' => $this->options['users'],
        ];
        
        return $this->twig->render(
            'mails/users/notification_admin_expiration_abonnement.html.twig',
            $viewData
        );
    }
}