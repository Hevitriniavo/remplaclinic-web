<?php
namespace App\Service\Request;

use App\Entity\EmailEvents;
use App\Entity\Request;
use App\Entity\RequestType;
use App\Service\Mail\MailService;
use App\Service\Mail\RequestMailBuilder;
use App\Service\Taches\AppConfigurationService;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class ArchiverService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AppConfigurationService $config,
        private readonly MailService $mailService,
        private readonly RequestMailBuilder $mailBuilder,
    ) {}

    public function execute(): void
    {
        $this->executeArchiveRequests(RequestType::REPLACEMENT);
        $this->executeArchiveRequests(RequestType::INSTALLATION);
    }

    private function executeArchiveRequests(RequestType $requestType)
    {
        $requests = $this->getRequests($requestType);

        $this->archiveRequests($requestType);

        $this->sendNotification($requests, $requestType);
    }

    private function getRequests(RequestType $requestType): array
    {   
        $queryBuilder = $this->em->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('r.id', 'r.title')
            ->from('request', 'r');
        
        $this->addWhereStatement($queryBuilder, $requestType);

        
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    private function archiveRequests(RequestType $requestType): int
    {
        $updateQb = $this->em->getConnection()->createQueryBuilder();
        $updateQb
            ->update('request', 'r')
            ->set('status', ':new_status')
            ->setParameter('new_status', Request::ARCHIVED);
        
        $this->addWhereStatement($updateQb, $requestType);

        return $updateQb->executeStatement();
    }

    private function addWhereStatement(QueryBuilder $qb, RequestType $requestType)
    {
        $qb
            ->andWhere('r.request_type = :request_type')
            ->setParameter('request_type', $requestType->value)
            ->andWhere('r.status = :status')
            ->setParameter('status', Request::IN_PROGRESS);
        
        if ($requestType === RequestType::INSTALLATION) {
            $qb
                ->andWhere('r.started_at < :today')
                ->setParameter('today', date('Y-m-d 00:00'));
        } else {
            $qb
                ->andWhere('r.end_at < :today')
                ->setParameter('today', date('Y-m-d 00:00'));
        }
    }

    private function sendNotification(array $requests, RequestType $requestType)
    {
        $mail = $this->mailBuilder->build(EmailEvents::REQUEST_ARCHIVAGE, null, null, [
            '_target' => $this->config->getValue('REQUEST_NOTIFICATION_TARGET_EMAIL'),
            'requests' => $requests,
            '_request_type' => $requestType,
        ]);

        $this->mailService->send($mail);
    }
}