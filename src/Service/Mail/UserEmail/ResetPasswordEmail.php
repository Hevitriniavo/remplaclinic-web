<?php
namespace App\Service\Mail\UserEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Entity\User;
use App\Entity\UserResetPasswordToken;
use Exception;
use Twig\Environment;

class ResetPasswordEmail
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
        return 'Remplaclinic / Instalclinic : demande de changement de mot de passe';
    }

    private function getBody(): string
    {
        if (empty($this->options['token'])) {
            throw new Exception('Vous devez fournir le token de demande de changement de mot de passe.');
        }

        /**
         * @var UserResetPasswordToken
         */
        $token = $this->options['token'];

        $viewData = [
            'prenom' => $this->user->getSurname(),
            'nom' => $this->user->getName(),
            'code' => $token->getCode(),
            'date_expiration' => $token->getExpiredAt()->format('d/m/Y H:i')
        ];
        
        return $this->twig->render(
            'mails/users/reset-password.html.twig',
            $viewData
        );
    }
}