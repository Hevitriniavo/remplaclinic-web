<?php
namespace App\Service\Mail\UserRequestEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\User;
use Twig\Environment;

class RequestResponseOwnerNotificationEmail
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
            ->setTarget($this->request->getApplicant()->getEmail())
            ->setHtml(true)
        ;
    }

    private function getSubject(): string
    {
        return 'Remplaclinic : un remplaçant a postulé';
    }

    private function getBody(): string
    {
        $viewData = [
            'prenom' => $this->request->getApplicant()->getSurname(),
            'nom' => $this->request->getApplicant()->getName(),
        ];
        
        return $this->twig->render(
            'mails/user-requests/demande_mail_croise_demandeur.html.twig',
            $viewData
        );
    }
}