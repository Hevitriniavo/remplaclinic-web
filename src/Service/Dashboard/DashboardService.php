<?php
namespace App\Service\Dashboard;

use App\Entity\Request;
use App\Entity\RequestResponse;
use App\Entity\RequestType;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DashboardService
{

    public function __construct(
        private readonly Connection $connection,
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {}

    /**
     * Recuperer les donnees necessaires au tableau de bord. Le schema devrait strictement respecter pour que le front fonctionne.
     */
    public function getData(): array
    {
        return [
            'users' => $this->getUsersCount(),
            'requests' => [
                'remplacement' => $this->getRequestCount(RequestType::REPLACEMENT),
                'installation' => $this->getRequestCount(RequestType::INSTALLATION),
                'users' => $this->getUsersCountByStatus(),
                'reponses' => $this->getResponseCount(),
            ],
            'inscription' => $this->getInscriptionWeekStats(),
            'reponse' => $this->getResponseWeekStats(),
        ];
    }

    /**
     * Add mille separator to a given number
     */
    private function nb($value, $separator = ' '): string
    {
        if (!$value) {
            return '0';
        }
        $result = [];

        $mille = 0;
        while($value % 1000 > 0) {
            if ($mille > 0) {
                array_unshift($result, str_pad($mille . '', 3, '0', STR_PAD_LEFT));
            }
            $mille = $value % 1000;
            $value = intdiv($value, 1000);
        }

        if ($mille > 0) {
            array_unshift($result, $mille);
        }

        return implode($separator, $result);
    }

    private function getUsersCount(): array
    {
        $roles = [
            'remplacant' => User::ROLE_REPLACEMENT_ID,
            'doctor' => User::ROLE_DOCTOR_ID,
            'director' => User::ROLE_CLINIC_ID,
            'clinic' => User::ROLE_DIRECTOR_ID,
        ];

        $sql = 'select r.user_role_id AS role, count(u.id) AS total FROM user_user_role r JOIN user u ON r.user_id = u.id WHERE r.user_role_id IN ('.  implode(',', array_values($roles)) .') GROUP BY r.user_role_id';

        $query = $this->connection->prepare($sql);

        $rs = $query->executeQuery()
            ->fetchAllAssociative();

        $roles = array_flip($roles);

        $result = [];

        foreach($rs as $row) {
            $result[$roles[$row['role']]] = $this->nb(+$row['total']);
        }

        foreach($roles as $roleName) {
            if (!array_key_exists($roleName, $result)) {
                $result[$roleName] = 0;
            }
        }

        return $result;
    }

    private function getUsersCountByStatus(): array
    {
        $statusList = [
            'actif' => 1,
            'inactif' => 0,
        ];

        $sql = 'select u.status as status, count(u.id) as total from user u group by u.status';

        $query = $this->connection->prepare($sql);

        $rs = $query->executeQuery()
            ->fetchAllAssociative();

        $statusList = array_flip($statusList);

        $result = [];

        $total = 0;
        foreach($rs as $row) {
            $total += $row['total'];
            $result[$statusList[$row['status']]] = $this->nb(+$row['total']);
        }

        foreach($statusList as $statusName) {
            if (!array_key_exists($statusName, $result)) {
                $result[$statusName] = 0;
            }
        }

        return [
            'total' => $this->nb($total),
            'status' => [
                $result['actif'], // actif
                $result['inactif'] // inactif
            ]
        ];
    }

    private function getResponseCount(): array
    {
        $statusList = [
            'accepte' => RequestResponse::ACCEPTE,
            'plus_infos' => RequestResponse::PLUS_D_INFOS,
            'exclu' => RequestResponse::EXCLU,
            'en_cours' => RequestResponse::EN_COURS,
        ];

        $sql = 'select rr.status as status, count(rr.id) as total from request_response rr where rr.updated_at like ? group by rr.status';

        $query = $this->connection->prepare($sql);
        $query->bindValue(1, date('Y-m') . '%');

        $rs = $query->executeQuery()
            ->fetchAllAssociative();

        $statusList = array_flip($statusList);

        $result = [];

        $total = 0;
        foreach($rs as $row) {
            $total += $row['total'];
            $result[$statusList[$row['status']]] = +$row['total'];
        }

        foreach($statusList as $statusName) {
            if (!array_key_exists($statusName, $result)) {
                $result[$statusName] = 0;
            }
        }

        $percentage = 0;
        if ($total > 0) {
            $percentage = 100.0 * ($result['accepte'] + $result['plus_infos']) / $total;
        }

        return [
            'total' => $this->nb($total),
            'percentage' => round($percentage, 2),
            'status' => [
                $this->nb($result['accepte']), // accepte
                $this->nb($result['plus_infos']), // plus d'info
                $this->nb($result['exclu']) // exclue
            ]
        ];
    }

    private function getRequestCount(RequestType $requestType): array
    {
        $statusList = [
            'a_valider' => Request::CREATED,
            'en_cours' => Request::IN_PROGRESS,
            'archive' => Request::ARCHIVED,
        ];

        $sql = 'select req.status as status, count(req.id) as total from request req where req.request_type = ? group by req.status';

        $query = $this->connection->prepare($sql);
        $query->bindValue(1, $requestType === RequestType::REPLACEMENT ? 'replacement' : 'installation');

        $rs = $query->executeQuery()
            ->fetchAllAssociative();

        $statusList = array_flip($statusList);

        $result = [];

        $total = 0;
        foreach($rs as $row) {
            $total += $row['total'];
            $result[$statusList[$row['status']]] = $this->nb(+$row['total']);
        }

        foreach($statusList as $statusName) {
            if (!array_key_exists($statusName, $result)) {
                $result[$statusName] = 0;
            }
        }

        return [
            'total' => $this->nb($total),
            'status' => [
                $result['a_valider'], // A valider
                $result['en_cours'], // En cours
                $result['archive'] // Archive
            ]
        ];
    }

    private function getInscriptionWeekStats(): array
    {
        // step 1: generate date
        $thisWeeks = $this->weekDates('monday this week');
        $lastWeeks = $this->weekDates('monday last week');

        // step 2: get inscription counts
        $inscriptionThisWeeks = $this->getInscriptionCounts($thisWeeks);
        $inscriptionLastWeeks = $this->getInscriptionCounts($lastWeeks);

        // step 4: compare and format result
        $result = [
            'currentWeek'  => [],
            'lastWeek'  => [],
        ];
        $totalThisWeek = 0;
        $totalLastWeek = 0;
        for($i = 0; $i < 7; $i++) {
            $dayThisWeek = $thisWeeks[$i];
            $dayLastWeek = $lastWeeks[$i];

            if (array_key_exists($dayThisWeek, $inscriptionThisWeeks)) {
                $result['currentWeek'][$i] = $inscriptionThisWeeks[$dayThisWeek];
                $totalThisWeek += $inscriptionThisWeeks[$dayThisWeek];
            } else {
                $result['currentWeek'][$i] = 0;
            }

            if (array_key_exists($dayLastWeek, $inscriptionLastWeeks)) {
                $result['lastWeek'][$i] = $inscriptionLastWeeks[$dayLastWeek];
                $totalLastWeek += $inscriptionLastWeeks[$dayLastWeek];
            } else {
                $result['lastWeek'][$i] = 0;
            }
        }

        $percentage = $this->percentChange($totalLastWeek, $totalThisWeek);

        $result['chartData'] = [
            'total' => $totalLastWeek + $totalThisWeek,
            'percentage' => $percentage,
            'up' => $percentage > 0,
            'viewHref' => $this->urlGenerator->generate('app_admin_replacement', [
                'created_from' => (new DateTimeImmutable('monday last week'))->format('d/m/Y')
            ])
        ];

        return $result;
    }

    private function getResponseWeekStats(): array
    {
        // step 1: generate date
        $thisWeeks = $this->weekDates('monday this week');
        $lastWeeks = $this->weekDates('monday last week');

        // step 2: get response counts
        $responseThisWeeks = $this->getResponseCounts($thisWeeks);
        $responseLastWeeks = $this->getResponseCounts($lastWeeks);

        // step 4: compare and format result
        $result = [
            'currentWeek'  => [],
            'lastWeek'  => [],
        ];
        $totalThisWeek = 0;
        $totalLastWeek = 0;
        for($i = 0; $i < 7; $i++) {
            $dayThisWeek = $thisWeeks[$i];
            $dayLastWeek = $lastWeeks[$i];

            if (array_key_exists($dayThisWeek, $responseThisWeeks)) {
                $result['currentWeek'][$i] = $responseThisWeeks[$dayThisWeek];
                $totalThisWeek += $responseThisWeeks[$dayThisWeek];
            } else {
                $result['currentWeek'][$i] = 0;
            }

            if (array_key_exists($dayLastWeek, $responseLastWeeks)) {
                $result['lastWeek'][$i] = $responseLastWeeks[$dayLastWeek];
                $totalLastWeek += $responseLastWeeks[$dayLastWeek];
            } else {
                $result['lastWeek'][$i] = 0;
            }
        }

        $percentage = $this->percentChange($totalLastWeek, $totalThisWeek);

        $result['chartData'] = [
            'total' => $totalLastWeek + $totalThisWeek,
            'percentage' => $percentage,
            'up' => $percentage > 0,
            'viewHref' => $this->urlGenerator->generate('app_admin_request_response', [
                'updated_from' => (new DateTimeImmutable('monday last week'))->format('d/m/Y'),
                'status' => RequestResponse::ACCEPTE,
            ])
        ];

        return $result;
    }

    private function weekDates(string $weekStartDay): array
    {
        $startOfWeek = new DateTimeImmutable($weekStartDay);

        $week = [];
        for ($i = 0; $i < 7; $i++) {
            $week[] = $startOfWeek->modify("+{$i} days")->format('Y-m-d');
        }

        return $week;
    }

    private function getInscriptionCounts(array $days): array
    {
        $placeholders = implode(',', array_fill(0, count($days), '?'));

        $sql = "SELECT DATE(create_at) AS day, COUNT(*) AS total FROM user WHERE DATE(create_at) IN ($placeholders) GROUP BY DATE(create_at)";

        $stmt = $this->connection->prepare($sql);
        
        $rs = $stmt->executeQuery($days);
        $result = [];

        while(($row = $rs->fetchAssociative()) !== false) {
            $result[$row['day']] = $row['total'];
        }

        return $result;
    }

    private function percentChange(int|float $previous, int|float $current): ?float
    {
        if ($previous == 0) {
            return null; // or 0, or 100 — depends on your business rule
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getResponseCounts(array $days): array
    {
        $status = [ RequestResponse::ACCEPTE, RequestResponse::PLUS_D_INFOS ];
        $placeholders = implode(',', array_fill(0, count($days), '?'));

        $sql = "SELECT DATE(r.updated_at) AS day, COUNT(r.id) AS total FROM request_response r WHERE DATE(r.updated_at) IN ($placeholders) AND r.status IN (". implode(',', $status) .") GROUP BY DATE(r.updated_at)";

        $stmt = $this->connection->prepare($sql);
        
        $rs = $stmt->executeQuery($days);
        $result = [];

        while(($row = $rs->fetchAssociative()) !== false) {
            $result[$row['day']] = $row['total'];
        }

        return $result;
    }
}