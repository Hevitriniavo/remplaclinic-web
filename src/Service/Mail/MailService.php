<?php
namespace App\Service\Mail;

use App\Entity\MailLog;
use App\Message\Request\RequestMessageMailSenderInterface;
use App\Service\Taches\AppConfigurationService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class MailService implements RequestMessageMailSenderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Mailjet3Service $mailjet3,
        private readonly Mailjet31Service $mailjet31,
        private readonly AppConfigurationService $appConfig,
    )
    {}

    public function send(MailLog $mail): ?MailLog
    {   
        $this->addMissingAttr($mail);

        // store the mail log
        $this->em->persist($mail);
        $this->em->flush();

        // send mail to mailjet
        $this->sendMail($mail);

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
                'name' => $this->appConfig->getValue('APP_EMAIL_FROM_NAME'),
                'email' => $this->appConfig->getValue('APP_EMAIL_FROM_EMAIL')
            ]);
        }
    }

    private function sendMail(MailLog $mailLog): void
    {
        $this->appConfig->loadAll([
            'MAILJET_API_KEY',
            'MAILJET_API_VERSION',
            'MAILJET_SECRET_KEY',
            'MAILJET_BASE_URL',
            'APP_MAILJET_ACTIVE',
            'MAILJET_REPLY_TO'
        ]);

        $isActive = $this->appConfig->getValue('APP_MAILJET_ACTIVE');

        if ($isActive === '1') {
            $apiVersion = $this->appConfig->getValue('MAILJET_API_VERSION');

            if ($apiVersion === '31') {
                $this->mailjet31->send($mailLog);
            } else {
                $this->mailjet3->send($mailLog);
            }
        }   
    }
}