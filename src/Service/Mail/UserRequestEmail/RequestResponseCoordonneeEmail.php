<?php
namespace App\Service\Mail\UserRequestEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\RequestResponse;
use App\Entity\User;
use Twig\Environment;

class RequestResponseCoordonneeEmail
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
        return 'Remplaclinic : Accédez aux coordonnées du demandeur';
    }

    private function getBody(): string
    {
        /**
         * @var RequestResponse
         */
        $requestResponse = $this->options['request_response'];

        $viewData = [
            'prenom' => $this->user->getSurname(),
            'nom' => $this->user->getName(),
            'croise' => $requestResponse->getStatus() === RequestResponse::ACCEPTE,
            'nom_etablissement' => $this->request->getApplicant()->getEstablishmentName(),
            'cp' => $this->templateValue($this->request->getApplicant()->getAddress()->getPostalCode()),
            'owner_prenom' => $this->request->getApplicant()->getSurname(),
            'owner_nom' => $this->request->getApplicant()->getName(),
            'mail' => $this->request->getApplicant()->getEmail(),
            'tel_fixe' => $this->templateValue($this->request->getApplicant()->getTelephone()),
            'tel_port' => $this->templateValue($this->request->getApplicant()->getTelephone2()),
        ];
        
        return $this->twig->render(
            'mails/user-requests/demande_mail_croise_remplacant.html.twig',
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