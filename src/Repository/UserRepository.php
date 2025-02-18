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

    public function findAllReplacementDataTables(DataTableParams $params): DataTableResponse
    {
        $sortBy = $params->getOrderColumn(['u.id', 'u.status', 'u.name', 'u.email', 'u.createdAt', 's.name'], 'u.id');
        
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.speciality', 's')
            ->orderBy($sortBy, $params->getOrderDir())
            ->setMaxResults($params->limit)
            ->setFirstResult($params->offset);

        if (!empty($params->value)) {
            $qb->where('u.name LIKE :value')
                ->orWhere('u.surname LIKE :value')
                ->orWhere('u.email LIKE :value')
                ->orWhere('s.name LIKE :value')
                ->setParameter('value', '%' . $params->value . '%');
        }
        
        $paginator = new Paginator($qb->getQuery());

        return DataTableResponse::fromPaginator($paginator, $params->draw + 1);
    }
}
