<?php
namespace App\Service\Mail;

use App\Entity\MailLog;
use App\Service\Taches\AppConfigurationService;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Mailjet31Service
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
                'Messages' => [[
                    'From' => [
                        'Email' => $sender['email'],
                        'Name' => $sender['name']
                    ],
                    'To' => [],
                    'Subject' => $mailLog->getSubject(),
                ]],
            ]
        ];

        // body
        if ($mailLog->isHtml()) {
            $mailjetData['json']['Messages'][0]['HTMLPart'] = $mailLog->getBody();
        } else {
            $mailjetData['json']['Messages'][0]['TextPart'] = $mailLog->getBody();
        }

        // recipients
        $to = explode(',', $mailLog->getTarget());
        foreach($to as $email) {
            $mailjetData['json']['Messages'][0]['To'][] = [
                'Email' => $email
            ];
        }

        // cc
        if (!empty($mailLog->getCc())) {
            $cc = explode(',', $mailLog->getCc());
            $ccList = [];
            foreach($cc as $email) {
                $ccList[] = [
                    'Email' => $email
                ];
            }
            $mailjetData['json']['Messages'][0]['Cc'] = $ccList;
        }

        // bcc
        if (!empty($mailLog->getBcc())) {
            $bcc = explode(',', $mailLog->getBcc());
            $bccList = [];
            foreach($bcc as $email) {
                $bccList[] = [
                    'Email' => $email
                ];
            }
            $mailjetData['json']['Messages'][0]['Bcc'] = $bccList;
        }

        // reply_to
        if ($this->config->hasValue('MAILJET_REPLY_TO')) {
            $mailjetData['json']['Messages'][0]['ReplyTo'] = [
                'Email' => $this->config->getValue('MAILJET_REPLY_TO')
            ];
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