<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\RequestResponse;
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
        $sortBy = $params->getOrderColumn(['u.name', 'u.surname', 's.name', 'a.status'], 'a.id');

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
}
