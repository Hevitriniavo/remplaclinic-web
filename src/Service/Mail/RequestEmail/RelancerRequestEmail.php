<?php
namespace App\Service\Mail\RequestEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Entity\User;
use Twig\Environment;

class RelancerRequestEmail
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
        if ($this->request->getRequestType() === RequestType::REPLACEMENT) {
            return 'Remplaclinic - Des remplaçants ont postulé à votre demande';
        }

        return 'Instalclinic - Des candidats ont postulé à votre proposition';
    }

    private function getBody(): string
    {
        $viewData = [
            'prenom' => $this->request->getApplicant()->getSurname(),
            'nom' => $this->request->getApplicant()->getApplicantName(),
        ];

        $viewName = 'relance_du_demandeur';

        if (array_key_exists('users', $this->options)) {
            $viewData['users'] = $this->options['users'];
        } else {
            $viewData['users'] = [];
        }
        
        return $this->twig->render(
            'mails/requests/' . $viewName . '.html.twig',
            $viewData
        );
    }
}