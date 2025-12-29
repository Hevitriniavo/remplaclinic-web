<?php
namespace App\Dto\Mailbox;

use App\Entity\MailLog;
use Symfony\Component\Validator\Constraints as Assert;

class ComposeEmailDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        public string $target,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $body,

        #[Assert\NotNull]
        #[Assert\NotBlank]
        public ?string $subject,

        public ?string $cc,

        public ?string $bcc,

        #[Assert\NotNull]
        public ?bool $html,
    ) {
    }

    public function toMail(): MailLog
    {
        return (new MailLog)
            ->setTarget($this->target)
            ->setBody($this->body)
            ->setHtml($this->html)
            ->setSubject($this->subject)
            ->setCc($this->cc)
            ->setBcc($this->bcc)
        ;
    }
}