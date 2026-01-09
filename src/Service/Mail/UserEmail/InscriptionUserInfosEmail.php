<?php
namespace App\Service\Mail\UserEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Entity\User;
use Twig\Environment;

class InscriptionUserInfosEmail
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
            ->setTarget($this->user->getEmail())
            ->setHtml(true)
        ;
    }

    private function getSubject(): string
    {
        return 'Remplaclinic / Instalclinic : information de votre compte';
    }

    private function getBody(): string
    {
        $viewData = [
            'prenom' => $this->user->getSurname(),
            'nom' => $this->user->getName(),
            'username' => $this->user->getEmail(),
            'password' => $this->options['raw_password'],
        ];
        
        return $this->twig->render(
            'mails/users/inscription.html.twig',
            $viewData
        );
    }
}