<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\RequestResponse;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RequestResponse>
 */
class RequestResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestResponse::class);
    }

    public function findAllDataTables(int $requestId, DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['a.id', 'a.id', 'u.name', 'u.surname', 's.name', 'a.status'], 'a.id');

        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('a.request', 'r')
            ->where('r.id = :requestId')
            ->setParameter('requestId', $requestId)
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);
        
        if (!empty($params->value)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'u.name LIKE :value',
                        'u.surname LIKE :value',
                        'u.email LIKE :value',
                        's.name LIKE :value'
                    )
                )
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());
        
        $result = [];
        foreach($paginator as $row) {
            $result[] = [
                'id' => $row->getId(),
                'statut' => $row->getStatus(),
                'user' => [
                    'id' => $row->getUser()->getId(),
                    'name' => $row->getUser()->getName(),
                    'surname' => $row->getUser()->getSurname(),
                    'speciality' => [
                        'id' => $row->getUser()->getSpeciality()->getId(),
                        'name' => $row->getUser()->getSpeciality()->getName(),
                    ],
                ],
            ];
        }

        $response = DataTableResponse::fromPaginator($paginator, $params->draw + 1, true);
        $response->data = $result;
        return $response;
    }

    public function findAllUserNotAddedTo(int $requestId, ?string $searchTerm = ''): array
    {
        $sql = 'FROM `user` AS u';
        $sql .= ' JOIN `user_user_role` AS r ON r.user_id = u.id';
        $sql .= ' LEFT JOIN `request_response` as rr ON rr.user_id = u.id AND rr.request_id = :request_id'; // join to request response with the given ID
        $sql .= ' WHERE rr.id IS NULL';
        $sql .= ' AND u.status = 1'; // only active
        $sql .= ' AND r.user_role_id = ' . User::ROLE_REPLACEMENT_ID; // only user replacement

        $params = [
            'request_id' => $requestId,
        ];

        if (!empty($searchTerm)) {
            $sql .= ' AND (u.name LIKE :search_value OR u.surname LIKE :search_value OR u.email LIKE :search_value)';
            $params['search_value'] = '%' . $searchTerm .'%';
        }

        $sql = 'SELECT DISTINCT u.id, u.name, u.surname ' . $sql . ' ORDER BY u.name ASC, u.surname ASC, u.id DESC';

        $connection = $this->getEntityManager()->getConnection();
        $statement = $connection->prepare($sql);
        
        $result = $statement->executeQuery($params);

        return $result->fetchAllAssociative();
    }
}
