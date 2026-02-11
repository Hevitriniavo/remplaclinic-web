<?php
namespace App\Service\User;

use App\Entity\EmailEvents;
use App\Entity\User;
use App\Service\Mail\MailService;
use App\Service\Mail\RequestMailBuilder;
use App\Service\Taches\AppConfigurationService;
use DateTime;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

final class UserAbonnementService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AppConfigurationService $config,
        private readonly MailService $mailService,
        private readonly RequestMailBuilder $mailBuilder,
    ) {}

    public function checkAbonnementEndDate(): void
    {
        $users = $this->getUsers();

        $this->updateNotificationFlag();

        $this->sendNotification($users);
    }

    private function getUsers(): array
    {   
        $queryBuilder = $this->em->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('u.id', 'u.name', 'u.surname', 'e.name AS establishmentName', 'uur.user_role_id AS role_id')
            ->distinct()
            ->from('user_subscription', 'sub')
            ->join('sub', 'user', 'u', 'sub.id = u.subscription_id')
            ->leftJoin('u', 'user_establishment', 'e', 'e.id = u.establishment_id')
            ->join('u', 'user_user_role', 'uur', 'u.id = uur.user_id AND uur.user_role_id IN ('. implode(', ', [User::ROLE_REPLACEMENT_ID, User::ROLE_CLINIC_ID, User::ROLE_DOCTOR_ID]) .')');
        
        $this->addWhereStatement($queryBuilder);

        $rs = $queryBuilder->executeQuery();
        $result = [];

        while(($row = $rs->fetchAssociative()) !== false) {
            $result[] = [
                'id' => $row['id'],
                'role_id' => $row['role_id'],
                'name' => empty($row['establishmentName']) ? 'Docteur ' . $row['name'] . ' '. $row['surname'] : $row['establishmentName']
            ];
        }

        return $result;
    }

    private function updateNotificationFlag(): int
    {
        $updateQb = $this->em->getConnection()->createQueryBuilder();
        $updateQb
            ->update('user_subscription', 'sub')
            ->set('end_notification', 1);
        
        $this->addWhereStatement($updateQb);

        return $updateQb->executeStatement();
    }

    private function addWhereStatement(QueryBuilder $qb)
    {
        $endDate = new DateTime('+3 months');
        $qb
            ->andWhere('sub.end_at <= :end_at')
            ->setParameter('end_at', $endDate->format('Y-m-d 23:59'))
            ->andWhere('(sub.end_notification = 0 OR sub.end_notification IS NULL)');
    }

    private function sendNotification(array $users)
    {
        $mail = $this->mailBuilder->build(EmailEvents::USER_ABONNEMENT_EXPIRATION, null, null, [
            '_target' => $this->config->getValue('USER_INSCRIPTION_TARGET_EMAIL'),
            'users' => $users,
        ]);

        $this->mailService->send($mail);
    }
}