<?php

namespace App\Repository;

use App\Dto\DataTable\DataTableParams;
use App\Dto\DataTable\DataTableResponse;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAllDataTables(?int $roleId, DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['u.id', 'u.status', 'u.name', 'u.email', 'u.createdAt', 's.name'], 'u.id');
        
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.speciality', 's')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!is_null($roleId)) {
            $qb->join('u.roles', 'r')
                ->andWhere('r.id = :roleId')
                ->setParameter('roleId', $roleId);
        }
        
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

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }

    public function findAllDataTablesForRequest(?int $roleId, ?DataTableParams $params = null, ?string $searchValue = null): DataTableResponse
    {
        $searchParam = $searchValue;

        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('u.mobilities', 'm');
        
        if (!is_null($params)) {
            $sortBy = $params->getOrderColumn(['u.id', 'u.name', 'u.surname', 's.name', 'm.name'], 'u.id');
            $searchParam = $params->value;

            $qb->orderBy($sortBy, $params->getOrderDir())
                ->setMaxResults($params->limit)
                ->setFirstResult($params->offset);
        }

        if (!is_null($roleId)) {
            $qb->join('u.roles', 'r')
                ->andWhere('r.id = :roleId')
                ->setParameter('roleId', $roleId);
        }
        
        if (!empty($searchParam)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'u.name LIKE :value',
                        'u.surname LIKE :value',
                        'u.email LIKE :value',
                        's.name LIKE :value',
                        'm.name LIKE :value',
                    )
                )
                ->setParameter('value', '%' . $searchParam . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        $result = [];
        foreach($paginator as $row) {
            $resultRow = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'surname' => $row->getSurname(),
                'speciality' => [
                    'id' => $row->getSpeciality()?->getId(),
                    'name' => $row->getSpeciality()?->getName(),
                ],
                'mobilities' => []
            ];

            if ($row->getMobilities()) {
                foreach($row->getMobilities() as $region) {
                    $resultRow['mobilities'][] = [
                        'id' => $region->getId(),
                        'name' => $region->getName(),
                    ];
                }
            }

            $result[] = $resultRow;
        }

        $response = DataTableResponse::fromPaginator($paginator, $params->draw + 1, true);
        $response->data = $result;
        return $response;
    }

    public function findAllIdsForRequest(?int $roleId, ?string $searchValue = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('DISTINCT(u.id)')
            ->leftJoin('u.speciality', 's')
            ->leftJoin('u.mobilities', 'm');

        if (!is_null($roleId)) {
            $qb->join('u.roles', 'r')
                ->andWhere('r.id = :roleId')
                ->setParameter('roleId', $roleId);
        }
        
        if (!empty($searchValue)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'u.name LIKE :value',
                        'u.surname LIKE :value',
                        'u.email LIKE :value',
                        's.name LIKE :value',
                        'm.name LIKE :value',
                    )
                )
                ->setParameter('value', '%' . $searchValue . '%');
        }
        
        return $qb->getQuery()->getSingleColumnResult();
    }
}
