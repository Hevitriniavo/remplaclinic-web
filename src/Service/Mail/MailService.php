<?php
namespace App\Service\Mail;

use App\Entity\MailLog;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MailService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $httpClient,
    )
    {}

    public function send(MailLog $mail): ?MailLog
    {   
        $this->addMissingAttr($mail);

        // store the mail log
        $this->em->persist($mail);
        $this->em->flush();

        // send mail to mailjet

        return $mail;
    }

    private function addMissingAttr(MailLog $mail)
    {
        if (empty($mail->getCreatedAt())) {
            $mail->setCreatedAt(new DateTimeImmutable());
        }

        if (empty($mail->getSentAt())) {
            $mail->setSentAt(new DateTimeImmutable());
        }

        if (empty($mail->getSender())) {
            $mail->setSender([
                'name' => 'Abned',
                'email' => 'fanabned@gmail.com'
            ]);
        }
    }
}