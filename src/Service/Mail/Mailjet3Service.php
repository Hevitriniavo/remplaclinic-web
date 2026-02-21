<?php
namespace App\Service\Mail;

use App\Entity\MailLog;
use App\Service\Taches\AppConfigurationService;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Mailjet3Service
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly AppConfigurationService $config,
    )
    {}

    public function send(MailLog $mailLog, bool $throwExcetionIfError = false): void
    {
        $url = $this->config->getValue('MAILJET_BASE_URL');
        $apiKey = $this->config->getValue('MAILJET_API_KEY');
        $secretKey = $this->config->getValue('MAILJET_SECRET_KEY');

        $sender = $mailLog->getSender();

        $mailjetData = [
            'auth_basic' => [$apiKey, $secretKey],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'FromEmail' => $sender['email'],
                'FromName' => $sender['name'],
                'Recipients' => [],
                'Subject' => $mailLog->getSubject(),
            ],
        ];

        // body
        if ($mailLog->isHtml()) {
            $mailjetData['json']['Html-part'] = $mailLog->getBody();
        } else {
            $mailjetData['json']['Text-part'] = $mailLog->getBody();
        }

        // recipients
        $to = explode(',', $mailLog->getTarget());
        foreach($to as $email) {
            $mailjetData['json']['Recipients'][] = [
                'Email' => $email
            ];
        }

        // cc
        if (!empty($mailLog->getCc())) {
            $mailjetData['json']['Cc'] = $mailLog->getCc();
        }

        // bcc
        if (!empty($mailLog->getBcc())) {
            $mailjetData['json']['Bcc'] = $mailLog->getBcc();
        }

        $response = $this->httpClient->request('POST', $url, $mailjetData);
        
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            if ($throwExcetionIfError) {
                throw new Exception('An error has been thrown by mailjet!');
            }
        }
    }
}