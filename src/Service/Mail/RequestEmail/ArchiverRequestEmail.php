<?php
namespace App\Service\Mail\RequestEmail;

use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Entity\User;
use Twig\Environment;

class ArchiverRequestEmail
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
        if ($this->options['_request_type'] === RequestType::REPLACEMENT) {
            return 'Remplaclinic - Demande(s) de remplacement archivée(s)';
        }

        return "Instalclinic - Demande(s) d'installations archivée(s)";
    }

    private function getBody(): string
    {
        // @TODO: change all path into emails template into url, the same for asset
        $requestType = $this->options['_request_type'];
        $viewData = [
            'requests' => $this->options['requests'],
        ];

        $viewName = 'notification_admin_archives_remplacements';
        if ($requestType === RequestType::INSTALLATION) {
            $viewName = 'notification_admin_archives_installations';
        }
        
        return $this->twig->render(
            'mails/requests/' . $viewName . '.html.twig',
            $viewData
        );
    }
}