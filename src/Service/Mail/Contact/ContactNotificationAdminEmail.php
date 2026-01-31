<?php
namespace App\Service\Mail\Contact;

use App\Entity\Contact;
use App\Entity\MailLog;
use App\Entity\Request;
use App\Entity\User;
use Exception;
use Twig\Environment;

class ContactNotificationAdminEmail
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

    private function getContact(): Contact
    {
        $contact = array_key_exists('contact', $this->options) ? $this->options['contact'] : null;

        if (!($contact instanceof Contact)) {
            throw new Exception('Contact object is required for admin notification.');
        }

        return $contact;
    }

    private function getSubject(): string
    {
        if ($this->getContact()->getContactType() === Contact::CONTACT_INSTAL_CLINIC) {
            return 'Instalclinic: Nouveau message';
        }

        return 'Remplaclinic: Nouveau message';
    }

    private function getBody(): string
    {
        $contact = $this->getContact();

        $viewData = [
            'type_message' => $contact->getContactTypeAsText(),
            'contact' => $contact,
        ];
        
        return $this->twig->render(
            'mails/contacts/notification_admin_contact.html.twig',
            $viewData
        );
    }
}