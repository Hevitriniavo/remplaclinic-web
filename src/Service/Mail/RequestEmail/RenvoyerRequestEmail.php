<?php
namespace App\Service\Mail\RequestEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Entity\User;
use Twig\Environment;

class RenvoyerRequestEmail
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
        if ($this->request->getRequestType() === RequestType::REPLACEMENT) {
            return sprintf(
                'Remplaclinic - Postuler pour la demande de remplacement du  %s au %s à %s',
                $this->request->getStartedAtFr(false),
                $this->request->getEndAtFr(false),
                $this->getApplicantLocality(),
            );
        }

        return sprintf(
            "Instalclinic - Postuler pour la proposition d'installation à partir du %s à %s",
            $this->request->getStartedAtFr(false),
            $this->request->getEndAtFr(false),
            $this->getApplicantLocality(),
        );
    }

    private function getBody(): string
    {
        $viewData = [
            'prenom' => $this->user->getSurname(),
            'nom' => $this->user->getName(),
            'specialite_demande' => $this->request->getSpeciality()->getName(),
            'nom_demandeur' => $this->request->getApplicant()->getApplicantName(),
            'ville_demandeur' => $this->getApplicantLocality(),
            'date_debut_demande' => $this->request->getStartedAtFr(false),
            'date_fin_demande' => $this->request->getEndAtFr(false),
            'remuneration' => $this->request->getRemuneration(),
            'commentaires_demande' => $this->request->getComment(),
            'specialites' => $this->request->getSubSpecialities(),
            'user_id' => $this->user->getId(),
            'request_id' => $this->request->getId(),
        ];

        $viewName = 'relance_de_remplacement';

        if ($this->request->getRequestType() === RequestType::INSTALLATION) {
            $viewData['raisons'] = $this->request->getReasons();
            $viewName = 'relance_d_installation';
        }
        
        return $this->twig->render(
            'mails/requests/' . $viewName . '.html.twig',
            $viewData
        );
    }

    private function getApplicantLocality(): string
    {
        return $this->request->getApplicant()->getAddress()?->getLocality();
    }
}