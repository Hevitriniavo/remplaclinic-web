<?php
namespace App\Service\Mail\UserRequestEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\User;
use Twig\Environment;

class InscriptionRequestInstallationEmail
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
        return 'Instalclinic : liste des propositions en cours';
    }

    private function getBody(): string
    {
        $viewData = [
            'prenom' => $this->user->getSurname(),
            'nom' => $this->user->getName(),
            'requests' => empty($this->options['requests']) ? [] : $this->options['requests'],
        ];
        
        return $this->twig->render(
            'mails/user-requests/creation_proposition_en_cours.html.twig',
            $viewData
        );
    }
}