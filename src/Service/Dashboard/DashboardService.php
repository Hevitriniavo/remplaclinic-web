<?php
namespace App\Service\Dashboard;

use App\Entity\Request;
use App\Entity\RequestResponse;
use App\Entity\RequestType;
use App\Entity\User;
use Doctrine\DBAL\Connection;

class DashboardService
{

    public function __construct(
        private readonly Connection $connection,
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
        $result = [
            'currentWeek' => [100, 120, 170, 167, 180, 177, 160],
            'lastWeek' => [60, 80, 70, 67, 80, 77, 100],
        ];

        return $result;
    }

    private function getResponseWeekStats(): array
    {
        $result = [
            'currentWeek' => [1000, 2000, 3000, 2500, 2700, 2500, 3000],
            'lastWeek' => [700, 1700, 2700, 2000, 1800, 1500, 2000],
        ];

        return $result;
    }
}